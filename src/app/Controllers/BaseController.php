<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use Config\App;

class BaseController extends Controller
{
    protected $helpers = [];

    public function __construct()
    {
        $this->restrictInDevelopment();
    }

    protected function restrictInDevelopment()
    {
        if (ENVIRONMENT === 'development') {
            $allowedIps = ['127.0.0.2'];
            $clientIp = $this->request->getIPAddress();

            if (!in_array($clientIp, $allowedIps)) {
                exit(json_encode(["error" => "Dostęp zabroniony w trybie developerskim."], JSON_PRETTY_PRINT));
            }
        }
    }
}
