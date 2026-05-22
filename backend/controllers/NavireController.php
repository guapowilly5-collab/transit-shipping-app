<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middlewares/auth.php';

class NavireController {

    // ── LISTER LES NAVIRES (admin/agent) ─────────────────────────
    public function lister() {
        adminOuAgent();

        $conn = getConnection();
        $sql  = "SELECT n.*, u.nom AS proprietaire, u.email 
                 FROM navires n
                 JOIN users u ON n.user_id = u.id
                 ORDER BY n.nom ASC";

        $result  = $conn->query($sql);
        $navires = [];
        while ($row = $result->fetch_assoc()) {
            $navires[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data'    => $navires
        ]);

        $conn->close();
    }

    // ── MON NAVIRE (client connecté) ──────────────────────────────
    public function monNavire() {
        $user = clientConnecte();

        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM navires WHERE user_id = ?");
        $stmt->bind_param("i", $user['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $navires = [];
        while ($row = $result->fetch_assoc()) {
            $navires[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data'    => $navires
        ]);

        $stmt->close();
        $conn->close();
    }

    // ── CRÉER UN NAVIRE (client connecté) ────────────────────────
    public function creer() {
        $user = clientConnecte();

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['nom'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Le nom du navire est obligatoire'
            ]);
            return;
        }

        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO navires (nom, numero_imo, type, compagnie, user_id) VALUES (?, ?, ?, ?, ?)");
        $numero_imo = $data['numero_imo'] ?? '';
        $type       = $data['type']       ?? '';
        $compagnie  = $data['compagnie']  ?? '';

        $stmt->bind_param("ssssi",
            $data['nom'], $numero_imo, $type,
            $compagnie, $user['user_id']
        );

        if ($stmt->execute()) {
            echo json_encode([
                'success'    => true,
                'message'    => 'Navire enregistré avec succès',
                'navire_id'  => $conn->insert_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l enregistrement'
            ]);
        }

        $stmt->close();
        $conn->close();
    }

    // ── MODIFIER UN NAVIRE ────────────────────────────────────────
    public function modifier($id) {
        $user = clientConnecte();

        $data = json_decode(file_get_contents('php://input'), true);
        $conn = getConnection();

        $stmt = $conn->prepare("UPDATE navires SET nom = ?, numero_imo = ?, type = ?, compagnie = ? WHERE id = ? AND user_id = ?");
        $numero_imo = $data['numero_imo'] ?? '';
        $type       = $data['type']       ?? '';
        $compagnie  = $data['compagnie']  ?? '';

        $stmt->bind_param("ssssii",
            $data['nom'], $numero_imo, $type,
            $compagnie, $id, $user['user_id']
        );

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Navire modifié avec succès'
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

    // ── SUPPRIMER UN NAVIRE (admin seulement) ────────────────────
    public function supprimer($id) {
        adminSeulement();

        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM navires WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Navire supprimé avec succès'
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