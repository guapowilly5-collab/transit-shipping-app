<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

session_start();

// Vérifier que l'utilisateur est connecté et est admin ou agent
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté'
    ]);
    exit;
}

if (!in_array($_SESSION['user_role'], ['admin', 'agent'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé — réservé aux admins et agents'
    ]);
    exit;
}

$donnees = json_decode(file_get_contents('php://input'), true);

// Vérifier les champs obligatoires
if (empty($donnees['commande_id']) || empty($donnees['statut'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID commande et statut sont obligatoires'
    ]);
    exit;
}

$commande_id = intval($donnees['commande_id']);
$statut      = trim($donnees['statut']);

// Vérifier que le statut est valide
$statuts_valides = ['en_attente', 'confirmee', 'en_preparation', 'livree', 'annulee'];
if (!in_array($statut, $statuts_valides)) {
    echo json_encode([
        'success' => false,
        'message' => 'Statut invalide. Valeurs acceptées : en_attente, confirmee, en_preparation, livree, annulee'
    ]);
    exit;
}

$conn = getConnection();

// Vérifier que la commande existe
$stmt = $conn->prepare('SELECT id FROM commandes WHERE id = ?');
$stmt->bind_param('i', $commande_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Commande introuvable'
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();

// Mettre à jour le statut
$stmt = $conn->prepare('UPDATE commandes SET statut = ? WHERE id = ?');
$stmt->bind_param('si', $statut, $commande_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Statut mis à jour avec succès',
        'commande_id' => $commande_id,
        'nouveau_statut' => $statut
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour : ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>