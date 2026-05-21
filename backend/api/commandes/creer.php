<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
        'message' => 'Vous devez être connecté pour passer une commande'
    ]);
    exit;
}

$donnees = json_decode(file_get_contents('php://input'), true);

// Vérifier les champs obligatoires
if (empty($donnees['navire_id']) || empty($donnees['canal']) || empty($donnees['items'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Navire, canal et produits sont obligatoires'
    ]);
    exit;
}

$navire_id = intval($donnees['navire_id']);
$canal     = trim($donnees['canal']);
$notes     = isset($donnees['notes']) ? trim($donnees['notes']) : '';
$agent_id  = $_SESSION['user_role'] === 'agent' ? $_SESSION['user_id'] : null;
$items     = $donnees['items'];

// Vérifier que le canal est valide
$canaux_valides = ['online', 'email', 'whatsapp', 'telephone'];
if (!in_array($canal, $canaux_valides)) {
    echo json_encode([
        'success' => false,
        'message' => 'Canal invalide. Valeurs acceptées : online, email, whatsapp, telephone'
    ]);
    exit;
}

// Vérifier que items n'est pas vide
if (count($items) === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'La commande doit contenir au moins un produit'
    ]);
    exit;
}

$conn = getConnection();

// Générer la référence unique : LMS-2025-XXXX
$annee = date('Y');
$stmt  = $conn->prepare('SELECT COUNT(*) as total FROM commandes');
$stmt->execute();
$result    = $stmt->get_result();
$row       = $result->fetch_assoc();
$numero    = str_pad($row['total'] + 1, 4, '0', STR_PAD_LEFT);
$reference = "LMS-{$annee}-{$numero}";
$stmt->close();

// Insérer la commande
$stmt = $conn->prepare('INSERT INTO commandes (reference, navire_id, agent_id, canal, notes) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('siiss', $reference, $navire_id, $agent_id, $canal, $notes);

if (!$stmt->execute()) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la création de la commande : ' . $conn->error
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$commande_id = $conn->insert_id;
$stmt->close();

// Insérer les items de la commande
foreach ($items as $item) {
    if (empty($item['produit_id']) || empty($item['quantite']) || empty($item['prix_unitaire'])) {
        continue;
    }

    $produit_id    = intval($item['produit_id']);
    $quantite      = intval($item['quantite']);
    $prix_unitaire = floatval($item['prix_unitaire']);
    $sous_total    = $quantite * $prix_unitaire;

    $stmt = $conn->prepare('INSERT INTO commande_items (commande_id, produit_id, quantite, prix_unitaire, sous_total) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('iiidd', $commande_id, $produit_id, $quantite, $prix_unitaire, $sous_total);
    $stmt->execute();
    $stmt->close();
}

echo json_encode([
    'success'      => true,
    'message'      => 'Commande créée avec succès',
    'commande_id'  => $commande_id,
    'reference'    => $reference
]);

$conn->close();
?>