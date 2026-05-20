<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middlewares/auth.php';

class ProductController {

    // ── LISTE PRODUITS PUBLIC (sans prix) ────────────────────────
    public function listerPublic() {
        $conn = getConnection();

        $sql = "SELECT p.id, p.nom, p.description, p.image, p.unite, p.stock, p.actif,
                       c.nom AS categorie
                FROM produits p
                JOIN categories c ON p.categorie_id = c.id
                WHERE p.actif = 1
                ORDER BY c.nom, p.nom ASC";

        $result = $conn->query($sql);
        $produits = [];
        while ($row = $result->fetch_assoc()) {
            $produits[] = $row;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Prix disponibles après connexion',
            'data'    => $produits
        ]);

        $conn->close();
    }

    // ── LISTE PRODUITS CLIENT (avec prix) ────────────────────────
    public function listerClient() {
        clientConnecte();

        $conn = getConnection();

        $sql = "SELECT p.id, p.nom, p.description, p.image, p.unite, p.stock, p.actif,
                       c.nom AS categorie,
                       pr.montant AS prix, pr.devise
                FROM produits p
                JOIN categories c ON p.categorie_id = c.id
                LEFT JOIN prix pr ON pr.produit_id = p.id
                WHERE p.actif = 1
                ORDER BY c.nom, p.nom ASC";

        $result = $conn->query($sql);
        $produits = [];
        while ($row = $result->fetch_assoc()) {
            $produits[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data'    => $produits
        ]);

        $conn->close();
    }

    // ── CRÉER UN PRODUIT (admin seulement) ───────────────────────
    public function creer() {
        adminSeulement();

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['nom']) || empty($data['categorie_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Nom et categorie_id sont obligatoires'
            ]);
            return;
        }

        $conn = getConnection();

        // Insérer le produit
        $stmt = $conn->prepare("INSERT INTO produits (nom, description, categorie_id, image, unite, stock) VALUES (?, ?, ?, ?, ?, ?)");
        $description  = $data['description'] ?? '';
        $image        = $data['image'] ?? '';
        $unite        = $data['unite'] ?? 'pièce';
        $stock        = $data['stock'] ?? 0;

        $stmt->bind_param("ssissi",
            $data['nom'], $description, $data['categorie_id'],
            $image, $unite, $stock
        );

        if (!$stmt->execute()) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la création du produit'
            ]);
            $stmt->close();
            $conn->close();
            return;
        }

        $produit_id = $conn->insert_id;
        $stmt->close();

        // Insérer le prix si fourni
        if (!empty($data['prix'])) {
            $devise = $data['devise'] ?? 'USD';
            $stmt2  = $conn->prepare("INSERT INTO prix (produit_id, montant, devise) VALUES (?, ?, ?)");
            $stmt2->bind_param("ids", $produit_id, $data['prix'], $devise);
            $stmt2->execute();
            $stmt2->close();
        }

        echo json_encode([
            'success'    => true,
            'message'    => 'Produit créé avec succès',
            'produit_id' => $produit_id
        ]);

        $conn->close();
    }

    // ── MODIFIER UN PRODUIT (admin seulement) ────────────────────
    public function modifier($id) {
        adminSeulement();

        $data = json_decode(file_get_contents('php://input'), true);
        $conn = getConnection();

        $stmt = $conn->prepare("UPDATE produits SET nom = ?, description = ?, categorie_id = ?, image = ?, unite = ?, stock = ? WHERE id = ?");
        $description = $data['description'] ?? '';
        $image       = $data['image'] ?? '';
        $unite       = $data['unite'] ?? 'pièce';
        $stock       = $data['stock'] ?? 0;

        $stmt->bind_param("ssissi i",
            $data['nom'], $description, $data['categorie_id'],
            $image, $unite, $stock, $id
        );
        $stmt->execute();
        $stmt->close();

        // Mettre à jour le prix si fourni
        if (!empty($data['prix'])) {
            $devise = $data['devise'] ?? 'USD';
            $stmt2  = $conn->prepare("INSERT INTO prix (produit_id, montant, devise) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE montant = ?, devise = ?");
            $stmt2->bind_param("idsds", $id, $data['prix'], $devise, $data['prix'], $devise);
            $stmt2->execute();
            $stmt2->close();
        }

        echo json_encode([
            'success' => true,
            'message' => 'Produit modifié avec succès'
        ]);

        $conn->close();
    }

    // ── SUPPRIMER UN PRODUIT (admin seulement) ───────────────────
    public function supprimer($id) {
        adminSeulement();

        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM produits WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Produit supprimé avec succès'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ]);
        }

        $stmt->close();
        $conn->close();
    }
}