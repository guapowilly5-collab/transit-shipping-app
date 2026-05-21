<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/database.php';

// Vérifier que c'est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

// Récupérer les données envoyées
$donnees = json_decode(file_get_contents('php://input'), true);

// Vérifier que les champs obligatoires sont présents
if (empty($donnees['email']) || empty($donnees['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email et mot de passe sont obligatoires'
    ]);
    exit;
}

$email    = trim($donnees['email']);
$password = $donnees['password'];
$role     = isset($donnees['role']) ? $donnees['role'] : 'client';

// Se connecter à la base de données
$conn = getConnection();

// Chercher l'utilisateur par email
$stmt = $conn->prepare('SELECT id, nom, email, password, role, statut FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

// Vérifier si l'utilisateur existe
if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Email ou mot de passe incorrect'
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Vérifier le mot de passe
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email ou mot de passe incorrect'
    ]);
    $conn->close();
    exit;
}

// Vérifier le statut du compte
if ($user['statut'] === 'en_attente') {
    echo json_encode([
        'success' => false,
        'message' => 'Votre compte est en attente de validation par l\'administrateur'
    ]);
    $conn->close();
    exit;
}

if ($user['statut'] === 'suspendu') {
    echo json_encode([
        'success' => false,
        'message' => 'Votre compte a été suspendu. Contactez l\'administrateur.'
    ]);
    $conn->close();
    exit;
}

// Vérifier que le rôle correspond
if ($user['role'] !== $role) {
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé pour ce rôle'
    ]);
    $conn->close();
    exit;
}

// Démarrer la session et enregistrer les infos utilisateur
session_start();
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_nom']  = $user['nom'];
$_SESSION['user_email']= $user['email'];
$_SESSION['user_role'] = $user['role'];

// Retourner les infos de l'utilisateur connecté
echo json_encode([
    'success' => true,
    'message' => 'Connexion réussie',
    'user' => [
        'id'    => $user['id'],
        'nom'   => $user['nom'],
        'email' => $user['email'],
        'role'  => $user['role']
    ]
]);

$conn->close();
?>