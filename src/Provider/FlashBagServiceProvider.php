<?php

declare(strict_types=1);

namespace Chiron\Flash\Provider;

use Chiron\Container\BindingInterface;
use Chiron\Core\Container\Provider\ServiceProviderInterface;
use Chiron\Core\Exception\ScopeException;
use Chiron\Session\Session;
use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Flash\Middleware\FlashBagMiddleware;
use Chiron\Flash\FlashBag;

final class FlashBagServiceProvider implements ServiceProviderInterface
{
    public function register(BindingInterface $container): void
    {
        // This SHOULDN'T BE a singleton(), use a basic bind() to ensure Request instance is fresh !
        $container->bind(FlashBag::class, Closure::fromCallable([$this, 'flashBag']));
    }

    private function flashBag(ServerRequestInterface $request): FlashBag
    {
        $flashBag = $request->getAttribute(FlashBagMiddleware::ATTRIBUTE);

        if ($flashBag === null) {
            throw new ScopeException('Unable to resolve FlashBag, invalid request scope.');
        }

        return $flashBag;
    }
}
