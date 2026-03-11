<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\AddExerciseRequest;
use App\DTO\Request\FinishSessionRequest;
use App\DTO\Request\LogCardioRequest;
use App\DTO\Request\LogMetabolicRequest;
use App\Entity\User;
use App\Enum\CardioType;
use App\Repository\Catalog\ExerciseRepository;
use App\Repository\Log\WorkoutSessionRepository;
use App\Repository\Program\PlannedExerciseRepository;
use App\Repository\Program\WeeklyCardioPlanRepository;
use App\Repository\Program\WeeklyMetabolicPlanRepository;
use App\Service\Log\WorkoutSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/sessions')]
class SessionController extends AbstractController
{
    public function __construct(
        private readonly WorkoutSessionRepository      $sessionRepository,
        private readonly ExerciseRepository            $exerciseRepository,
        private readonly PlannedExerciseRepository     $plannedExerciseRepository,
        private readonly WeeklyCardioPlanRepository    $cardioPlanRepository,
        private readonly WeeklyMetabolicPlanRepository $metabolicPlanRepository,
        private readonly WorkoutSessionService         $sessionService,
    ) {}

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $session = $this->sessionRepository->findOneWithAllEntries($id);

        if ($session === null) {
            return $this->json(['error' => 'Sesión no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($session->getTrainingDay()->getUser()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Acceso denegado.'], Response::HTTP_FORBIDDEN);
        }

        return $this->json($session, context: ['groups' => 'session:read']);
    }

    #[Route('/{id}/exercises', methods: ['GET'])]
    public function exercises(string $id): JsonResponse
    {
        $session = $this->sessionRepository->findOneWithAllEntries($id);

        if ($session === null) {
            return $this->json(['error' => 'Sesión no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($session->getTrainingDay()->getUser()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Acceso denegado.'], Response::HTTP_FORBIDDEN);
        }

        return $this->json(
            $session->getExerciseEntries()->toArray(),
            context: ['groups' => 'session:read'],
        );
    }

    #[Route('/{id}/exercises', methods: ['POST'])]
    public function addExercise(
        string $id,
        #[MapRequestPayload] AddExerciseRequest $dto,
    ): JsonResponse {
        $session = $this->sessionRepository->find($id);

        if ($session === null) {
            return $this->json(['error' => 'Sesión no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($session->getTrainingDay()->getUser()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Acceso denegado.'], Response::HTTP_FORBIDDEN);
        }

        $exercise = $this->exerciseRepository->find($dto->exerciseId);

        if ($exercise === null) {
            return $this->json(['error' => 'Ejercicio no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $sortOrder = $session->getExerciseEntries()->count() + 1;

        if ($dto->plannedExerciseId !== null) {
            $planned = $this->plannedExerciseRepository->find($dto->plannedExerciseId);

            if ($planned === null) {
                return $this->json(['error' => 'Ejercicio planificado no encontrado.'], Response::HTTP_NOT_FOUND);
            }

            $entry = $this->sessionService->addPlannedExercise($session, $planned, $sortOrder);
        } else {
            $entry = $this->sessionService->addFreeExercise($session, $exercise, $sortOrder);
        }

        return $this->json($entry, Response::HTTP_CREATED, context: ['groups' => 'session:read']);
    }

    #[Route('/{id}/cardio', methods: ['POST'])]
    public function logCardio(
        string $id,
        #[MapRequestPayload] LogCardioRequest $dto,
    ): JsonResponse {
        $session = $this->sessionRepository->find($id);

        if ($session === null) {
            return $this->json(['error' => 'Sesión no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($session->getTrainingDay()->getUser()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Acceso denegado.'], Response::HTTP_FORBIDDEN);
        }

        $cardioType = CardioType::tryFrom($dto->type);

        if ($cardioType === null) {
            return $this->json(['error' => 'Tipo de cardio inválido.'], Response::HTTP_BAD_REQUEST);
        }

        $plan = $dto->weeklyCardioPlanId !== null
            ? $this->cardioPlanRepository->find($dto->weeklyCardioPlanId)
            : null;

        $entry = $this->sessionService->logCardio(
            $session,
            $cardioType,
            $dto->durationSeconds,
            $dto->distanceMeters,
            $dto->avgSpeedKmh,
            $dto->inclinePct,
            $plan,
            $dto->notes,
        );

        return $this->json($entry, Response::HTTP_CREATED, context: ['groups' => 'session:read']);
    }

    #[Route('/{id}/metabolic', methods: ['POST'])]
    public function logMetabolic(
        string $id,
        #[MapRequestPayload] LogMetabolicRequest $dto,
    ): JsonResponse {
        $session = $this->sessionRepository->find($id);

        if ($session === null) {
            return $this->json(['error' => 'Sesión no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($session->getTrainingDay()->getUser()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Acceso denegado.'], Response::HTTP_FORBIDDEN);
        }

        $plan = $dto->weeklyMetabolicPlanId !== null
            ? $this->metabolicPlanRepository->find($dto->weeklyMetabolicPlanId)
            : null;

        $entry = $this->sessionService->logMetabolic(
            $session,
            $plan,
            $dto->weekNumber,
            $dto->rounds,
            $dto->timeSeconds,
            $dto->result,
            $dto->notes,
        );

        return $this->json($entry, Response::HTTP_CREATED, context: ['groups' => 'session:read']);
    }

    #[Route('/{id}/finish', methods: ['PUT'])]
    public function finish(
        string $id,
        #[MapRequestPayload] FinishSessionRequest $dto,
    ): JsonResponse {
        $session = $this->sessionRepository->find($id);

        if ($session === null) {
            return $this->json(['error' => 'Sesión no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($session->getTrainingDay()->getUser()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Acceso denegado.'], Response::HTTP_FORBIDDEN);
        }

        if ($dto->perceivedEffort !== null) {
            $session->setPerceivedEffort($dto->perceivedEffort);
        }

        if ($dto->notes !== null) {
            $session->setNotes($dto->notes);
        }

        $this->sessionService->finish($session);

        return $this->json($session, context: ['groups' => 'session:read']);
    }
}
