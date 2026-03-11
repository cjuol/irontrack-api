<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\CreateSessionRequest;
use App\DTO\Request\LogStepsRequest;
use App\Entity\User;
use App\Enum\TrainingDayType;
use App\Repository\Log\TrainingDayRepository;
use App\Repository\Program\SessionTemplateRepository;
use App\Service\Log\TrainingDayService;
use App\Service\Log\WorkoutSessionService;
use App\Service\Program\SessionPreloader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/training-days')]
class TrainingDayController extends AbstractController
{
    public function __construct(
        private readonly TrainingDayRepository     $trainingDayRepository,
        private readonly SessionTemplateRepository $sessionTemplateRepository,
        private readonly TrainingDayService        $trainingDayService,
        private readonly WorkoutSessionService     $sessionService,
        private readonly SessionPreloader          $preloader,
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        /** @var User $user */
        $user  = $this->getUser();
        $year  = (int) ($request->query->get('year', date('Y')));
        $month = (int) ($request->query->get('month', date('n')));

        $days = $this->trainingDayRepository->findByUserAndMonth($user, $year, $month);

        return $this->json($days, context: ['groups' => 'training-day:read']);
    }

    #[Route('/{date}', methods: ['GET'], requirements: ['date' => '\d{4}-\d{2}-\d{2}'])]
    public function show(string $date): JsonResponse
    {
        /** @var User $user */
        $user    = $this->getUser();
        $dateObj = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        if ($dateObj === false) {
            return $this->json(['error' => 'Formato de fecha inválido. Use YYYY-MM-DD.'], Response::HTTP_BAD_REQUEST);
        }

        $day = $this->trainingDayRepository->findOneWithSessions($user, $dateObj);

        if ($day === null) {
            return $this->json(['error' => 'Día no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($day, context: ['groups' => 'training-day:read']);
    }

    #[Route('/{date}/sessions', methods: ['POST'], requirements: ['date' => '\d{4}-\d{2}-\d{2}'])]
    public function createSession(
        string $date,
        #[MapRequestPayload] CreateSessionRequest $dto,
    ): JsonResponse {
        /** @var User $user */
        $user    = $this->getUser();
        $dateObj = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        if ($dateObj === false) {
            return $this->json(['error' => 'Formato de fecha inválido. Use YYYY-MM-DD.'], Response::HTTP_BAD_REQUEST);
        }

        $day = $this->trainingDayService->findOrCreate($user, $dateObj, TrainingDayType::TRAINING);

        try {
            if ($dto->templateId !== null) {
                $template = $this->sessionTemplateRepository->find($dto->templateId);

                if ($template === null || $template->getMesocycle()->getUser()->getId() !== $user->getId()) {
                    return $this->json(['error' => 'Plantilla no encontrada.'], Response::HTTP_NOT_FOUND);
                }

                $session = $this->preloader->preloadFromSpecificTemplate($day, $template);
            } elseif ($dto->templateSortOrder !== null) {
                $session = $this->preloader->preloadFromTemplate($day, $dto->templateSortOrder);
            } else {
                $session = $this->sessionService->create($day);
            }
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json($session, Response::HTTP_CREATED, context: ['groups' => 'session:read']);
    }

    #[Route('/{date}/steps', methods: ['PUT'], requirements: ['date' => '\d{4}-\d{2}-\d{2}'])]
    public function logSteps(
        string $date,
        #[MapRequestPayload] LogStepsRequest $dto,
    ): JsonResponse {
        /** @var User $user */
        $user    = $this->getUser();
        $dateObj = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        if ($dateObj === false) {
            return $this->json(['error' => 'Formato de fecha inválido. Use YYYY-MM-DD.'], Response::HTTP_BAD_REQUEST);
        }

        $day = $this->trainingDayService->findOrCreate($user, $dateObj, TrainingDayType::TRAINING);
        $this->trainingDayService->logSteps($day, $dto->steps);

        return $this->json($day, context: ['groups' => 'training-day:read']);
    }
}
