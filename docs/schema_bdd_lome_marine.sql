CREATE TABLE `users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL COMMENT 'admin / agent / client',
  `statut` varchar(20) DEFAULT 'en_attente' COMMENT 'en_attente / valide / suspendu',
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `navires` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nom` varchar(150) NOT NULL,
  `numero_imo` varchar(20) UNIQUE,
  `type` varchar(100) COMMENT 'bulk carrier / tanker / container / offshore...',
  `compagnie` varchar(150),
  `user_id` int NOT NULL,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `categories` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text,
  `image` varchar(255)
);

CREATE TABLE `produits` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nom` varchar(200) NOT NULL,
  `description` text,
  `categorie_id` int NOT NULL,
  `image` varchar(255),
  `unite` varchar(50) COMMENT 'kg / litre / pièce / carton...',
  `stock` int DEFAULT 0,
  `actif` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `prix` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `produit_id` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `devise` varchar(10) DEFAULT 'USD',
  `updated_at` timestamp DEFAULT (now())
);

CREATE TABLE `commandes` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `reference` varchar(50) UNIQUE COMMENT 'LMS-2025-0001',
  `navire_id` int NOT NULL,
  `agent_id` int,
  `statut` varchar(30) DEFAULT 'en_attente' COMMENT 'en_attente / confirmee / en_preparation / livree / annulee',
  `canal` varchar(20) NOT NULL COMMENT 'online / email / whatsapp / telephone',
  `notes` text,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `commande_items` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `commande_id` int NOT NULL,
  `produit_id` int NOT NULL,
  `quantite` int NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `sous_total` decimal(10,2) NOT NULL
);

CREATE TABLE `devis` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `commande_id` int NOT NULL,
  `fichier_pdf` varchar(255),
  `envoye_par_email` boolean DEFAULT false,
  `created_at` timestamp DEFAULT (now())
);

ALTER TABLE `navires` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `produits` ADD FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`);

ALTER TABLE `prix` ADD FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`);

ALTER TABLE `commandes` ADD FOREIGN KEY (`navire_id`) REFERENCES `navires` (`id`);

ALTER TABLE `commandes` ADD FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`);

ALTER TABLE `commande_items` ADD FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`);

ALTER TABLE `commande_items` ADD FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`);

ALTER TABLE `devis` ADD FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`);
