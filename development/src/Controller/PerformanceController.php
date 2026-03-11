<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\Catalog\ExerciseRepository;
use App\Service\Log\PreviousPerformanceFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/exercises')]
class PerformanceController extends AbstractController
{
    public function __construct(
        private readonly ExerciseRepository         $exerciseRepository,
        private readonly PreviousPerformanceFetcher $performanceFetcher,
    ) {}

    #[Route('/{id}/last-performance', methods: ['GET'])]
    public function lastPerformance(string $id): JsonResponse
    {
        /** @var User $user */
        $user     = $this->getUser();
        $exercise = $this->exerciseRepository->find($id);

        if ($exercise === null) {
            return $this->json(['error' => 'Ejercicio no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $sets    = $this->performanceFetcher->getLastPerformance($exercise, $user);
        $summary = $this->performanceFetcher->getLastPerformanceSummary($exercise, $user);

        return $this->json([
            'exercise' => $exercise,
            'summary'  => $summary,
            'sets'     => $sets,
        ], context: ['groups' => 'performance:read']);
    }

    #[Route('/{id}/history', methods: ['GET'])]
    public function history(string $id, Request $request): JsonResponse
    {
        /** @var User $user */
        $user     = $this->getUser();
        $exercise = $this->exerciseRepository->find($id);

        if ($exercise === null) {
            return $this->json(['error' => 'Ejercicio no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $limit   = min((int) $request->query->get('limit', 10), 50);
        $history = $this->performanceFetcher->getHistory($exercise, $user, $limit);

        return $this->json([
            'exercise' => $exercise,
            'history'  => $history,
        ], context: ['groups' => 'performance:read']);
    }
}
