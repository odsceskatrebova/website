<?php

declare(strict_types=1);

namespace App\Sulu\Controller\Admin;

use App\Entity\Lead;
use App\Repository\LeadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LeadController extends AbstractController
{
    public function __construct(
        private readonly LeadRepository $repository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/admin/api/leads', name: 'app.get_leads', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 25);
        $page = max(1, $request->query->getInt('page', 1));
        $offset = ($page - 1) * $limit;

        $items = $this->repository->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
        $total = $this->repository->count([]);

        return $this->json([
            '_embedded' => [
                Lead::RESOURCE_KEY => array_map(fn (Lead $lead) => [
                    'id' => (string) $lead->getId(),
                    'createdAt' => $lead->getCreatedAt()->format('c'),
                    'name' => $lead->getName(),
                    'email' => $lead->getEmail(),
                    'phone' => $lead->getPhone(),
                    'source' => $lead->getSource(),
                ], $items),
            ],
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    #[Route('/admin/api/leads/{id}', name: 'app.get_lead', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $lead = $this->repository->find($id);
        if (!$lead) {
            return $this->json(['error' => 'Not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => (string) $lead->getId(),
            'createdAt' => $lead->getCreatedAt()->format('c'),
            'source' => $lead->getSource(),
            'name' => $lead->getName(),
            'email' => $lead->getEmail(),
            'phone' => $lead->getPhone(),
            'message' => $lead->getMessage(),
            'payload' => json_encode($lead->getPayload()),
        ]);
    }

    #[Route('/admin/api/leads/{id}', name: 'app.delete_lead', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $lead = $this->repository->find($id);
        if (!$lead) {
            return $this->json(['error' => 'Not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($lead);
        $this->entityManager->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
