<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middlewares/auth.php';

class OrderController {

    // ── LISTER TOUTES LES COMMANDES (admin/agent) ────────────────
    public function lister() {
        adminOuAgent();

        $conn = getConnection();
        $sql  = "SELECT c.*, n.nom AS navire, n.numero_imo,
                        u.nom AS agent_nom
                 FROM commandes c
                 JOIN navires n ON c.navire_id = n.id
                 LEFT JOIN users u ON c.agent_id = u.id
                 ORDER BY c.created_at DESC";

        $result    = $conn->query($sql);
        $commandes = [];
        while ($row = $result->fetch_assoc()) {
            $commandes[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data'    => $commandes
        ]);

        $conn->close();
    }

    // ── MES COMMANDES (client connecté) ──────────────────────────
    public function mesCommandes() {
        $user = clientConnecte();

        $conn = getConnection();
        $stmt = $conn->prepare(
            "SELECT c.*, n.nom AS navire
             FROM commandes c
             JOIN navires n ON c.navire_id = n.id
             WHERE n.user_id = ?
             ORDER BY c.created_at DESC"
        );
        $stmt->bind_param("i", $user['user_id']);
        $stmt->execute();
        $result    = $stmt->get_result();
        $commandes = [];
        while ($row = $result->fetch_assoc()) {
            $commandes[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data'    => $commandes
        ]);

        $stmt->close();
        $conn->close();
    }

    // ── DÉTAIL D'UNE COMMANDE ─────────────────────────────────────
    public function detail($id) {
        $user = clientConnecte();

        $conn = getConnection();

        // Récupérer la commande
        $stmt = $conn->prepare(
            "SELECT c.*, n.nom AS navire, n.numero_imo
             FROM commandes c
             JOIN navires n ON c.navire_id = n.id
             WHERE c.id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $commande = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$commande) {
            echo json_encode([
                'success' => false,
                'message' => 'Commande introuvable'
            ]);
            return;
        }

        // Récupérer les items de la commande
        $stmt2 = $conn->prepare(
            "SELECT ci.*, p.nom AS produit, p.unite
             FROM commande_items ci
             JOIN produits p ON ci.produit_id = p.id
             WHERE ci.commande_id = ?"
        );
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $items  = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt2->close();

        $commande['items'] = $items;

        echo json_encode([
            'success' => true,
            'data'    => $commande
        ]);

        $conn->close();
    }

    // ── CRÉER UNE COMMANDE EN LIGNE (client) ─────────────────────
    public function creerEnLigne() {
        $user = clientConnecte();

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['navire_id']) || empty($data['items'])) {
            echo json_encode([
                'success' => false,
                'message' => 'navire_id et items sont obligatoires'
            ]);
            return;
        }

        $conn = getConnection();

        // Générer une référence unique
        $reference = 'LMS-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Créer la commande
        $stmt = $conn->prepare(
            "INSERT INTO commandes (reference, navire_id, statut, canal, notes)
             VALUES (?, ?, 'en_attente', 'online', ?)"
        );
        $notes = $data['notes'] ?? '';
        $stmt->bind_param("sis", $reference, $data['navire_id'], $notes);

        if (!$stmt->execute()) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la création de la commande'
            ]);
            $stmt->close();
            $conn->close();
            return;
        }

        $commande_id = $conn->insert_id;
        $stmt->close();

        // Insérer les items
        $total = 0;
        foreach ($data['items'] as $item) {
            // Récupérer le prix du produit
            $stmt2 = $conn->prepare("SELECT montant FROM prix WHERE produit_id = ?");
            $stmt2->bind_param("i", $item['produit_id']);
            $stmt2->execute();
            $prix_row    = $stmt2->get_result()->fetch_assoc();
            $prix_unitaire = $prix_row ? $prix_row['montant'] : 0;
            $sous_total    = $prix_unitaire * $item['quantite'];
            $total        += $sous_total;
            $stmt2->close();

            $stmt3 = $conn->prepare(
                "INSERT INTO commande_items (commande_id, produit_id, quantite, prix_unitaire, sous_total)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt3->bind_param("iiddd",
                $commande_id, $item['produit_id'],
                $item['quantite'], $prix_unitaire, $sous_total
            );
            $stmt3->execute();
            $stmt3->close();
        }

        echo json_encode([
            'success'     => true,
            'message'     => 'Commande créée avec succès',
            'commande_id' => $commande_id,
            'reference'   => $reference,
            'total'       => $total
        ]);

        $conn->close();
    }

    // ── SAISIR UNE COMMANDE PAR AGENT (WhatsApp/email/tel) ───────
    public function creerParAgent() {
        adminOuAgent();

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['navire_id']) || empty($data['items']) || empty($data['canal'])) {
            echo json_encode([
                'success' => false,
                'message' => 'navire_id, items et canal sont obligatoires'
            ]);
            return;
        }

        $conn = getConnection();

        // Session pour récupérer l'agent
        if (session_status() === PHP_SESSION_NONE) session_start();
        $agent_id = $_SESSION['user_id'];

        // Générer référence
        $reference = 'LMS-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $stmt = $conn->prepare(
            "INSERT INTO commandes (reference, navire_id, agent_id, statut, canal, notes)
             VALUES (?, ?, ?, 'en_attente', ?, ?)"
        );
        $notes = $data['notes'] ?? '';
        $stmt->bind_param("siiss", $reference, $data['navire_id'], $agent_id, $data['canal'], $notes);

        if (!$stmt->execute()) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la création'
            ]);
            $stmt->close();
            $conn->close();
            return;
        }

        $commande_id = $conn->insert_id;
        $stmt->close();

        // Insérer les items
        $total = 0;
        foreach ($data['items'] as $item) {
            $stmt2 = $conn->prepare("SELECT montant FROM prix WHERE produit_id = ?");
            $stmt2->bind_param("i", $item['produit_id']);
            $stmt2->execute();
            $prix_row      = $stmt2->get_result()->fetch_assoc();
            $prix_unitaire = $prix_row ? $prix_row['montant'] : 0;
            $sous_total    = $prix_unitaire * $item['quantite'];
            $total        += $sous_total;
            $stmt2->close();

            $stmt3 = $conn->prepare(
                "INSERT INTO commande_items (commande_id, produit_id, quantite, prix_unitaire, sous_total)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt3->bind_param("iiddd",
                $commande_id, $item['produit_id'],
                $item['quantite'], $prix_unitaire, $sous_total
            );
            $stmt3->execute();
            $stmt3->close();
        }

        echo json_encode([
            'success'     => true,
            'message'     => 'Commande saisie avec succès (canal : ' . $data['canal'] . ')',
            'commande_id' => $commande_id,
            'reference'   => $reference,
            'total'       => $total
        ]);

        $conn->close();
    }

    // ── CHANGER STATUT COMMANDE (admin/agent) ────────────────────
    public function changerStatut($id) {
        adminOuAgent();

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['statut'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Le statut est obligatoire'
            ]);
            return;
        }

        $statuts_valides = ['en_attente', 'confirmee', 'en_preparation', 'livree', 'annulee'];
        if (!in_array($data['statut'], $statuts_valides)) {
            echo json_encode([
                'success' => false,
                'message' => 'Statut invalide'
            ]);
            return;
        }

        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
        $stmt->bind_param("si", $data['statut'], $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Statut mis à jour : ' . $data['statut']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ]);
        }

        $stmt->close();
        $conn->close();
    }
}