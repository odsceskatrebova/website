<?php

declare(strict_types=1);

namespace App\Sulu\Controller\Admin;

use App\Entity\CookieConsent;
use App\Repository\CookieConsentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class CookieConsentController extends AbstractController
{
    public function __construct(
        private readonly CookieConsentRepository $repository,
    ) {
    }

    #[Route('/admin/api/cookie_consents', name: 'app.get_cookie_consents', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 25);
        $page = max(1, $request->query->getInt('page', 1));
        $offset = ($page - 1) * $limit;

        $items = $this->repository->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
        $total = $this->repository->count([]);

        return $this->json([
            '_embedded' => [
                CookieConsent::RESOURCE_KEY => array_map(fn (CookieConsent $c) => [
                    'id' => (string) $c->getId(),
                    'createdAt' => $c->getCreatedAt()->format('c'),
                    'ip' => $c->getIp(),
                    'userAgent' => $c->getUserAgent(),
                    'preferences' => implode(', ', $c->getPreferences()),
                ], $items),
            ],
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    #[Route('/admin/api/cookie_consents/{id}', name: 'app.get_cookie_consent', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $consent = $this->repository->find($id);
        if (!$consent) {
            return $this->json(['error' => 'Not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => (string) $consent->getId(),
            'createdAt' => $consent->getCreatedAt()->format('c'),
            'ip' => $consent->getIp(),
            'userAgent' => $consent->getUserAgent(),
            'preferences' => implode(', ', $consent->getPreferences()),
        ]);
    }
}
