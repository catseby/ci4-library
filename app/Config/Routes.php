<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'BookController::index');
$routes->get('/categories', 'BookController::categories');
$routes->get('/categories/form', 'BookController::categoryForm');
$routes->post('/books', 'BookController::add');

$routes->get('/books', 'BookController::fetch');
$routes->get('/books/(:num)', 'BookController::edit/$1');
$routes->post('/books/(:num)', 'BookController::update/$1');
$routes->delete('/books/(:num)', 'BookController::destroy/$1');

$routes->get('/forms/(:alpha)/fetch', 'FormController::fetch/$1');

$routes->get('/forms/(:alpha)/add', 'FormController::index/$1');
$routes->post('/forms/(:alpha)/add', 'FormController::add/$1');

$routes->get('/forms/(:alpha)/edit/(:num)', 'FormController::edit/$1/$2/id');
$routes->get('/forms/(:alpha)/edit/(:num)/(:segment)', 'FormController::edit/$1/$2/$3');
$routes->post('/forms/(:alpha)/edit/(:num)', 'FormController::update/$1/$2/id');
$routes->post('/forms/(:alpha)/edit/(:num)/(:segment)', 'FormController::update/$1/$2/$3');

$routes->get('/forms/(:alpha)/delete/(:num)', 'FormController::destroy/$1/$2');




