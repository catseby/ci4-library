<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'BookController::index');
$routes->get('/categories', 'BookController::categories');
$routes->post('/books', 'BookController::add');
$routes->post('/books/filter', 'BookController::fetchFiltered');
$routes->get('/books', 'BookController::fetch');
$routes->get('/books/(:num)', 'BookController::edit/$1');
$routes->post('/books/(:num)', 'BookController::update/$1');
$routes->delete('/books/(:num)', 'BookController::destroy/$1');



