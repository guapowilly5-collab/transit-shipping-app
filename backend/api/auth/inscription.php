<?php
// Autoriser les requêtes depuis le frontend
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Inclure la connexion à la base de données
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
if (empty($donnees['nom']) || empty($donnees['email']) || empty($donnees['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Nom, email et mot de passe sont obligatoires'
    ]);
    exit;
}

// Récupérer et nettoyer les données
$nom      = trim($donnees['nom']);
$email    = trim($donnees['email']);
$password = $donnees['password'];

// Vérifier que l'email est valide
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Adresse email invalide'
    ]);
    exit;
}

// Vérifier que le mot de passe fait au moins 8 caractères
if (strlen($password) < 8) {
    echo json_encode([
        'success' => false,
        'message' => 'Le mot de passe doit contenir au moins 8 caractères'
    ]);
    exit;
}

// Se connecter à la base de données
$conn = getConnection();

// Vérifier si l'email existe déjà
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Cet email est déjà utilisé'
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();

// Hasher le mot de passe
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insérer le nouvel utilisateur
// Le rôle est 'client' et le statut 'en_attente' par défaut
$stmt = $conn->prepare('INSERT INTO users (nom, email, password, role, statut) VALUES (?, ?, ?, "client", "en_attente")');
$stmt->bind_param('sss', $nom, $email, $password_hash);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Compte créé avec succès. En attente de validation par l\'administrateur.',
        'user_id' => $user_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la création du compte : ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>