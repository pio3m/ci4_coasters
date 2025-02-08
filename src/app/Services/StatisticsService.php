<?php
namespace App\Services;

use Config\Services;
use App\Services\RedisService;
use App\Services\CapacityService;
use Psr\Log\LoggerInterface;

class StatisticsService
{
    private RedisService $redisService;
    private CapacityService $capacityService;
    private LoggerInterface $logger;
    private string $prefix;

    public function __construct()
    {
        $this->redisService = new RedisService();
        $this->capacityService = new CapacityService();
        $this->logger = Services::logger();
        $this->prefix = \App\Services\ConfigService::getRedisPrefix();
    }

    public function getSystemStatistics(): array
    {
        $coasterKeys = $this->redisService->getAllCoasters();
        $statistics = [];

        foreach ($coasterKeys as $coasterId) {
            $coasterData = $this->redisService->getCoaster($coasterId, true);
            //$coasterId usunac prefix samo id
            $capacityStats = $this->capacityService->checkCapacity($coasterId);

            if (isset($capacityStats['error'])) {
                continue;
            }

            $missingWagons = $capacityStats['missing_wagons'] ?? 0;
            $missingPersonnel = $capacityStats['missing_personnel'] ?? 0;

            $status = "OK";
            if ($missingWagons > 0 || $missingPersonnel > 0) {
                $status = "Problem: Brakuje $missingWagons wagonów, brak $missingPersonnel pracowników";
                $this->logIssue($coasterId, $status, $capacityStats);
            }

            $statistics[] = [
                "coaster_id" => $coasterId,
                "godziny_dzialania" => isset($coasterData['godziny_od'], $coasterData['godziny_do']) ? "{$coasterData['godziny_od']} - {$coasterData['godziny_do']}" : "Brak danych",
                "liczba_wagonow" => count($this->redisService->getWagonsForCoaster($coasterId)),
                "liczba_personelu" => "{$capacityStats['available_personnel']}/{$capacityStats['required_personnel']}",
                "liczba_klientow" => $capacityStats['daily_clients'],
                "status" => $status
            ];
        }

        return $statistics;
    }

    private function logIssue(string $coasterId, string $message, array $capacityStats)
    {
        $logMessage = "[" . date("Y-m-d H:i:s") . "] Kolejka $coasterId - $message";

        $this->logger->error($logMessage);

        $event = [
            "coaster_id" => $coasterId,
            "type" => "alert",
            "missing_wagons" => $capacityStats['missing_wagons'] ?? 0,
            "missing_personnel" => $capacityStats['missing_personnel'] ?? 0,
            "timestamp" => date("Y-m-d H:i:s")
        ];

        $this->redisService->publishEvent($coasterId, "alert", $event);
    }
}

