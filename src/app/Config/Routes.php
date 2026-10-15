<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('comments', 'Comments::index');
$routes->post('comments', 'Comments::create');
$routes->delete('comments/(:num)', 'Comments::delete/$1');
