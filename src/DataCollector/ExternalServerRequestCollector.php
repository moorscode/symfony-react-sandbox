<?php

namespace App\DataCollector;

use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Data collector about external server calls.
 */
class ExternalServerRequestCollector extends AbstractDataCollector
{
    /**
     * @param Request         $request
     * @param Response        $response
     * @param \Throwable|null $exception
     *
     * @return void
     */
    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
    }

    /**
     * Used in the profiler twig template.
     *
     * @return array
     */
    public function getRequests(): array
    {
        return $this->data['requests'];
    }

    /**
     * @return string|null
     */
    public static function getTemplate(): ?string
    {
        return 'data_collector/template.html.twig';
    }

    /**
     * Resets the data object.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->data = ['requests' => []];
    }

    /**
     * Adds a server side render request to the stack.
     *
     * @param string $serverSocketPath
     * @param string $data
     * @param string $contents
     *
     * @return void
     */
    public function addRequest(string $serverSocketPath, string $data, string $contents): void
    {
        $this->data['requests'][] = [
            'server' => $serverSocketPath,
            'request' => $data,
            'response' => $contents,
        ];
    }
}
