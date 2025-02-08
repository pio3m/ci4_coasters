<?php
namespace App\Services;

use Config\Services;
use App\Services\RedisService;
use Psr\Log\LoggerInterface;

class CapacityService
{
    private RedisService $redisService;
    private LoggerInterface $logger;
    private string $prefix;

    public function __construct()
    {
        $this->redisService = new RedisService();
        $this->logger = Services::logger();
        $this->prefix = \App\Services\ConfigService::getRedisPrefix();
    }

    public function checkCapacity(string $coasterId): array
    {
        $coasterData = $this->redisService->getCoaster($coasterId, true);
        if (!$coasterData) {
            return [
                "error" => "Kolejka nie istnieje.",
                "coaster_id" => $coasterId,
                "daily_clients" => 0,
                "available_personnel" => 0,
                "required_personnel" => 0,
                "total_capacity" => 0,
                "missing_wagons" => 0,
                "missing_personnel" => 0
            ];
        }

        $wagons = $this->redisService->getWagonsForCoaster($coasterId) ?? [];
        $totalCapacity = 0;
        $requiredPersonnel = 1;
        $missingWagons = 0;
        $missingPersonnel = 0;

        foreach ($wagons as $wagon) {
            $totalCapacity += $this->calculateWagonCapacity($coasterData, $wagon);
            $requiredPersonnel += 2;
        }

        $availablePersonnel = isset($coasterData['liczba_personelu']) ? (int) $coasterData['liczba_personelu'] : 0;
        $dailyClients = isset($coasterData['liczba_klientow']) ? (int) $coasterData['liczba_klientow'] : 0;

        if ($totalCapacity < $dailyClients) {
            $missingWagons = ceil(($dailyClients - $totalCapacity) / 32);
        }

        if ($availablePersonnel < $requiredPersonnel) {
            $missingPersonnel = $requiredPersonnel - $availablePersonnel;
        }

        return [
            "coaster_id" => $coasterId,
            "daily_clients" => $dailyClients,
            "available_personnel" => $availablePersonnel,
            "required_personnel" => $requiredPersonnel,
            "total_capacity" => $totalCapacity,
            "missing_wagons" => $missingWagons,
            "missing_personnel" => $missingPersonnel
        ];
    }

    private function calculateWagonCapacity(array $coasterData, array $wagon): int
    {
        $trackLength = isset($coasterData['dl_trasy']) ? (int) $coasterData['dl_trasy'] : 0;
        $speed = isset($wagon['predkosc_wagonu']) ? (float) $wagon['predkosc_wagonu'] : 0;
        $seats = isset($wagon['ilosc_miejsc']) ? (int) $wagon['ilosc_miejsc'] : 0;

        if ($speed <= 0) {
            return 0;
        }

        $tripTime = ($trackLength * 2) / $speed;
        $cooldownTime = 300;
        $totalTimePerRun = $tripTime + $cooldownTime;

        $openingTime = strtotime($coasterData['godziny_od']);
        $closingTime = strtotime($coasterData['godziny_do']);
        $operationTime = $closingTime - $openingTime;

        $maxRuns = floor($operationTime / $totalTimePerRun);
        return max(0, $maxRuns * $seats);
    }
}
