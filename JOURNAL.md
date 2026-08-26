# Journal de développement — Aide Phone

> Plateforme professionnelle de gestion et d'assistance à la réparation électronique  
> Stack : Vue 3 + TypeScript + Vite + Laravel 12 + Supabase PostgreSQL  
> Déploiement : Frontend → Vercel | Backend → Wasmer | DB → Supabase

---

## Liens importants

| Ressource | URL |
|-----------|-----|
| Frontend (prod) | https://aide-phone-repair-three.vercel.app |
| Backend (prod) | https://aide-phone-repair.wasmer.app |
| Repository GitHub | https://github.com/fpythonz-arch/aide-phone-repair |
| Base de données | Supabase — projet `rztrcsydxuybdavgoock` |

---

## Structure du projet

```
aide-phone-repair/
├── frontend/          ← Vue 3 + TypeScript + Vite + Tailwind v4
│   └── src/
│       ├── views/     ← Pages de l'application
│       ├── components/← Composants réutilisables
│       ├── composables/← Logique métier (useRepairs, useDiagnostic...)
│       ├── stores/    ← État global Pinia
│       ├── router/    ← Définition des routes
│       ├── types/     ← Types TypeScript centralisés
│       └── style.css  ← Design System
└── app/               ← Laravel 12 — API REST (à la racine)
    ├── Http/Controllers/API/
    ├── Models/
    ├── database/migrations/
    └── routes/api.php
```

---

## Historique des sessions

---

### Session 1 — Mise en place du projet
**Date :** Août 2026  
**Statut :** ✅ Terminé

#### Ce qui a été fait
- Création du projet (backend Laravel + frontend Vue 3)
- Mise en place de l'environnement local (PHP, Node, MySQL)
- Structure initiale des modules : Dashboard, Diagnostic, Dépannage, Composants, Outils, Codes, Ressources, Évolution

---

### Session 2 — Déploiement initial
**Date :** 23 Août 2026  
**Statut :** ✅ Terminé

#### Ce qui a été fait
- Initialisation du repository Git
- Push sur GitHub (`fpythonz-arch/aide-phone-repair`)
- Configuration Supabase PostgreSQL (remplacement MySQL)
- Déploiement backend sur Wasmer
- Déploiement frontend sur Vercel
- Création des tables SQL dans Supabase (migrations manuelles)
- Insertion des données de base : 20 symptômes, 20 composants, 15 codes secrets, 19 appareils

#### Problèmes résolus
- Erreur `composer: command not found` sur Wasmer → choix du préréglage PHP
- Erreur `No postgres database config` → désactivation de la DB intégrée Wasmer
- Erreur 422 sur `/evolution` → FormRequest trop strict
- Routes `/api/tools` et `/api/resources` manquantes → ajoutées
- Erreur 500 `relation "components" does not exist` → tables créées via SQL Editor

---

### Session 3 — Refonte UI/UX + Module Réparations
**Date :** 26 Août 2026  
**Statut :** ✅ Terminé — déployé en production

#### Objectif
Transformer l'application prototype en produit professionnel utilisable dans un atelier.

#### Ce qui a été fait

**Design System (`frontend/src/style.css`)**
- Tokens CSS : couleurs, typographie, animations
- Composants CSS : `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-danger`, `.btn-ghost`
- `.card`, `.card-hover`, `.badge` (7 variantes couleur)
- `.input`, `.select`, `.label`
- `.stat-card`, `.empty-state`, `.table-container`
- `.sidebar-link`, `.sidebar-group-label`
- Animations : `fadeIn`, `scaleIn`, `shimmer` (skeleton loader)

**Navigation (`frontend/src/App.vue`)**
- Sidebar fixe desktop (220px) avec 3 groupes : Atelier / Connaissances / Outils
- Top bar sticky mobile avec bouton menu
- Bottom navigation mobile (Accueil, Diagnostic, Réparations, Dépannage, Plus)
- Drawer mobile animé avec toute la navigation
- Toasts globaux (succès, erreur, warning, info)
- Badge compteur sur "Réparations" (nb actives)
- Dark mode persistant (`ap_theme` localStorage)

**Dashboard (`frontend/src/views/AtelierDashboard.vue`)**
- Stats calculées depuis les vraies données (localStorage)
- 4 KPI : En cours, En attente, Terminées, Urgentes
- Liste des réparations actives (5 dernières, triées par priorité)
- État vide expressif avec CTA "Créer une réparation"
- Actions rapides (4 boutons)
- Guides populaires (4 liens dépannage)
- Activité récente calculée depuis les réparations

**Module Réparations** (nouveau)
- `frontend/src/composables/useRepairs.ts` — CRUD complet localStorage
  - Numérotation automatique `REP-2026-001`
  - `createRepair`, `updateRepair`, `updateStatus`, `deleteRepair`, `filterRepairs`
- `frontend/src/views/ReparationsView.vue` — Liste avec filtres
  - Recherche par client/appareil/numéro
  - Filtres statut et priorité
  - Tableau desktop avec actions
  - Cartes mobile
  - Modal confirmation suppression
