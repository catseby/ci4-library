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

$routes->get('/forms/(:alpha)/(:num)/edit', 'FormController::edit/$1/$2');
$routes->post('/forms/(:alpha)/(:num)/edit', 'FormController::update/$1/$2');

$routes->get('/forms/(:alpha)/(:num)/delete', 'FormController::destroy/$1/$2');




