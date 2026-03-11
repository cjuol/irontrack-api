<?php

declare(strict_types=1);

namespace App\Service\Log;

use App\Entity\Catalog\Exercise;
use App\Entity\User;
use App\Repository\Log\SetEntryRepository;
use Cjuol\StatGuard\RobustStats;
use Cjuol\StatGuard\StatsComparator;

class ProgressionAnalyzer
{
    private readonly RobustStats $robust;
    private readonly StatsComparator $comparator;

    public function __construct(private readonly SetEntryRepository $setEntryRepository)
    {
        $this->robust     = new RobustStats();
        $this->comparator = new StatsComparator();
    }

    /**
     * La media de Huber ignora sesiones anómalas (lesiones, días malos) para
     * que la línea de tendencia refleje la progresión real del atleta.
     *
     * @return array{
     *   dataPoints: array<int, array{date: string, maxWeight: float, estimated1rm: float|null}>,
     *   trend: array{huberMean: float|null, robustDeviation: float|null, hasOutliers: bool}
     * }
     */
    public function analyzeProgression(Exercise $exercise, User $user, int $days = 90): array
    {
        $weightHistory = $this->setEntryRepository->findMaxWeightPerSession($exercise, $user, $days);
        $irmHistory    = $this->setEntryRepository->findEstimated1RMHistory($exercise, $user, $days);

        $irmByDate = [];
        foreach ($irmHistory as $entry) {
            $irmByDate[$entry['date']] = $entry['estimated1rm'];
        }

        $dataPoints = [];
        foreach ($weightHistory as $entry) {
            $date         = $entry['date'] instanceof \DateTimeInterface ? $entry['date']->format('Y-m-d') : (string) $entry['date'];
            $dataPoints[] = [
                'date'         => $date,
                'maxWeight'    => (float) $entry['maxWeight'],
                'estimated1rm' => isset($irmByDate[$date]) ? (float) $irmByDate[$date] : null,
            ];
        }

        $irmValues = array_values(array_filter(
            array_column($irmHistory, 'estimated1rm'),
            static fn($v) => $v !== null,
        ));

        $trend = ['huberMean' => null, 'robustDeviation' => null, 'hasOutliers' => false];

        if (count($irmValues) >= 2) {
            $trend['huberMean']       = round($this->robust->getHuberMean($irmValues), 2);
            $trend['robustDeviation'] = round($this->robust->getRobustDeviation($irmValues), 2);

            $analysis             = $this->comparator->analyze($irmValues);
            $trend['hasOutliers'] = ($analysis['outliers_detected'] ?? false) === true;
        }

        return ['dataPoints' => $dataPoints, 'trend' => $trend];
    }

    /** @return array<int, array{muscle: string, volume: float, sets: int}> */
    public function getVolumeStats(User $user, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $volumes = $this->setEntryRepository->findVolumeByMuscleGroup($user, $from, $to);
        $counts  = $this->setEntryRepository->findSetCountByMuscleGroup($user, $from, $to);

        $setsByMuscle = [];
        foreach ($counts as $row) {
            $setsByMuscle[$row['muscle']] = (int) $row['totalSets'];
        }

        return array_map(static fn(array $row) => [
            'muscle' => $row['muscle'],
            'volume' => round((float) $row['totalVolume'], 2),
            'sets'   => $setsByMuscle[$row['muscle']] ?? 0,
        ], $volumes);
    }
}
