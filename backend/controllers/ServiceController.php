<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middlewares/auth.php';

class ServiceController {

    // ── LISTER TOUS LES SERVICES (public) ────────────────────────
    public function lister() {
        $conn   = getConnection();
        $result = $conn->query(
            "SELECT * FROM categories 
             WHERE nom IN (
                'Services Speciaux',
                'Safety Equipment',
                'Bunker Supply',
                'Anti-Piracy Equipment'
             )
             ORDER BY nom ASC"
        );

        $services = [];
        while ($row = $result->fetch_assoc()) {
            // Récupérer les produits de chaque service
            $stmt = $conn->prepare(
                "SELECT id, nom, description, image, unite
                 FROM produits
                 WHERE categorie_id = ? AND actif = 1"
            );
            $stmt->bind_param("i", $row['id']);
            $stmt->execute();
            $result2   = $stmt->get_result();
            $produits  = [];
            while ($p = $result2->fetch_assoc()) {
                $produits[] = $p;
            }
            $row['produits'] = $produits;
            $services[]      = $row;
            $stmt->close();
        }

        echo json_encode([
            'success' => true,
            'data'    => $services
        ]);

        $conn->close();
    }

    // ── REPORTING PAR CATÉGORIE (admin/agent) ────────────────────
    public function reportingCategorie() {
        adminOuAgent();

        $conn   = getConnection();
        $result = $conn->query(
            "SELECT c.nom AS categorie,
                    COUNT(ci.id) AS nb_commandes,
                    SUM(ci.sous_total) AS chiffre_affaires,
                    SUM(ci.quantite) AS quantite_totale
             FROM commande_items ci
             JOIN produits p ON ci.produit_id = p.id
             JOIN categories c ON p.categorie_id = c.id
             GROUP BY c.id, c.nom
             ORDER BY chiffre_affaires DESC"
        );

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data'    => $data
        ]);

        $conn->close();
    }

    // ── REPORTING PAR PÉRIODE (admin/agent) ──────────────────────
    public function reportingPeriode() {
        adminOuAgent();

        $debut = $_GET['debut'] ?? date('Y-m-01');
        $fin   = $_GET['fin']   ?? date('Y-m-t');

        $conn = getConnection();
        $stmt = $conn->prepare(
            "SELECT DATE_FORMAT(c.created_at, '%Y-%m-%d') AS jour,
                    COUNT(c.id) AS nb_commandes,
                    SUM(ci.sous_total) AS chiffre_affaires,
                    c.canal
             FROM commandes c
             JOIN commande_items ci ON c.id = ci.commande_id
             WHERE c.created_at BETWEEN ? AND ?
             GROUP BY jour, c.canal
             ORDER BY jour ASC"
        );
        $stmt->bind_param("ss", $debut, $fin);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode([
            'success' => true,
            'periode' => ['debut' => $debut, 'fin' => $fin],
            'data'    => $data
        ]);

        $stmt->close();
        $conn->close();
    }

    // ── EXPORT CSV COMMANDES (admin/agent) ───────────────────────
    public function exportCSV() {
        adminOuAgent();

        $conn   = getConnection();
        $result = $conn->query(
            "SELECT c.reference, c.statut, c.canal, c.created_at,
                    n.nom AS navire, n.numero_imo,
                    p.nom AS produit, ci.quantite,
                    ci.prix_unitaire, ci.sous_total
             FROM commandes c
             JOIN navires n ON c.navire_id = n.id
             JOIN commande_items ci ON c.id = ci.commande_id
             JOIN produits p ON ci.produit_id = p.id
             ORDER BY c.created_at DESC"
        );

        // Générer le CSV
        $csv  = "Reference,Statut,Canal,Date,Navire,IMO,Produit,Quantite,Prix Unitaire,Sous Total\n";
        while ($row = $result->fetch_assoc()) {
            $csv .= implode(',', [
                $row['reference'],
                $row['statut'],
                $row['canal'],
                $row['created_at'],
                $row['navire'],
                $row['numero_imo'],
                $row['produit'],
                $row['quantite'],
                $row['prix_unitaire'],
                $row['sous_total']
            ]) . "\n";
        }

        // Sauvegarder le fichier
        $filename = 'commandes_' . date('Y-m-d') . '.csv';
        $filepath = __DIR__ . '/../../docs/' . $filename;
        file_put_contents($filepath, $csv);

        echo json_encode([
            'success'  => true,
            'message'  => 'Export CSV généré avec succès',
            'fichier'  => $filename,
            'contenu'  => $csv
        ]);

        $conn->close();
    }
}