<?php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/NavireController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../middlewares/auth.php';

$auth    = new AuthController();
$category = new CategoryController();
$product  = new ProductController();
$navire   = new NavireController();
$order    = new OrderController();

// Récupérer la méthode HTTP et l'URL
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = str_replace('/transit-shipping-app/backend/index.php', '', $uri);

// Extraire l'ID si présent
$parts = explode('/', trim($uri, '/'));
$id    = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : null;
$base  = '/' . ($parts[0] ?? '');
$sub   = isset($parts[1]) ? '/' . $parts[1] : '';

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

// ── ROUTES NAVIRES ────────────────────────────────────────────────
} elseif ($method === 'GET' && $uri === '/navires') {
    $navire->lister();

} elseif ($method === 'GET' && $uri === '/navires/mon-navire') {
    $navire->monNavire();

} elseif ($method === 'POST' && $base === '/navires') {
    $navire->creer();

} elseif ($method === 'PUT' && $base === '/navires' && $id) {
    $navire->modifier($id);

} elseif ($method === 'DELETE' && $base === '/navires' && $id) {
    $navire->supprimer($id);

// ── ROUTES COMMANDES ──────────────────────────────────────────────
} elseif ($method === 'GET' && $uri === '/commandes') {
    $order->lister();

} elseif ($method === 'GET' && $uri === '/commandes/mes-commandes') {
    $order->mesCommandes();

} elseif ($method === 'GET' && $base === '/commandes' && $id) {
    $order->detail($id);

} elseif ($method === 'POST' && $uri === '/commandes/en-ligne') {
    $order->creerEnLigne();

} elseif ($method === 'POST' && $uri === '/commandes/agent') {
    $order->creerParAgent();

} elseif ($method === 'PUT' && $base === '/commandes' && $id) {
    $order->changerStatut($id);

// ── ROUTE PAR DÉFAUT ──────────────────────────────────────────────
} else {
    echo json_encode([
        'success'  => true,
        'message'  => 'API Lome Marine operationnelle',
        'database' => 'connectee',
        'version'  => '1.0',
        'routes'   => [
            'AUTH'       => ['POST /auth/inscription', 'POST /auth/connexion', 'POST /auth/deconnexion', 'POST /auth/valider-compte'],
            'CATEGORIES' => ['GET /categories', 'POST /categories', 'PUT /categories/:id', 'DELETE /categories/:id'],
            'PRODUITS'   => ['GET /produits', 'GET /produits/client', 'POST /produits', 'PUT /produits/:id', 'DELETE /produits/:id'],
            'NAVIRES'    => ['GET /navires', 'GET /navires/mon-navire', 'POST /navires', 'PUT /navires/:id', 'DELETE /navires/:id'],
            'COMMANDES'  => ['GET /commandes', 'GET /commandes/mes-commandes', 'GET /commandes/:id', 'POST /commandes/en-ligne', 'POST /commandes/agent', 'PUT /commandes/:id'],
        ]
    ]);
}