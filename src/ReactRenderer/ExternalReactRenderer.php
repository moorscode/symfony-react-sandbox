<?php

namespace App\ReactRenderer;

use App\DataCollector\ExternalServerRequestCollector;
use Limenius\ReactRenderer\Context\ContextProviderInterface;
use Limenius\ReactRenderer\Renderer\ReactRendererInterface;
use Limenius\ReactRenderer\Renderer\RenderResult;
use Limenius\ReactRenderer\Renderer\RenderResultInterface;
use Psr\Log\LoggerInterface;

/**
 * This class should be in the implementation repository.
 */
class ExternalReactRenderer implements ReactRendererInterface
{
    /**
     * Constructor.
     *
     * @param string                   $serverSocketPath
     * @param bool                     $failLoud
     * @param ContextProviderInterface $contextProvider
     * @param LoggerInterface          $logger
     */
    public function __construct(
        protected string $serverSocketPath,
        private readonly bool $failLoud,
        private readonly ContextProviderInterface $contextProvider,
        private readonly LoggerInterface $logger,
        private readonly ExternalServerRequestCollector $externalServerRequestCollector
    ) {

        if (!str_contains($this->serverSocketPath, '://')) {
            throw new \InvalidArgumentException('Missing protocol for server socket path.');
        }
    }

    /**
     * @param string $componentName
     * @param string $propsString
     * @param string $uuid
     * @param array  $registeredStores
     * @param bool   $trace
     *
     * @return RenderResultInterface
     */
    public function render(
        string $componentName,
        string $propsString,
        string $uuid,
        array $registeredStores = [],
        bool $trace = false
    ): RenderResultInterface {
        if (!$socket = stream_socket_client($this->serverSocketPath, $errorCode, $errorMessage)) {
            throw new \RuntimeException($errorMessage);
        }

        $data = $this->getRemoteRenderingCode($componentName, $propsString, $uuid, $registeredStores, $trace);

        $this->logger->debug(
            'Requesting server side rendering ({server}) with: {data}',
            ['server' => $this->serverSocketPath, 'data' => $data]
        );

        stream_socket_sendto($socket, $data."\0");

        $contents = stream_get_contents($socket);
        if (false === $contents) {
            throw new \RuntimeException('Failed to read content from external renderer.');
        }

        fclose($socket);

        $this->logger->debug('Server side rendering returned {contents}', ['contents' => $contents]);

        $this->externalServerRequestCollector->addRequest($this->serverSocketPath, $data, $contents);

        $result = json_decode($contents, true);
        if ($result['hasErrors']) {
            $this->logErrors($result['consoleReplayScript']);
            if ($this->failLoud) {
                $this->throwError($result['consoleReplayScript'], $componentName);
            }
        }

        $evaluated = $result['html'];
        if (!$result['hasErrors'] && is_array($evaluated) && array_key_exists('componentHtml', $evaluated)) {
            $evaluated = $evaluated['componentHtml'];
        }

        return new RenderResult(
            $evaluated,
            $result['consoleReplayScript'],
            $result['hasErrors']
        );
    }

    /**
     * @param array  $registeredStores
     * @param string $context
     *
     * @return string
     */
    protected function initializeReduxStores(array $registeredStores = [], string $context = ''): string
    {
        if (empty($registeredStores)) {
            return '';
        }

        $result = '';
        foreach ($registeredStores as $storeName => $reduxProps) {
            $result .= "ReactOnRails.setStore('$storeName',ReactOnRails.getStoreGenerator('$storeName')($reduxProps,$context));";
        }

        return $result;
    }

    /**
     * Logs the errors extracted from the console replay.
     *
     * @param string $consoleReplay
     *
     * @return void
     */
    protected function logErrors(string $consoleReplay): void
    {
        if (!$this->logger) {
            return;
        }

        $report = $this->extractErrorLines($consoleReplay);
        foreach ($report as $line) {
            $this->logger->warning($line);
        }
    }

    /**
     * @param string $name
     * @param string $propsString
     * @param string $uuid
     * @param array  $registeredStores
     * @param bool   $trace
     *
     * @return string
     */
    protected function getRemoteRenderingCode(
        string $name,
        string $propsString,
        string $uuid,
        array $registeredStores = [],
        bool $trace = false
    ): string {
        $context = $this->contextProvider->getContext(true);
        $jsContext = json_encode(
            [
                'serverSide' => $context->isServerSide(),
                'href' => $context->href(),
                'location' => $context->requestUri(),
                'scheme' => $context->scheme(),
                'host' => $context->host(),
                'port' => $context->port(),
                'base' => $context->baseUrl(),
                'pathname' => $context->pathInfo(),
                'search' => $context->queryString(),
            ]
        );

        $traceStr = json_encode($trace);

        $initializedReduxStores = $this->initializeReduxStores($registeredStores, $jsContext);

        $template = '
(function() {
  %1$s
  return ReactOnRails.serverRenderReactComponent({
    name: \'%2$s\',
    domNodeId: \'%3$s\',
    props: %4$s,
    trace: %5$s,
    railsContext: %6$s
  });
})();
';
        $template = str_replace("\n", '', $template);

        return sprintf($template, $initializedReduxStores, $name, $uuid, $propsString, $traceStr, $jsContext);
    }

    /**
     * Extracts the error lines from a console replay script.
     *
     * @param string $consoleReplay
     *
     * @return array
     */
    protected function extractErrorLines(string $consoleReplay): array
    {
        $report = [];
        $lines = explode("\n", $consoleReplay);
        $usefulLines = array_slice($lines, 2, count($lines) - 4);
        foreach ($usefulLines as $line) {
            if (preg_match('/console\.error\.apply\(console, \["\[SERVER] (?P<msg>.*)"]\);/', $line, $matches)) {
                $report[] = $matches['msg'];
            }
        }

        return $report;
    }

    /**
     * Converts the console replay script into a PHP error.
     *
     * @param string $consoleReplay
     * @param string $componentName
     *
     * @return void
     *
     * @throws EvalJsException
     */
    protected function throwError(string $consoleReplay, string $componentName): void
    {
        $report = implode("\n", $this->extractErrorLines($consoleReplay));
        throw new EvalJsException($componentName, $report);
    }
}
