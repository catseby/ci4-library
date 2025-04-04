<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


$routes->get('/forms', 'TableController::index');

$routes->post('/forms/(:segment)/fetch/datatables', 'TableController::fetch/$1');

$routes->post('/forms/add', 'TableController::add');
$routes->post('/forms/edit/(:segment)', 'TableController::update');
$routes->post('/forms/delete/(:segment)', 'TableController::destroy');

$routes->get('/forms/(:segment)/add', 'FormController::add/$1');
$routes->post('/forms/(:segment)/add', 'FormController::create/$1');

$routes->get('/forms/(:segment)/edit/(:num)', 'FormController::edit/$1/$2/id');
$routes->get('/forms/(:segment)/edit/(:num)/(:segment)', 'FormController::edit/$1/$2/$3');

$routes->post('/forms/(:segment)/edit/(:num)', 'FormController::update/$1/$2/id');
$routes->post('/forms/(:segment)/edit/(:num)/(:segment)', 'FormController::update/$1/$2/$3');

$routes->get('/forms/(:segment)/delete/(:num)', 'FormController::destroy/$1/$2/id');
$routes->get('/forms/(:segment)/delete/(:num)/(:segment)', 'FormController::destroy/$1/$2/$3');

service('auth')->routes($routes);





