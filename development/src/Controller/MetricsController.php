<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\Catalog\ExerciseRepository;
use App\Repository\Log\SetEntryRepository;
use App\Repository\Program\MesocycleRepository;
use App\Service\Log\ProgressionAnalyzer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/metrics')]
class MetricsController extends AbstractController
{
    public function __construct(
        private readonly ProgressionAnalyzer $analyzer,
        private readonly ExerciseRepository  $exerciseRepository,
        private readonly SetEntryRepository  $setEntryRepository,
        private readonly MesocycleRepository $mesocycleRepository,
    ) {}

    #[Route('/exercises/{id}/progression', methods: ['GET'])]
    public function progression(string $id, Request $request): JsonResponse
    {
        /** @var User $user */
        $user     = $this->getUser();
        $exercise = $this->exerciseRepository->find($id);

        if ($exercise === null) {
            return $this->json(['error' => 'Ejercicio no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $days   = min((int) $request->query->get('days', 90), 365);
        $result = $this->analyzer->analyzeProgression($exercise, $user, $days);

        return $this->json([
            'exercise'   => ['id' => (string) $exercise->getId(), 'name' => $exercise->getName()],
            'days'       => $days,
            'dataPoints' => $result['dataPoints'],
            'trend'      => $result['trend'],
        ]);
    }

    #[Route('/volume', methods: ['GET'])]
    public function volume(Request $request): JsonResponse
    {
        /** @var User $user */
        $user  = $this->getUser();
        $today = new \DateTimeImmutable('today');

        if ($request->query->has('from') && $request->query->has('to')) {
            try {
                $from = new \DateTimeImmutable($request->query->getString('from'));
                $to   = new \DateTimeImmutable($request->query->getString('to'));
            } catch (\Exception) {
                return $this->json(['error' => 'Formato de fecha inválido. Usa YYYY-MM-DD.'], Response::HTTP_BAD_REQUEST);
            }
        } else {
            // Si no hay rango, usamos el mesociclo activo o las últimas 4 semanas
            $mesocycle = $this->mesocycleRepository->findActiveForUser($user, $today);
            $from      = $mesocycle?->getStartDate() ?? $today->modify('-28 days');
            $to        = $mesocycle?->getEndDate()   ?? $today;
        }

        $stats = $this->analyzer->getVolumeStats($user, $from, $to);

        return $this->json([
            'from'    => $from->format('Y-m-d'),
            'to'      => $to->format('Y-m-d'),
            'muscles' => $stats,
        ]);
    }

    #[Route('/personal-records', methods: ['GET'])]
    public function personalRecords(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $prs  = $this->setEntryRepository->findPersonalRecords($user);

        return $this->json(['personalRecords' => $prs]);
    }
}
