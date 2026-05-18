# API Documentation — Lome Marine Supplier Global

> Document évolutif — mis à jour au fil du développement  
> Version 1.0 | Équipe : D1 (Willy), D2, D3

---

##  Auth

| Méthode | Route | Description | Accès |
|---|---|---|---|
| POST | `/api/auth/inscription` | Créer un compte | Public |
| POST | `/api/auth/connexion` | Se connecter | Public |
| POST | `/api/auth/deconnexion` | Se déconnecter | Connecté |

---

##  Users

| Méthode | Route | Description | Accès |
|---|---|---|---|
| GET | `/api/users` | Liste tous les utilisateurs | Admin |
| GET | `/api/users/{id}` | Détail d'un utilisateur | Admin |
| PUT | `/api/users/{id}/statut` | Valider / suspendre un compte | Admin |
| DELETE | `/api/users/{id}` | Supprimer un utilisateur | Admin |

---

##  Navires

| Méthode | Route | Description | Accès |
|---|---|---|---|
| POST | `/api/navires` | Inscrire un navire | Client |
| GET | `/api/navires` | Liste tous les navires | Admin/Agent |
| GET | `/api/navires/{id}` | Détail d'un navire | Admin/Agent/Client |
| PUT | `/api/navires/{id}` | Modifier un navire | Admin/Client |
| DELETE | `/api/navires/{id}` | Supprimer un navire | Admin |

---

##  Catégories

| Méthode | Route | Description | Accès |
|---|---|---|---|
| GET | `/api/categories` | Liste toutes les catégories | Public |
| POST | `/api/categories` | Créer une catégorie | Admin |
| PUT | `/api/categories/{id}` | Modifier une catégorie | Admin |
| DELETE | `/api/categories/{id}` | Supprimer une catégorie | Admin |

---

##  Produits

| Méthode | Route | Description | Accès |
|---|---|---|---|
| GET | `/api/produits` | Liste produits SANS prix | Public |
| GET | `/api/produits/catalogue` | Liste produits AVEC prix | Client connecté |
| GET | `/api/produits/{id}` | Détail d'un produit | Public |
| POST | `/api/produits` | Créer un produit | Admin |
| PUT | `/api/produits/{id}` | Modifier un produit | Admin |
| DELETE | `/api/produits/{id}` | Supprimer un produit | Admin |

---

##  Prix

| Méthode | Route | Description | Accès |
|---|---|---|---|
| POST | `/api/prix` | Définir un prix | Admin |
| PUT | `/api/prix/{produit_id}` | Modifier un prix | Admin |

---

##  Commandes

| Méthode | Route | Description | Accès |
|---|---|---|---|
| POST | `/api/commandes` | Créer une commande | Client/Agent |
| GET | `/api/commandes` | Liste toutes les commandes | Admin/Agent |
| GET | `/api/commandes/{id}` | Détail d'une commande | Admin/Agent/Client |
| GET | `/api/commandes/mes-commandes` | Commandes du client connecté | Client |
| PUT | `/api/commandes/{id}/statut` | Changer le statut | Admin/Agent |
| DELETE | `/api/commandes/{id}` | Annuler une commande | Admin |

---

##  Devis

| Méthode | Route | Description | Accès |
|---|---|---|---|
| POST | `/api/devis/{commande_id}` | Générer un devis PDF | Admin/Agent |
| GET | `/api/devis/{id}` | Télécharger un devis | Admin/Agent/Client |
| POST | `/api/devis/{id}/envoyer` | Envoyer le devis par email | Admin/Agent |


---

##  Catégories de produits

| # | Catégorie | Exemples de produits |
|---|---|---|
| 1 | Provisions | Viandes, fruits, produits laitiers, jus, céréales |
| 2 | Bonded Stores | Cigarettes, alcools, vins, bières, chocolats, parfums |
| 3 | Deck / Engine / Electrical Spares | Câbles, vannes, outils, instruments de mesure |
| 4 | Cabin / Salon Stores | Literie, vaisselle, ustensiles, électroménager |
| 5 | Safety Equipment | Gilets de sauvetage, extincteurs, combinaisons SOLAS |
| 6 | Anti-Piracy Equipment | Razor wires, Water cannon |
| 7 | Bunker Supply | Fuel oils, luboils |
| 8 | Services Spéciaux | Fumigation, Location matériel, Workshop, Recharge CO2 |
