 <?php

function verifierSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Non authentifié. Veuillez vous connecter'
        ]);
        exit;
    }

    return [
        'user_id'  => $_SESSION['user_id'],
        'user_nom' => $_SESSION['user_nom'],
        'user_role'=> $_SESSION['user_role']
    ];
}

function verifierRole(...$roles) {
    $user = verifierSession();

    if (!in_array($user['user_role'], $roles)) {
        echo json_encode([
            'success' => false,
            'message' => 'Accès refusé. Vous n\'avez pas les droits nécessaires'
        ]);
        exit;
    }

    return $user;
}

// ── FONCTIONS RACCOURCIS PAR RÔLE ────────────────────────────────

function adminSeulement() {
    return verifierRole('admin');
}

function adminOuAgent() {
    return verifierRole('admin', 'agent');
}

function clientConnecte() {
    return verifierRole('admin', 'agent', 'client');
}
