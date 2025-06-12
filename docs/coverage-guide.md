# Guide de Couverture de Code - Loomi Server

## 📊 Vue d'ensemble

Ce guide présente l'analyse de couverture de code pour l'application Laravel Loomi Server.

**État actuel :** 56% de couverture (14/25 classes testées)

## 🚀 Utilisation

### Commande principale

```bash
npm run coverage:report
```

Cette commande génère un rapport détaillé de couverture qui inclut :
- **Analyse console** : Statistiques complètes affichées dans le terminal
- **Rapport HTML** : Fichier interactif dans `storage/logs/coverage-html/index.html`

### Tests simples

```bash
npm run test
```

## 📊 Métriques actuelles

- **Classes testées :** 14/25 (56%)
- **Fichiers de tests :** 14
- **Méthodes de test :** 112
- **Lignes de code :** 2,472
- **Tests Unit :** 4 fichiers
- **Tests Feature :** 9 fichiers

## ⚠️ Classes à tester

Les classes suivantes nécessitent des tests :
- Controllers (Auth, Box, Delivery, etc.)
- Commands (ProcessSubscriptionDeliveries)
- Middleware (CustomAuthMiddleware)
- Providers (AppServiceProvider)

## 🎯 Objectif

Passer de 56% à 80%+ de couverture en créant des tests pour les classes manquantes.

---

**✅ Pour voir le rapport détaillé : `npm run coverage:report`**
