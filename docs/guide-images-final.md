# Guide d'utilisation - Images des Boîtes

## ✅ Configuration terminée

### Ce qui a été implémenté :

1. **Table `boxes_images`** créée avec les champs requis
2. **6 boîtes spécifiques** avec seulement 2 catégories :
   - **Activité manuelle** : Box couture, Box mystère, Box peinture, Box tricot
   - **DIY** : Box création savon, Box création bougie

3. **12 vraies images** liées aux boîtes (2 par boîte)
4. **API mise à jour** pour retourner les images avec les boîtes
5. **Front-end modifié** pour afficher les vraies images

### Composants mis à jour :
- ✅ `BoxCard` - Affiche les images dans les listes
- ✅ `BoxCarousel` - Affiche les images dans le carrousel
- ✅ `BoxDetails` - Affiche les images sur la page détail
- ✅ `AdminBoxesList` - Affiche les images dans l'admin
- ✅ `BoxSelectionForGiftCard` - Affiche les images lors de sélection

## 🚀 Démarrage

### 1. Démarrer le serveur Laravel
```bash
cd c:\Users\benja\Documents\MDS\MDP\loomi-server
php artisan serve
```

### 2. Démarrer le front-end React
```bash
cd c:\Users\benja\Documents\MDS\MDP\loomi
npm run dev
```

### 3. Accéder à l'application
- Front-end : http://localhost:5173
- API : http://localhost:8000

## 📸 Images disponibles

Toutes les images sont stockées dans `/public/images/boxes/` :

### Box couture (Activité manuelle)
- box_couture_001.jpg (image principale)
- box_poterie_001.jpg (image secondaire)

### Box création savon (DIY)
- box_savon_001.png (image principale)
- box_lessive_001.jpg (image secondaire)

### Box mystère (Activité manuelle)
- box_mystere_001.png (image principale)
- box_mystere_orange.jpg (image secondaire)

### Box peinture (Activité manuelle)
- box_peinture_001.png (image principale)
- box_dessin_001.png (image secondaire)

### Box création bougie (DIY)
- box_bougie_001.png (image principale)
- box_parfum_001.png (image secondaire)

### Box tricot (Activité manuelle)
- box_tricot_001.png (image principale)
- box_plante_001.png (image secondaire)

## 🔧 Configuration technique

### Variables d'environnement
```
# Front-end (.env)
VITE_API_BASE_URL=http://localhost:8000

# Back-end (.env)
APP_URL=http://localhost:8000
```

### Structure API
```json
{
  "id": 1,
  "name": "Box couture",
  "base_price": "36.90",
  "category": {
    "short_name": "Activité manuelle"
  },
  "images": [
    {
      "link": "/images/boxes/box_couture_001.jpg",
      "alt": "Box couture avec matériel de couture professionnel"
    }
  ]
}
```

## 📱 Résultat

- ✅ Les vraies images s'affichent automatiquement sur toutes les pages
- ✅ Fallback en cas d'erreur de chargement
- ✅ Images optimisées avec texte alternatif
- ✅ URL configurables via les variables d'environnement
- ✅ Compatible mobile et desktop

## 🛠️ Fonctions utilitaires

### `getImageUrl(imagePath)`
Fonction qui construit les URLs complètes des images :
```javascript
import { getImageUrl } from '../api';

// Utilisation
<img src={getImageUrl(box.images[0].link)} alt={box.images[0].alt} />
```

## 📝 Notes importantes

1. **Serveur requis** : Le serveur Laravel doit être démarré pour servir les images
2. **Fallback automatique** : Si une image ne charge pas, une image placeholder s'affiche
3. **Performance** : Les images sont chargées à la demande
4. **Responsive** : Les images s'adaptent automatiquement aux différentes tailles d'écran

## ✨ Prêt à utiliser !

Votre application affiche maintenant les vraies images des boîtes partout où elles sont nécessaires. Les utilisateurs verront les vraies photos des produits au lieu des placeholders.
