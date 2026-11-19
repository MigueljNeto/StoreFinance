<?php

    $router = new Router();

    $router->get('/', AuthController::class, 'login');
    $router->get('/login', AuthController::class, 'login');
    $router->post('/login', AuthController::class, 'authenticate');
    $router->get('/register', AuthController::class, 'register');
    $router->post('/register', AuthController::class, 'store');
    $router->post('/logout', AuthController::class, 'logout');

    $router->get('/dashboard', DashboardController::class, 'index');

    $router->get('/tasks/create', TaskController::class, 'create');
    $router->post('/tasks', TaskController::class, 'store');
    $router->get('/tasks/edit', TaskController::class, 'edit');
    $router->post('/tasks/update', TaskController::class, 'update');
    $router->post('/tasks/start', TaskController::class, 'start');
    $router->post('/tasks/toggle', TaskController::class, 'toggle');
    $router->post('/tasks/delete', TaskController::class, 'destroy');

    $router->get('/transactions', TransactionController::class, 'index');
    $router->get('/transactions/create', TransactionController::class, 'create');
    $router->post('/transactions', TransactionController::class, 'store');
    $router->get('/transactions/edit', TransactionController::class, 'edit');
    $router->post('/transactions/update', TransactionController::class, 'update');
    $router->post('/transactions/delete', TransactionController::class, 'destroy');

    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
