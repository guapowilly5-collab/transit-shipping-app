<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté'
    ]);
    exit;
}

$conn = getConnection();
$role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

// Admin et agent voient toutes les commandes
// Client voit uniquement ses propres commandes
if ($role === 'admin' || $role === 'agent') {
    $stmt = $conn->prepare('
        SELECT c.id, c.reference, c.statut, c.canal, c.notes, c.created_at,
               n.nom AS navire, u.nom AS agent
        FROM commandes c
        LEFT JOIN navires n ON c.navire_id = n.id
        LEFT JOIN users u ON c.agent_id = u.id
        ORDER BY c.created_at DESC
    ');
    $stmt->execute();
} else {
    $stmt = $conn->prepare('
        SELECT c.id, c.reference, c.statut, c.canal, c.notes, c.created_at,
               n.nom AS navire, u.nom AS agent
        FROM commandes c
        LEFT JOIN navires n ON c.navire_id = n.id
        LEFT JOIN users u ON c.agent_id = u.id
        WHERE n.user_id = ?
        ORDER BY c.created_at DESC
    ');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
}

$result    = $stmt->get_result();
$commandes = [];

while ($row = $result->fetch_assoc()) {
    $commandes[] = $row;
}

echo json_encode([
    'success'   => true,
    'total'     => count($commandes),
    'commandes' => $commandes
]);

$stmt->close();
$conn->close();
?>