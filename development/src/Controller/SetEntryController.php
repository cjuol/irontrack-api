<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\LogSetRequest;
use App\Entity\User;
use App\Repository\Log\SetEntryRepository;
use App\Service\Log\WorkoutSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/set-entries')]
class SetEntryController extends AbstractController
{
    public function __construct(
        private readonly SetEntryRepository    $setEntryRepository,
        private readonly WorkoutSessionService $sessionService,
    ) {}

    #[Route('/{id}', methods: ['PUT'])]
    public function edit(
        string $id,
        #[MapRequestPayload] LogSetRequest $dto,
    ): JsonResponse {
        $set = $this->setEntryRepository->find($id);

        if ($set === null) {
            return $this->json(['error' => 'Serie no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($set->getExerciseEntry()->getWorkoutSession()->getTrainingDay()->getUser()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Acceso denegado.'], Response::HTTP_FORBIDDEN);
        }

        $this->sessionService->editSet($set, $dto->weightKg, $dto->reps, $dto->rir, $dto->toFailure);

        return $this->json($set, context: ['groups' => 'session:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $set = $this->setEntryRepository->find($id);

        if ($set === null) {
            return $this->json(['error' => 'Serie no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($set->getExerciseEntry()->getWorkoutSession()->getTrainingDay()->getUser()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Acceso denegado.'], Response::HTTP_FORBIDDEN);
        }

        $this->sessionService->deleteSet($set);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
