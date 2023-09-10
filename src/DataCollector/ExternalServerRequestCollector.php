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

    public function getRequests(): array {
        return $this->data['requests'];
    }

    /**
     * @return string|null
     */
    public static function getTemplate(): ?string
    {
        return 'data_collector/template.html.twig';
    }

    public function reset(): void
    {
        $this->data = ['requests' => []];
    }

    public function addRequest(string $serverSocketPath, string $data, string $contents)
    {
        $this->data['requests'][] = [
            'server' => $serverSocketPath,
            'request' => $data,
            'response' => $contents,
        ];
    }
}
