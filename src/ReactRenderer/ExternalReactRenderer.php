<?php

namespace App\ReactRenderer;

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
    /** @var string */
    protected $serverSocketPath = 'tcp://node:3000';
    /** @var bool */
    protected $failLoud = false;
    /** @var LoggerInterface */
    private $logger;
    /** @var ContextProviderInterface */
    private $contextProvider;

    /**
     * Constructor.
     *
     * @param ContextProviderInterface $contextProvider
     * @param LoggerInterface          $logger
     */
    public function __construct(
        ContextProviderInterface $contextProvider,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->contextProvider = $contextProvider;
    }

    /**
     * @param string|null $serverSocketPath
     */
    public function setServerSocketPath(?string $serverSocketPath): void
    {
        $this->logger->debug('Setting server socket path '.$serverSocketPath);
        if ($serverSocketPath) {
            $this->serverSocketPath = $serverSocketPath;
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
        array $registeredStores = array(),
        bool $trace = false
    ): RenderResultInterface {
        if (strpos($this->serverSocketPath, '://') === false) {
            $this->serverSocketPath = 'unix://'.$this->serverSocketPath;
        }

        if (!$sock = stream_socket_client($this->serverSocketPath, $errno, $errstr)) {
            throw new \RuntimeException($errstr);
        }

        $data = $this->wrap($componentName, $propsString, $uuid, $registeredStores, $trace);
        $this->logger->debug('Sending data {data}', ['data' => $data]);

        stream_socket_sendto($sock, $data."\0");

        if (false === $contents = stream_get_contents($sock)) {
            throw new \RuntimeException('Failed to read content from external renderer.');
        }

        fclose($sock);

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

        $this->logger->debug('Server side rendering returned {contents}', ['contents' => $contents]);

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
    protected function initializeReduxStores(array $registeredStores = array(), string $context = ''): string
    {
        if (!is_array($registeredStores) || empty($registeredStores)) {
            return '';
        }

        $result = 'var reduxProps, context, storeGenerator, store'.PHP_EOL;
        foreach ($registeredStores as $storeName => $reduxProps) {
            $result .= <<<JS
reduxProps = $reduxProps;
context = $context;
storeGenerator = ReactOnRails.getStoreGenerator('$storeName');
store = storeGenerator(reduxProps, context);
ReactOnRails.setStore('$storeName', store);
JS;
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
    protected function wrap(
        string $name,
        string $propsString,
        string $uuid,
        array $registeredStores = array(),
        bool $trace = false
    ): string {
        $context = $this->contextProvider->getContext(true);
        $contextArray = [
            'serverSide' => $context->isServerSide(),
            'href' => $context->href(),
            'location' => $context->requestUri(),
            'scheme' => $context->scheme(),
            'host' => $context->host(),
            'port' => $context->port(),
            'base' => $context->baseUrl(),
            'pathname' => $context->pathInfo(),
            'search' => $context->queryString(),
        ];

        $traceStr = $trace ? 'true' : 'false';
        $jsContext = json_encode($contextArray);

        $initializedReduxStores = $this->initializeReduxStores($registeredStores, $jsContext);

        return <<<JS
(function() {
  $initializedReduxStores
  return ReactOnRails.serverRenderReactComponent({
    name: '$name',
    domNodeId: '$uuid',
    props: $propsString,
    trace: $traceStr,
    railsContext: $jsContext,
  });
})();
JS;
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
