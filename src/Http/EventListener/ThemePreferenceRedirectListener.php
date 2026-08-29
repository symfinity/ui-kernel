<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Http\EventListener;

use Symfinity\UiKernel\Http\ThemePreferenceRedirectHandler;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 512)]
final class ThemePreferenceRedirectListener
{
    public function __construct(
        private readonly ThemePreferenceRedirectHandler $handler,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $this->handler->createRedirectIfPreferenceQueryPresent($event->getRequest());
        if ($response !== null) {
            $event->setResponse($response);
        }
    }
}
