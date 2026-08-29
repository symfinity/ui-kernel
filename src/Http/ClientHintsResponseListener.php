<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Http;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE)]
final class ClientHintsResponseListener
{
    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        if ($response->headers->has('Accept-CH')) {
            return;
        }

        $response->headers->set('Accept-CH', 'Sec-CH-Prefers-Color-Scheme');
    }
}
