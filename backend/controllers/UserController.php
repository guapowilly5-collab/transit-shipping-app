<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middlewares/auth.php';

class UserController {

    // ── LISTER TOUS LES UTILISATEURS (admin) ─────────────────────
    public function lister() {
        adminSeulement();

        $conn   = getConnection();
        $result = $conn->query(
            "SELECT id, nom, email, role, statut, created_at
             FROM users
             ORDER BY created_at DESC"
        );

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data'    => $users
        ]);

        $conn->close();
    }

    // ── LISTER LES CLIENTS EN ATTENTE (admin) ────────────────────
    public function enAttente() {
        adminSeulement();

        $conn   = getConnection();
        $result = $conn->query(
            "SELECT id, nom, email, role, statut, created_at
             FROM users
             WHERE statut = 'en_attente'
             ORDER BY created_at ASC"
        );

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data'    => $users
        ]);

        $conn->close();
    }

    // ── VALIDER UN COMPTE (admin) ─────────────────────────────────
    public function valider($id) {
        adminSeulement();

        $conn = getConnection();
        $stmt = $conn->prepare(
            "UPDATE users SET statut = 'valide' WHERE id = ?"
        );
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Compte validé avec succès'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la validation'
            ]);
        }

        $stmt->close();
        $conn->close();
    }

    // ── SUSPENDRE UN COMPTE (admin) ───────────────────────────────
    public function suspendre($id) {
        adminSeulement();

        $conn = getConnection();
        $stmt = $conn->prepare(
            "UPDATE users SET statut = 'suspendu' WHERE id = ?"
        );
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Compte suspendu avec succès'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la suspension'
            ]);
        }

        $stmt->close();
        $conn->close();
    }

    // ── SUPPRIMER UN COMPTE (admin) ───────────────────────────────
    public function supprimer($id) {
        adminSeulement();

        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Compte supprimé avec succès'
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

    // ── MON PROFIL (tout utilisateur connecté) ───────────────────
    public function monProfil() {
        $user = clientConnecte();

        $conn = getConnection();
        $stmt = $conn->prepare(
            "SELECT id, nom, email, role, statut, created_at
             FROM users WHERE id = ?"
        );
        $stmt->bind_param("i", $user['user_id']);
        $stmt->execute();
        $profil = $stmt->get_result()->fetch_assoc();

        echo json_encode([
            'success' => true,
            'data'    => $profil
        ]);

        $stmt->close();
        $conn->close();
    }
}