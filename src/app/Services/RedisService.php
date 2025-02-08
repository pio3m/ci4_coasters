<?php
namespace App\Services;

class RedisService
{
    private \Redis $redis;
    private string $prefix;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('redis', 6379);
        $this->prefix = ConfigService::getRedisPrefix();
    }

    public function saveCoaster(string $id, array $data)
    {
        $this->redis->hMSet("{$this->prefix}coaster:$id", $data);
    }

    public function getCoaster(string $id, bool $withPrefix = false): ?array
    {
        if($withPrefix) {
            $data = $this->redis->hGetAll("$id");
        } else {
            $data = $this->redis->hGetAll("{$this->prefix}coaster:$id");
        }

        return !empty($data) ? $data : null;
    }

    public function addWagon(string $coasterId, string $wagonId, array $wagonData)
    {
        if (!$this->getCoaster($coasterId)) {
            return false;
        }

        $wagonData['wagon_id'] = $wagonId;
        $this->redis->hMSet("{$this->prefix}wagon:$wagonId", $wagonData);
        $this->redis->lPush("{$this->prefix}coaster:$coasterId:wagons", $wagonId);

        return true;
    }

    public function removeWagon(string $coasterId, string $wagonId): bool
    {
        if (!$this->getCoaster($coasterId)) {
            return false;
        }

        $wagonExists = $this->redis->hGetAll("{$this->prefix}wagon:$wagonId");
        if (empty($wagonExists)) {
            return false;
        }

        $this->redis->lRem("{$this->prefix}coaster:$coasterId:wagons", 0, $wagonId);
        $this->redis->del("{$this->prefix}wagon:$wagonId");

        return true;
    }

    /**
     * @throws \RedisException
     */
    public function updateCoaster(string $coasterId, array $data): bool
    {
        if (!$this->getCoaster($coasterId)) {
            return false;
        }

        $existingData = $this->redis->hGetAll("{$this->prefix}coaster:$coasterId");
        $updatedData = array_merge($existingData, $data);
        unset($updatedData['dl_trasy']);

        $this->redis->hMSet("{$this->prefix}coaster:$coasterId", $updatedData);

        return true;
    }

    public function publishEvent(string $coasterId, string $eventType, array $data)
    {
        $channel = "{$this->prefix}coaster_events";

        $event = [
            "coaster_id" => $coasterId,
            "type" => $eventType,
            "data" => $data,
            "timestamp" => date("Y-m-d H:i:s")
        ];

        $this->redis->publish($channel, json_encode($event));
    }
    public function getAllCoasters(): array
    {
        return $this->redis->keys("{$this->prefix}coaster:*");
    }

    public function getWagonsForCoaster(string $coasterId): array
    {
        return $this->redis->lRange("{$this->prefix}coaster:$coasterId:wagons", 0, -1);
    }

}
