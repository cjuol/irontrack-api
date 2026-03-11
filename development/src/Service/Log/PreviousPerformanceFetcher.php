<?php

declare(strict_types=1);

namespace App\Service\Log;

use App\Entity\Catalog\Exercise;
use App\Entity\Log\SetEntry;
use App\Entity\User;
use App\Repository\Log\SetEntryRepository;

class PreviousPerformanceFetcher
{
    public function __construct(private readonly SetEntryRepository $setEntryRepository) {}

    /** @return SetEntry[] */
    public function getLastPerformance(Exercise $exercise, User $user): array
    {
        return $this->setEntryRepository->findLastPerformance($exercise, $user);
    }

    /** @return array{maxWeight: float, totalVolume: float, sets: int}|null */
    public function getLastPerformanceSummary(Exercise $exercise, User $user): ?array
    {
        $sets = $this->getLastPerformance($exercise, $user);

        if (empty($sets)) {
            return null;
        }

        $maxWeight   = 0.0;
        $totalVolume = 0.0;

        foreach ($sets as $set) {
            $maxWeight   = max($maxWeight, $set->getWeightKg());
            $totalVolume += $set->getVolume();
        }

        return [
            'maxWeight'   => $maxWeight,
            'totalVolume' => $totalVolume,
            'sets'        => count($sets),
        ];
    }

    /** @return array<int, array{date: string, sessionId: string, sets: SetEntry[]}> */
    public function getHistory(Exercise $exercise, User $user, int $limit = 10): array
    {
        return $this->setEntryRepository->findPerformanceHistory($exercise, $user, $limit);
    }
}
