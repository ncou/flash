<?php

declare(strict_types=1);

namespace Chiron\Flash;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Flash\Config\FlashConfig;

final class FlashBagFactory
{
    private $flashConfig;

    public function __construct(FlashConfig $flashConfig)
    {
        $this->flashConfig = $flashConfig;
    }

    public function create(array $messages): FlashBag
    {
        // TODO : il faut maintenant utiliser ces valeurs !!!!
        $level = $this->flashConfig->getLevel();
        $tags = $this->flashConfig->getTags();

        return FlashBag::create($messages);
    }
}
