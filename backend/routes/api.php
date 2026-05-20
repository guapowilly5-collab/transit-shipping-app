<?php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../middlewares/auth.php';

$auth     = new AuthController();
$category = new CategoryController();
$product  = new ProductController();

// Récupérer la méthode HTTP et l'URL
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = str_replace('/transit-shipping-app/backend/index.php', '', $uri);

// Extraire l'ID si présent dans l'URL (ex: /categories/5)
$parts = explode('/', trim($uri, '/'));
$id    = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : null;
$base  = '/' . ($parts[0] ?? '');

// ── ROUTES AUTH ───────────────────────────────────────────────────
if ($method === 'POST' && $uri === '/auth/inscription') {
    $auth->inscription();

} elseif ($method === 'POST' && $uri === '/auth/connexion') {
    $auth->connexion();

} elseif ($method === 'POST' && $uri === '/auth/deconnexion') {
    $auth->deconnexion();

} elseif ($method === 'POST' && $uri === '/auth/valider-compte') {
    $auth->validerCompte();

// ── ROUTES CATÉGORIES ─────────────────────────────────────────────
} elseif ($method === 'GET' && $base === '/categories') {
    $category->lister();

} elseif ($method === 'POST' && $base === '/categories') {
    $category->creer();

} elseif ($method === 'PUT' && $base === '/categories' && $id) {
    $category->modifier($id);

} elseif ($method === 'DELETE' && $base === '/categories' && $id) {
    $category->supprimer($id);

// ── ROUTES PRODUITS ───────────────────────────────────────────────
} elseif ($method === 'GET' && $uri === '/produits') {
    $product->listerPublic();

} elseif ($method === 'GET' && $uri === '/produits/client') {
    $product->listerClient();

} elseif ($method === 'POST' && $base === '/produits') {
    $product->creer();

} elseif ($method === 'PUT' && $base === '/produits' && $id) {
    $product->modifier($id);

} elseif ($method === 'DELETE' && $base === '/produits' && $id) {
    $product->supprimer($id);

// ── ROUTE PAR DÉFAUT ──────────────────────────────────────────────
} else {
    echo json_encode([
        'success'  => true,
        'message'  => 'API Lome Marine operationnelle',
        'database' => 'connectee',
        'routes'   => [
            'POST /auth/inscription',
            'POST /auth/connexion',
            'POST /auth/deconnexion',
            'POST /auth/valider-compte',
            'GET  /categories',
            'POST /categories',
            'PUT  /categories/:id',
            'DELETE /categories/:id',
            'GET  /produits',
            'GET  /produits/client',
            'POST /produits',
            'PUT  /produits/:id',
            'DELETE /produits/:id',
        ]
    ]);
}