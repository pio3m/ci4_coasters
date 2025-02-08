<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Services\RedisService;

class CoastersController extends ResourceController
{
    private RedisService $redisService;

    public function __construct()
    {
        $this->redisService = new RedisService();
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['liczba_personelu'], $data['liczba_klientow'], $data['dl_trasy'], $data['godziny_od'], $data['godziny_do'])) {
            return $this->fail("Brak wymaganych pól", 400);
        }

        $id = uniqid();
        $this->redisService->saveCoaster($id, $data);
        $this->redisService->publishEvent($id, "coaster_created", $data);

        return $this->respondCreated([
            "message" => "Kolejka górska została dodana",
            "id" => $id,
            "data" => $data
        ]);
    }

    public function update($coasterId = null)
    {
        $data = $this->request->getJSON(true);

        if (!$this->redisService->updateCoaster($coasterId, $data)) {
            return $this->failNotFound("Kolejka nie istnieje");
        }

        return $this->respond([
            "message" => "Kolejka została zaktualizowana",
            "coaster_id" => $coasterId,
            "updated_data" => $data
        ]);
    }

    public function getStatistics()
    {
        $statisticsService = new \App\Services\StatisticsService();
        $result = $statisticsService->getSystemStatistics();

        return $this->respond($result);
    }
}
