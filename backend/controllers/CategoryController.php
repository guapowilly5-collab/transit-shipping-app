<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middlewares/auth.php';

class CategoryController {

    // ── LISTE TOUTES LES CATÉGORIES (public) ─────────────────────
    public function lister() {
        $conn = getConnection();
        $result = $conn->query("SELECT * FROM categories ORDER BY nom ASC");

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data'    => $categories
        ]);

        $conn->close();
    }

    // ── CRÉER UNE CATÉGORIE (admin seulement) ────────────────────
    public function creer() {
        adminSeulement();

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['nom'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Le nom de la catégorie est obligatoire'
            ]);
            return;
        }

        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO categories (nom, description, image) VALUES (?, ?, ?)");
        $description = $data['description'] ?? '';
        $image       = $data['image'] ?? '';
        $stmt->bind_param("sss", $data['nom'], $description, $image);

        if ($stmt->execute()) {
            echo json_encode([
                'success'      => true,
                'message'      => 'Catégorie créée avec succès',
                'categorie_id' => $conn->insert_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la création'
            ]);
        }

        $stmt->close();
        $conn->close();
    }

    // ── MODIFIER UNE CATÉGORIE (admin seulement) ─────────────────
    public function modifier($id) {
        adminSeulement();

        $data = json_decode(file_get_contents('php://input'), true);
        $conn = getConnection();

        $stmt = $conn->prepare("UPDATE categories SET nom = ?, description = ?, image = ? WHERE id = ?");
        $description = $data['description'] ?? '';
        $image       = $data['image'] ?? '';
        $stmt->bind_param("sssi", $data['nom'], $description, $image, $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Catégorie modifiée avec succès'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la modification'
            ]);
        }

        $stmt->close();
        $conn->close();
    }

    // ── SUPPRIMER UNE CATÉGORIE (admin seulement) ────────────────
    public function supprimer($id) {
        adminSeulement();

        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Catégorie supprimée avec succès'
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