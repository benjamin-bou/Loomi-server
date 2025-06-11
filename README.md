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

### Lancer des tests spécifiques

```bash
# Tests unitaires uniquement
php artisan test tests/Unit

# Tests d'intégration uniquement
php artisan test tests/Feature
```

### Types de tests

- **Tests unitaires** : Modèles, relations, logique métier
- **Tests d'intégration** : Endpoints API, authentification
- **Tests de fonctionnalités** : Parcours utilisateur complets

## 📚 API Documentation

### Authentification

Tous les endpoints protégés nécessitent un token JWT dans le header :

```http
Authorization: Bearer your_jwt_token
```

Quelques exemples d'endpoints :

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