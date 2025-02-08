<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('api/coasters', 'CoastersController::create');
$routes->post('api/coasters/(:segment)/wagons', 'WagonsController::create/$1');
$routes->get('api/coasters/(:segment)/wagons', 'WagonsController::index/$1');
$routes->delete('api/coasters/(:segment)/wagons/(:segment)', 'WagonsController::delete/$1/$2');
$routes->put('api/coasters/(:segment)', 'CoastersController::update/$1');

$routes->get('api/statistics', 'CoastersController::getStatistics');
