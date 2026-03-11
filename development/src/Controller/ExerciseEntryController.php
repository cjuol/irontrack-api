<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\LogSetRequest;
use App\Entity\User;
use App\Repository\Log\ExerciseEntryRepository;
use App\Repository\Program\PlannedSetRepository;
use App\Service\Log\WorkoutSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/exercise-entries')]
class ExerciseEntryController extends AbstractController
{
    public function __construct(
        private readonly ExerciseEntryRepository $entryRepository,
        private readonly PlannedSetRepository    $plannedSetRepository,
        private readonly WorkoutSessionService   $sessionService,
    ) {}

    #[Route('/{id}/sets', methods: ['POST'])]
    public function logSet(
        string $id,
        #[MapRequestPayload] LogSetRequest $dto,
    ): JsonResponse {
        $entry = $this->entryRepository->find($id);

        if ($entry === null) {
            return $this->json(['error' => 'Entrada de ejercicio no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($entry->getWorkoutSession()->getTrainingDay()->getUser()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Acceso denegado.'], Response::HTTP_FORBIDDEN);
        }

        $planned = $dto->plannedSetId !== null
            ? $this->plannedSetRepository->find($dto->plannedSetId)
            : null;

        $set = $this->sessionService->logSet(
            $entry,
            $dto->weightKg,
            $dto->reps,
            $dto->rir,
            $dto->toFailure,
            $planned,
        );

        return $this->json($set, Response::HTTP_CREATED, context: ['groups' => 'session:read']);
    }
}
