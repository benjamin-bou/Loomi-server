# 🎁 Loomi Backend API

Loomi est une plateforme e-commerce dédiée à la découverte et à l’apprentissage de loisirs créatifs à travers des box thématiques. L’application repose sur un front-end React, un back-end PHP/Laravel et une base de données MySQL. Ce repository contient l'API backend développée avec Laravel 11.

## 📋 Table des matières

- [Architecture](#-architecture)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Utilisation](#-utilisation)
- [Tests](#-tests)
- [API Documentation](#-api-documentation)
- [Déploiement](#-déploiement)
- [Contribution](#-contribution)

## 🏗 Architecture

Le backend Loomi utilise une architecture Laravel moderne avec :

- **Laravel 11** - Framework PHP
- **MySQL** - Base de données principale
- **JWT** - Authentification
- **MySQL**
- **Swagger/OpenAPI** - Documentation API interactive
- **PHPUnit** - Tests automatisés
- **GitLab CI/CD** - Intégration continue

### Structure du projet

```
loomi-server/
├── app/
│   ├── Http/Controllers/     # Contrôleurs API
│   ├── Models/              # Modèles Eloquent
│   └── Providers/           # Providers Laravel
├── database/
│   ├── factories/           # Factories pour tests
│   ├── migrations/          # Migrations DB
│   └── seeders/            # Seeders
├── routes/
│   └── api.php             # Routes API
├── tests/
│   ├── Feature/            # Tests d'intégration
│   └── Unit/              # Tests unitaires
└── storage/               # Logs et cache
```

## 🔧 Prérequis

- **PHP** >= 8.2
- **Composer** >= 2.0
- **MySQL** >= 8.0
- **Node.js** >= 18 (pour le frontend)
- **Git**

## 🚀 Installation

### 1. Cloner le repository

```bash
git clone http://dev-loomi.data-flow.fr/loomi/loomi-server.git
cd loomi-server
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configuration de l'environnement

```bash
# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Générer la clé JWT
php artisan jwt:secret
```

### 4. Configuration de la base de données

Modifier le fichier `.env` avec vos paramètres de base de données :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loomi
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Migrations et seeders

```bash
# Nettoyer toute la base de donnée, puis exécuter les migrations, puis peupler de données fictives
php artisan migrate:fresh --seed
```

## ⚙️ Configuration

### Variables d'environnement principales

```env
APP_NAME=Loomi
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# JWT Configuration
JWT_SECRET=your_jwt_secret
JWT_ALGO=HS256

# CORS pour le frontend
FRONTEND_URL=http://localhost:5173
```

## 🎯 Utilisation

### Démarrer le serveur de développement

```bash
php artisan serve
```

L'API sera accessible sur `http://localhost:8000`

### Endpoints principaux

- **Authentication** : `/api/login`, `/api/register`
- **Boxes** : `/api/boxes`
- **Orders** : `/api/orders`
- **Subscriptions** : `/api/subscriptions`
- **Gift Cards** : `/api/gift-cards`
- **Health Check** : `/api/health`

### Avec le frontend

⚠️ **Important** : Ce backend est conçu pour fonctionner avec le frontend Loomi React. 

Pour une expérience complète :
1. Démarrez le backend sur le port 8000
2. Démarrez le frontend sur le port 5173
3. Les deux applications communiquent via l'API REST

## 🧪 Tests

Le projet inclut une suite complète de tests automatisés.

### Lancer tous les tests

```bash
# Tests complets
php artisan test
```

### Lancer tous les tests avec rapport et statistiques

```bash
# Tests complets avec rapport et statistiques
npm run coverage:report
```

### Types de tests

- **Tests unitaires** : Modèles, relations, logique métier
- **Tests de fonctionnalités** : Parcours utilisateur complets

## 📚 Documentation API

### 🚀 Documentation Interactive Swagger

**La documentation complète de l'API est disponible via une interface Swagger interactive :**

**🔗 [Accéder à la documentation API](http://localhost:8000/api/documentation)**

Cette interface moderne vous permet de :

- 📋 **Explorer** tous les endpoints organisés par catégories (Authentication, Boxes, Orders, etc.)
- 🧪 **Tester** les requêtes API directement depuis le navigateur
- 📊 **Visualiser** les schémas de données avec des exemples concrets

#### Utilisation de l'interface Swagger

1. **Ouvrez** [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)
2. **Explorez** les différentes sections (Authentication, Boxes, Orders, etc.)
3. **Authentifiez-vous** pour tester les endpoints protégés :

   #### 🔐 Comment obtenir et utiliser un token JWT

   **Étape 1 : Obtenir un token d'authentification**
   
   1. Dans l'interface Swagger, naviguez vers la section **"Authentication"**
   2. Cliquez sur **"POST /login"** pour l'ouvrir
   3. Cliquez sur **"Try it out"**
   4. Utilisez l'un de ces comptes de test dans le champ Request body :
   
   **Compte utilisateur :**
   ```json
   {
     "email": "user@example.com",
     "password": "password"
   }
   ```
   
   **Compte administrateur :**
   ```json
   {
     "email": "admin@example.com",
     "password": "password"
   }
   ```
   
   5. Cliquez sur **"Execute"**
   6. Dans la réponse (section "Response body"), **copiez la valeur du champ `access_token`**
   
   **Étape 2 : Utiliser le token pour l'authentification**
   
   1. En haut de la page Swagger, cliquez sur le bouton **"Authorize"** 🔒
   2. Dans la popup qui s'ouvre, collez votre token dans le champ **"Value"**
   3. Cliquez sur **"Authorize"** puis **"Close"**
   4. ✅ Vous êtes maintenant authentifié ! Vous pouvez tester tous les endpoints protégés

   > **💡 Note :** Le token JWT a une durée de vie limitée. Si vous recevez une erreur 401, répétez le processus pour obtenir un nouveau token.

4. **Testez** les endpoints directement depuis l'interface

#### Régénérer la documentation

```bash
php artisan l5-swagger:generate
```

### Aperçu des endpoints

### Authentification

Tous les endpoints protégés nécessitent un token JWT dans le header :

```http
Authorization: Bearer your_jwt_token
```

Quelques exemples d'endpoints (voir la documentation Swagger complète pour tous les détails) :

### Endpoints publics

```http
GET  /api/boxes              # Liste des boîtes
GET  /api/boxes/{id}         # Détails d'une boîte
POST /api/register           # Inscription
POST /api/login              # Connexion
GET  /api/health             # Status de l'API
```

### Endpoints authentifiés

```http
GET  /api/orders             # Commandes de l'utilisateur
POST /api/orders             # Créer une commande
GET  /api/subscription       # Abonnement actuel
POST /api/cancel-subscription # Annuler l'abonnement
GET  /api/profile/deliveries # Livraisons
POST /api/reviews            # Créer un avis
```

## 🚀 Déploiement

### CI/CD GitLab

Le projet inclut une configuration GitLab CI qui :

- **Teste** automatiquement toutes les branches
- **Déploie** automatiquement la branche `main` après validation des tests
- **Bloque** le déploiement si les tests échouent

## 🔗 Liens utiles

- [Frontend Loomi](https://dev-loomi.data-flow.fr/loomi/loomi-front) - Application React
- [Laravel Documentation](https://laravel.com/docs)
- [JWT Auth](https://jwt-auth.readthedocs.io/)

---

**⚠️ Note importante** : Ce backend doit être utilisé conjointement avec le frontend Loomi React pour une expérience utilisateur complète.

## Auteur

Boutrois Benjamin