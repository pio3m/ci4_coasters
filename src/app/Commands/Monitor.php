<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\MonitorService;

class Monitor extends BaseCommand
{
    protected $group = 'custom';
    protected $name = 'monitor';
    protected $description = 'Uruchamia monitoring kolejek górskich';

    public function run(array $params)
    {
        $monitorService = new MonitorService();
        $monitorService->run();
    }
}
