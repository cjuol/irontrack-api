<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\Log\DashboardAggregator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/dashboard')]
class DashboardController extends AbstractController
{
    public function __construct(private readonly DashboardAggregator $aggregator) {}

    #[Route('/summary', methods: ['GET'])]
    public function summary(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json($this->aggregator->getSummary($user));
    }
}
