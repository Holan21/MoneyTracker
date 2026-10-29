<?php

declare(strict_types=1);

session_start();

require dirname(__DIR__) . '/app/bootstrap.php';

$router = new Router();

$router->get('/', [DashboardController::class, 'index']);

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/uploads/{file}', [FileController::class, 'show']);


$router->get('/categories', [CategoryController::class, 'index']);
$router->get('/categories/create', [CategoryController::class, 'create']);
$router->post('/categories', [CategoryController::class, 'store']);
$router->get('/categories/{id}', [CategoryController::class, 'show']);
$router->get('/categories/{id}/edit', [CategoryController::class, 'edit']);
$router->post('/categories/{id}', [CategoryController::class, 'update']);
$router->post('/categories/{id}/delete', [CategoryController::class, 'destroy']);

$router->get('/transactions', [TransactionController::class, 'index']);
$router->get('/transactions/create', [TransactionController::class, 'create']);
$router->post('/transactions', [TransactionController::class, 'store']);
$router->get('/transactions/{id}', [TransactionController::class, 'show']);
$router->get('/transactions/{id}/edit', [TransactionController::class, 'edit']);
$router->post('/transactions/{id}', [TransactionController::class, 'update']);
$router->post('/transactions/{id}/delete', [TransactionController::class, 'destroy']);

$router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
