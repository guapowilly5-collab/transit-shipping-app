<?php

require_once __DIR__ . '/../config/database.php';

class AuthController {

    // ── INSCRIPTION ──────────────────────────────────────────────
    public function inscription() {
        $data = json_decode(file_get_contents('php://input'), true);

        // Vérification des champs obligatoires
        if (empty($data['nom']) || empty($data['email']) || empty($data['password'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Nom, email et mot de passe sont obligatoires'
            ]);
            return;
        }

        $conn = getConnection();

        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Cet email est déjà utilisé'
            ]);
            $stmt->close();
            $conn->close();
            return;
        }
        $stmt->close();

        // Hashage du mot de passe
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $role = $data['role'] ?? 'client';
        $statut = 'en_attente';

        // Insertion
        $stmt = $conn->prepare("INSERT INTO users (nom, email, password, role, statut) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $data['nom'], $data['email'], $password_hash, $role, $statut);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Inscription réussie. En attente de validation par un administrateur.',
                'user_id' => $conn->insert_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l inscription'
            ]);
        }

        $stmt->close();
        $conn->close();
    }

    // ── CONNEXION ─────────────────────────────────────────────────
    public function connexion() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['email']) || empty($data['password'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Email et mot de passe obligatoires'
            ]);
            return;
        }

        $conn = getConnection();

        $stmt = $conn->prepare("SELECT id, nom, email, password, role, statut FROM users WHERE email = ?");
        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ]);
            $stmt->close();
            $conn->close();
            return;
        }

        // Vérifier le mot de passe
        if (!password_verify($data['password'], $user['password'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ]);
            $stmt->close();
            $conn->close();
            return;
        }

        // Vérifier le statut du compte
        if ($user['statut'] === 'en_attente') {
            echo json_encode([
                'success' => false,
                'message' => 'Votre compte est en attente de validation par un administrateur'
            ]);
            $stmt->close();
            $conn->close();
            return;
        }

        if ($user['statut'] === 'suspendu') {
            echo json_encode([
                'success' => false,
                'message' => 'Votre compte a été suspendu. Contactez l administrateur'
            ]);
            $stmt->close();
            $conn->close();
            return;
        }

        // Créer la session
        session_start();
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_role']= $user['role'];

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

        $stmt->close();
        $conn->close();
    }

    // ── DÉCONNEXION ───────────────────────────────────────────────
    public function deconnexion() {
        session_start();
        session_destroy();
        echo json_encode([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }

    // ── VALIDATION COMPTE PAR ADMIN ───────────────────────────────
    public function validerCompte() {
        // Vérifier que c'est bien un admin
        session_start();
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode([
                'success' => false,
                'message' => 'Accès refusé. Réservé aux administrateurs'
            ]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['user_id']) || empty($data['statut'])) {
            echo json_encode([
                'success' => false,
                'message' => 'user_id et statut sont obligatoires'
            ]);
            return;
        }

        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE users SET statut = ? WHERE id = ?");
        $stmt->bind_param("si", $data['statut'], $data['user_id']);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Statut du compte mis à jour : ' . $data['statut']
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
