<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middlewares/auth.php';

class DashboardController {

    // ── STATS GÉNÉRALES (admin/agent) ─────────────────────────────
    public function stats() {
        adminOuAgent();

        $conn = getConnection();

        // Total commandes
        $total_commandes = $conn->query("SELECT COUNT(*) as total FROM commandes")->fetch_assoc()['total'];

        // Commandes par statut
        $result = $conn->query("SELECT statut, COUNT(*) as total FROM commandes GROUP BY statut");
        $commandes_statut = [];
        while ($row = $result->fetch_assoc()) {
            $commandes_statut[$row['statut']] = $row['total'];
        }

        // Chiffre d'affaires total
        $ca = $conn->query("SELECT SUM(sous_total) as total FROM commande_items")->fetch_assoc()['total'];

        // Total produits actifs
        $total_produits = $conn->query("SELECT COUNT(*) as total FROM produits WHERE actif = 1")->fetch_assoc()['total'];

        // Total clients validés
        $total_clients = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'client' AND statut = 'valide'")->fetch_assoc()['total'];

        // Clients en attente de validation
        $clients_attente = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'client' AND statut = 'en_attente'")->fetch_assoc()['total'];

        // Total navires
        $total_navires = $conn->query("SELECT COUNT(*) as total FROM navires")->fetch_assoc()['total'];

        // Commandes par canal
        $result2 = $conn->query("SELECT canal, COUNT(*) as total FROM commandes GROUP BY canal");
        $commandes_canal = [];
        while ($row = $result2->fetch_assoc()) {
            $commandes_canal[$row['canal']] = $row['total'];
        }

        // 5 dernières commandes
        $result3 = $conn->query(
            "SELECT c.reference, c.statut, c.canal, c.created_at, n.nom AS navire
             FROM commandes c
             JOIN navires n ON c.navire_id = n.id
             ORDER BY c.created_at DESC LIMIT 5"
        );
        $dernieres_commandes = [];
        while ($row = $result3->fetch_assoc()) {
            $dernieres_commandes[] = $row;
        }

        // Commandes par mois (6 derniers mois)
        $result4 = $conn->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as mois, COUNT(*) as total
             FROM commandes
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY mois
             ORDER BY mois ASC"
        );
        $commandes_mois = [];
        while ($row = $result4->fetch_assoc()) {
            $commandes_mois[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data'    => [
                'total_commandes'     => $total_commandes,
                'commandes_statut'    => $commandes_statut,
                'chiffre_affaires'    => $ca ?? 0,
                'total_produits'      => $total_produits,
                'total_clients'       => $total_clients,
                'clients_attente'     => $clients_attente,
                'total_navires'       => $total_navires,
                'commandes_canal'     => $commandes_canal,
                'dernieres_commandes' => $dernieres_commandes,
                'commandes_mois'      => $commandes_mois,
            ]
        ]);

        $conn->close();
    }
}