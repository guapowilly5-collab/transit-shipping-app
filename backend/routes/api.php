<?php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../middlewares/auth.php';

$auth = new AuthController();

// Récupérer la méthode HTTP et l'URL
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/transit-shipping-app/backend/index.php', '', $uri);

// ── ROUTES AUTH ───────────────────────────────────────────────────
// POST /auth/inscription
if ($method === 'POST' && $uri === '/auth/inscription') {
    $auth->inscription();

// POST /auth/connexion
} elseif ($method === 'POST' && $uri === '/auth/connexion') {
    $auth->connexion();

// POST /auth/deconnexion
} elseif ($method === 'POST' && $uri === '/auth/deconnexion') {
    $auth->deconnexion();

// POST /auth/valider-compte (admin seulement)
} elseif ($method === 'POST' && $uri === '/auth/valider-compte') {
    $auth->validerCompte();

// ── ROUTE PAR DÉFAUT ──────────────────────────────────────────────
} else {
    echo json_encode([
        'success' => true,
        'message' => 'API Lome Marine operationnelle',
        'database'=> 'connectee',
        'routes'  => [
            'POST /auth/inscription',
            'POST /auth/connexion',
            'POST /auth/deconnexion',
            'POST /auth/valider-compte'
        ]
    ]);
}