<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\Program\MesocycleRepository;
use App\Repository\Program\SessionTemplateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1')]
class MesocycleController extends AbstractController
{
    public function __construct(
        private readonly MesocycleRepository      $mesocycleRepository,
        private readonly SessionTemplateRepository $sessionTemplateRepository,
    ) {}

    #[Route('/mesocycles', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var User $user */
        $user      = $this->getUser();
        $mesocycles = $this->mesocycleRepository->findByUser($user);

        return $this->json($mesocycles, context: ['groups' => 'mesocycle:read']);
    }

    #[Route('/mesocycles/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        /** @var User $user */
        $user      = $this->getUser();
        $mesocycle = $this->mesocycleRepository->findOneWithFullStructure($id, $user);

        if ($mesocycle === null) {
            return $this->json(['error' => 'Mesociclo no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($mesocycle, context: ['groups' => 'mesocycle:read']);
    }

    #[Route('/mesocycles/{id}/sessions', methods: ['GET'])]
    public function sessions(string $id): JsonResponse
    {
        /** @var User $user */
        $user      = $this->getUser();
        $mesocycle = $this->mesocycleRepository->findOneWithFullStructure($id, $user);

        if ($mesocycle === null) {
            return $this->json(['error' => 'Mesociclo no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $templates = [];
        foreach ($this->sessionTemplateRepository->findByMesocycle($mesocycle) as $template) {
            $templates[] = $this->sessionTemplateRepository->findOneWithFullPlan(
                $template->getId()->toRfc4122()
            );
        }

        return $this->json($templates, context: ['groups' => 'mesocycle:sessions']);
    }
}