- `frontend/src/views/ReparationFormView.vue` — Formulaire création/édition
  - Informations client (nom, téléphone, email)
  - Appareil (marque parmi 15 options, modèle, IMEI)
  - Problème + diagnostic + technicien + priorité + statut
  - Coût estimé / final avec devise (FCFA, EUR, USD, GHS, NGN)
  - Garantie en jours
- `frontend/src/views/ReparationDetailView.vue` — Fiche détail
  - Changement de statut rapide (7 boutons)
  - Informations client + réparation
  - Problème + diagnostic
  - Coût formaté
  - Notes internes

**Types TypeScript (`frontend/src/types/index.ts`)**
- Centralisation de tous les types (20+ types définis)
- Nouveaux types : `Repair`, `RepairStatus`, `RepairPriority`, `Client`
- Types manquants ajoutés : `SecretCode`, `ProTool`, `Resource`, `PanneType`, `PhoneEra`, `Toast`, `DeviceInfo`

**Router (`frontend/src/router/index.ts`)**
- Nouvelles routes : `/reparations`, `/reparations/nouvelle`, `/reparations/:id`, `/reparations/:id/edit`
- Page 404 (`NotFoundView.vue`)
- Scroll behavior smooth

**Corrections bugs**
- Debug block `bg-yellow-200` supprimé de `ComposantsView.vue`
- Watch console.log supprimé de `ComposantsView.vue`
- Bug 422 Évolution : `StoreEvolutionEventRequest` simplifié + `EvolutionController.store()` adapté

#### Fichiers créés
- `frontend/src/style.css` (refonte complète)
- `frontend/src/App.vue` (refonte complète)
- `frontend/src/types/index.ts` (refonte complète)
- `frontend/src/router/index.ts` (refonte complète)
- `frontend/src/composables/useRepairs.ts` (nouveau)
- `frontend/src/views/AtelierDashboard.vue` (refonte complète)
- `frontend/src/views/ReparationsView.vue` (nouveau)
- `frontend/src/views/ReparationFormView.vue` (nouveau)
- `frontend/src/views/ReparationDetailView.vue` (nouveau)
- `frontend/src/views/NotFoundView.vue` (nouveau)

#### Fichiers modifiés
- `frontend/src/views/ComposantsView.vue` (suppression debug block + watch log)
- `app/Http/Requests/StoreEvolutionEventRequest.php` (simplification règles)
- `app/Http/Controllers/API/EvolutionController.php` (correction store())

---

## En cours / À faire

### 🔴 Critique
- [ ] Page de connexion (Login) avec authentification
- [ ] Protection des routes (guard router)
- [ ] Déployer les corrections backend sur Wasmer

### 🟠 Important
- [ ] Page profil utilisateur / déconnexion dans la sidebar
- [ ] Module Clients (liste, fiche, historique)
- [ ] Passeport numérique de l'appareil
- [ ] Données de démo réalistes pré-chargées
- [ ] Responsive test sur mobile réel

### 🟡 Amélioration
- [ ] Dark mode cohérent sur tous les composants existants
- [ ] Unifier les deux sources de données dépannage
- [ ] Supprimer composants dupliqués (ComponentDetail x2, SymptomGrid x2)
- [ ] Ajouter Vitest pour les tests unitaires

### 🟢 Optionnel / Futur
- [ ] Phone Health Score
- [ ] Module Devis (PDF)
- [ ] Module Stock / Composants en inventaire
- [ ] PWA (offline, install prompt)
- [ ] Authentification backend réelle (Laravel Sanctum)
- [ ] Multi-atelier / multi-utilisateur

---

## Techniciens de démo

| Nom | Email | Mot de passe | Rôle |
|-----|-------|--------------|------|
| Abdoul Diallo | abdoul@atelier.com | demo1234 | Technicien senior |
| Ibrahim Koné | ibrahim@atelier.com | demo1234 | Technicien |
| Moussa Traoré | moussa@atelier.com | demo1234 | Admin |
| Admin | admin@aidephone.com | admin123 | Admin |

---

## Commandes utiles

```bash
# Lancer en local
cd frontend && npm run dev        # http://localhost:5173
cd backend && php artisan serve   # http://localhost:8000 (si backend encore dans /backend)

# Déployer
git add -A
git commit -m "feat: description"
git push
# → Vercel et Wasmer redéploient automatiquement

# Base de données Supabase
# SQL Editor : https://app.supabase.com/project/rztrcsydxuybdavgoock/editor
```

---

## Notes techniques

- **Tailwind v4** : `@apply` avec des classes custom n'est pas supporté → utiliser les propriétés CSS directement
- **Wasmer** : utilise `phpix` (PHP 32-bit), pas de SSH, redéploiement via push GitHub
- **Réparations** : stockées en `localStorage` (`ap_repairs`) — MVP, à migrer vers API
- **Auth** : simulée en localStorage (`ap_session`) — à remplacer par Laravel Sanctum
- **FCFA** : devise par défaut pour le contexte Afrique francophone
