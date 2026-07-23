<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Prevents SuluHttpCache (Symfony HttpCache + Psr6Store) from writing error
 * responses to disk.
 *
 * Symfony's Response::isCacheable() treats 404 as cacheable. Without this
 * subscriber a bot requesting thousands of unique non-existent URLs causes
 * HttpCache::fetch() to write a separate cache file for each URL, which can
 * exhaust disk space and take down the server.
 *
 * Runs at priority -1024 to fire after all Sulu response subscribers
 * (highest known priority: WebspaceTagSubscriber at 2048) and have the
 * final word on Cache-Control for error responses.
 */
final class HttpCacheControlSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -1024],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        if ($response->getStatusCode() < 400) {
            return;
        }

        $response->headers->set('Cache-Control', 'no-store, private');
    }
}
