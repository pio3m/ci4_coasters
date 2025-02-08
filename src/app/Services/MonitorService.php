<?php
namespace App\Services;

use CodeIgniter\CLI\CLI;
use Config\Services;

class MonitorService
{
    private \Redis $redis;
    private $logger;
    private string $prefix;
    private string $eventChannel;
    private string $alertChannel;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('redis', 6379);
        $this->logger = Services::logger();
        $this->prefix = \App\Services\ConfigService::getRedisPrefix();

        $this->eventChannel = "{$this->prefix}coaster_events";
        $this->alertChannel = "{$this->prefix}coaster_alerts";
    }

    public function run()
    {
        CLI::write("Nasłuchiwanie na kanałach Redis: {$this->eventChannel}, {$this->alertChannel}", 'yellow');

        $this->redis->subscribe([$this->eventChannel, $this->alertChannel], function ($redis, $channel, $message) {
            $this->handleEvent($channel, $message);
        });
    }

    private function handleEvent(string $channel, string $message)
    {
        $event = json_decode($message, true);

        if (!$event || !isset($event['coaster_id'])) {
            CLI::write("Błędne zdarzenie na kanale $channel: " . $message, 'red');
            return;
        }

        if ($channel === $this->eventChannel) {
            $logMessage = "[{$event['coaster_id']}] Zdarzenie: {$event['type']} | Szczegóły: " . json_encode($event['data']);
            CLI::write($logMessage, 'blue');
            $this->logger->info($logMessage);
        }

        if ($channel === $this->alertChannel) {
            $logMessage = "[ALERT] Kolejka {$event['coaster_id']} - Brakuje wagonów: {$event['missing_wagons']}, Brakuje personelu: {$event['missing_personnel']}";
            CLI::write($logMessage, 'red');
            $this->logger->error($logMessage);
        }
    }
}
