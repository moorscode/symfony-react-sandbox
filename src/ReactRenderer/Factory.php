<?php

namespace App\ReactRenderer;

use Limenius\ReactRenderer\Context\ContextProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * Creates external renderer nodes from config.
 */
class Factory
{
    /**
     * Creates a renderer.
     *
     * @param string                   $serverSocketPath
     * @param ContextProviderInterface $contextProvider
     * @param LoggerInterface          $logger
     *
     * @return ExternalReactRenderer
     */
    public static function createRenderer(
        string $serverSocketPath,
        ContextProviderInterface $contextProvider,
        LoggerInterface $logger
    ): ExternalReactRenderer {
        return new ExternalReactRenderer($serverSocketPath, $contextProvider, $logger);
    }
}
