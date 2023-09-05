<?php

namespace App\ReactRenderer;

use Limenius\ReactRenderer\Context\ContextProviderInterface;
use Limenius\ReactRenderer\Renderer\ReactRendererInterface;
use Psr\Log\LoggerInterface;

class Factory
{
    public static function createRenderer(
        string $serverSocketPath,
        ContextProviderInterface $contextProvider,
        LoggerInterface $logger
    ): ReactRendererInterface {
        return new ExternalReactRenderer($serverSocketPath, $contextProvider, $logger);
    }
}
