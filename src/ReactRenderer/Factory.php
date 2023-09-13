<?php

namespace App\ReactRenderer;

use App\DataCollector\ExternalServerRequestCollector;
use MyOnlineStore\ReactRenderer\Context\ContextProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * Creates external renderer nodes from config.
 */
class Factory
{
    /**
     * Creates a renderer.
     *
     * @param string                         $serverSocketPath
     * @param bool                           $failLoud
     * @param ContextProviderInterface       $contextProvider
     * @param LoggerInterface                $logger
     * @param ExternalServerRequestCollector $externalServerRequestCollector
     *
     * @return ExternalReactRenderer
     */
    public static function createRenderer(
        string $serverSocketPath,
        bool $failLoud,
        ContextProviderInterface $contextProvider,
        LoggerInterface $logger,
        ExternalServerRequestCollector $externalServerRequestCollector
    ): ExternalReactRenderer {
        return new ExternalReactRenderer(
            $serverSocketPath,
            $failLoud,
            $contextProvider,
            $logger,
            $externalServerRequestCollector
        );
    }
}
