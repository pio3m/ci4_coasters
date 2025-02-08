<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Services\RedisService;

class WagonsController extends ResourceController
{
    private RedisService $redisService;

    public function __construct()
    {
        $this->redisService = new RedisService();
    }

    public function create($coasterId = null)
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['ilosc_miejsc'], $data['predkosc_wagonu'])) {
            return $this->fail("Brak wymaganych pól", 400);
        }

        $wagonId = uniqid("wagon_");

        if (!$this->redisService->addWagon($coasterId, $wagonId, $data)) {
            return $this->failNotFound("Kolejka górska nie istnieje");
        }

        $this->redisService->publishEvent($coasterId, "wagon_added", ["wagon_id" => $wagonId, "data" => $data]);

        return $this->respondCreated([
            "message" => "Wagon został dodany",
            "wagon_id" => $wagonId,
            "data" => $data
        ]);
    }
    public function delete($coasterId = null, $wagonId = null)
    {
        if (!$this->redisService->removeWagon($coasterId, $wagonId)) {
            return $this->failNotFound("Wagon nie istnieje lub kolejka jest nieprawidłowa");
        }

        $this->redisService->publishEvent($coasterId, "wagon_removed", ["wagon_id" => $wagonId]);

        return $this->respondDeleted([
            "message" => "Wagon został usunięty",
            "wagon_id" => $wagonId
        ]);
    }


    public function index($coasterId = null)
    {
        $wagons = $this->redisService->getWagons($coasterId);

        if (empty($wagons)) {
            return $this->failNotFound("Brak wagonów dla tej kolejki");
        }

        return $this->respond($wagons);
    }
}
