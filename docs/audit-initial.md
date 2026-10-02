# Audit initial — Aide Phone
> Généré après exploration complète du dépôt. Aucun fichier modifié.  
> Date : août 2026

---

## 1. Ce qui existe et fonctionne vraiment

### Backend (Laravel + Supabase PostgreSQL)

| Fonctionnalité | Endpoint | Réel ? |
|----------------|----------|--------|
| Liste des marques | `GET /api/devices/brands` | ✅ Requête DB réelle |
| Appareils par marque | `GET /api/devices/by-brand/:brand` | ✅ Requête DB réelle |
| Liste des symptômes | `GET /api/symptoms` | ✅ Requête DB réelle |
| Analyse des symptômes | `POST /api/diagnostic/analyze` | ✅ Jointures DB réelles (symptômes → composants → guides) |
| Catalogue composants | `GET /api/components` | ✅ Requête DB réelle avec filtres et pagination |
| Codes secrets | `GET /api/codes`, `/by-brand`, `/by-category` | ✅ Requête DB réelle |
| Évolution / timeline | `GET /api/evolution` + CRUD complet | ✅ Requête DB réelle avec agrégats |
| Authentification | `POST /api/auth/login` / `logout` / `me` | ✅ Laravel Sanctum |
| Réparations | `GET/POST/PUT/PATCH/DELETE /api/repairs` | ✅ CRUD complet, protégé par Sanctum |
| Calcul de sévérité | Dans `DiagnosticController::analyze()` | ✅ Basé sur le champ `severity` des symptômes |
| Recommandations | Dans `DiagnosticController::analyze()` | ✅ Règles simples basées sur les catégories |

### Frontend (Vue 3 + TypeScript)

| Fonctionnalité | Réel ? |
|----------------|--------|
| Wizard diagnostic 5 étapes | ✅ Appels API réels |
| Sélection marque → modèle → symptômes | ✅ Données API |
| Affichage du résultat d'analyse | ✅ Données API (composants, guides, sévérité) |
| Sauvegarde diagnostic dans localStorage | ✅ (`diagnostic_history`) |
| Module Réparations | ✅ CRUD complet via API Sanctum |
| Authentification | ✅ Via API, token Sanctum, session stockée |
| Migration localStorage → API | ✅ `migrateLocalRepairsIfNeeded()` |
| Dépannage — liste de catégories | ✅ API réelle (données hardcodées côté backend) |
| Composants — catalogue | ✅ API réelle |
| Codes secrets | ✅ API réelle |

---

## 2. Ce qui est simulé ou incomplet

### Backend

| Problème | Fichier | Impact |
|----------|---------|--------|
| **Confiance toujours à 0.85** | `DiagnosticController::analyze()` ligne `'confidence' => 0.85` | Affichage trompeur — aucune logique derrière ce chiffre |
| **Coût calculé arbitrairement** | `min = composants × 25`, `max = composants × 85` (EUR) | Chiffre inventé, non lié aux prix réels |
| **Session diagnostic non persistée** | `initialize()` génère un UUID mais ne le stocke pas en DB | L'historique est impossible à reconstituer |
| **`validateResults()` ne sauvegarde rien** | `DiagnosticController` — retourne `success: true` sans écriture DB | La validation n'a aucun effet |
| **`history()` retourne `[]`** | `DiagnosticController::history()` | Fonctionnalité annoncée, non implémentée |
| **`nextSteps()` est statique** | 3 phrases hardcodées, pas calculées | Inutile en l'état |
| **`getByDevice()` ignore l'ID** | `SymptomController::getByDevice()` — retourne tous les symptômes | Le filtre par appareil ne fonctionne pas |
| **Dépannage : 29/31 catégories vides** | `DepannageController::show()` — seuls `ecran` et `batterie` sont implémentés | Les autres catégories retournent un guide vide |
| **Outils : pas de table DB** | `ToolController` — entièrement hardcodé en PHP | Impossible d'administrer les outils sans modifier le code |
| **`analysisResults` toujours vide** | `useDiagnosticStore` ligne `analysisResults.value = []` après `analyze()` | La section "Ressources" dans ResultStep est toujours cachée |
| **`totalCost` toujours à 0** | `ValidationStep.vue` | Le coût affiché en validation est toujours 0 |
| **Barre de progression cosmétique** | `DiagnosticView.vue` — `setInterval` de +10% / 300ms | Ne reflète pas la vraie progression de l'API |
| **Schéma incohérent** | Migration crée `severity_level` (entier), modèle et contrôleur utilisent `severity` (chaîne) | Risque de requêtes silencieuses qui ne trouvent rien |
| **Routes dupliquées** | `routes/api.php` — `/components/components` et `/components/components/{id}` | Routes mortes, méthode `showBySlug` inexistante |

---

## 3. Problèmes de sécurité

### 🔴 CRITIQUE — Mots de passe en clair dans le frontend

**Fichier :** `frontend/src/views/LoginView.vue`

Trois comptes avec des mots de passe en texte clair sont intégrés dans le code JavaScript livré au navigateur. N'importe qui peut les lire dans le bundle ou les DevTools.

```
abdoul@atelier.com   / demo1234  — Technicien senior
ibrahim@atelier.com  / demo1234  — Technicien
moussa@atelier.com   / demo1234  — Admin          ← accès admin exposé
```

**Risque :** Si ces comptes existent dans Supabase avec ce mot de passe, n'importe qui peut se connecter comme administrateur.  
**À faire en priorité absolue** avant tout autre travail.

---

### 🔴 CRITIQUE — `.env` avec des credentials de production

**Fichier :** `.env` (à la racine)

Le fichier `.env` contient :
- `DB_HOST` : hôte Supabase de production (pas `localhost`)
- `DB_PASSWORD` : mot de passe réel de la base de données
- `APP_KEY` : clé applicative Laravel

**Bonne nouvelle :** `.env` est bien dans `.gitignore` — il n'est pas commité sur GitHub.  
**Risque résiduel :** `APP_DEBUG=true` pointe vers une base de production. Une erreur Laravel peut exposer des traces de pile contenant l'hôte, l'utilisateur et la structure DB dans la réponse HTTP.

---

### 🟠 IMPORTANT — API publique sans authentification

**Fichier :** `routes/api.php`

Les routes suivantes sont accessibles **sans token** par n'importe qui :

- `POST /api/diagnostic/analyze` — interroge la base de données
- `GET /api/symptoms`, `GET /api/components`, `GET /api/codes`
- `GET /api/devices/*`, `POST /api/evolution`

Seuls `/api/repairs/*` et `/api/auth/*` exigent un token Sanctum.

---

### 🟠 IMPORTANT — Clés MCP non configurées

**Fichier :** `.env`

```
MCP_API_KEYS=your-dev-key-1,your-dev-key-2
MCP_AUTH_ENABLED=true
```

Des valeurs de test sont utilisées en production. Si le middleware `mcp.auth` accepte ces valeurs, n'importe qui connaissant ces chaînes peut appeler `POST /api/mcp/`.

---

## 4. Ce qui est stocké dans localStorage

| Clé | Contenu | Qui l'écrit | Qui le lit |
|-----|---------|-------------|------------|
| `token` | Token Sanctum (chaîne) | `useAuth.login()` si remember=true | `api/client.ts` — ajouté à chaque requête |
| `ap_session` | JSON : `{ name, email, role, loggedAt, remember }` | `useAuth.login()` si remember=true | `useAuth.loadSession()`, `App.vue` (nom affiché) |
| `diagnostic_history` | Tableau JSON : `[{ id, date, device, result }]` | `ValidationStep.vue — onSave()` | Non relu nulle part (données orphelines) |
| `ap_repairs` | Tableau JSON : réparations créées avant l'API | Ancienne version de `useRepairs.ts` | `useAuth.migrateLocalRepairsIfNeeded()` — migration unique |
| `ap_repairs_migrated_${timestamp}` | Sauvegarde des réparations migrées | `useAuth.migrateLocalRepairsIfNeeded()` | Jamais relu — sauvegarde de sécurité |
| `ap_theme` | `'dark'` ou `'light'` | `App.vue` — toggleDark() | `App.vue` — onMounted() |

**Note :** `ap_session` et `token` vont dans `sessionStorage` si remember=false.

---

## 5. Plan par étapes

### Étape 0 — Sécurité (à faire AVANT tout le reste)

1. **Supprimer les mots de passe du frontend** — retirer `demoAccounts` avec passwords de `LoginView.vue`. Remplacer par des boutons qui pré-remplissent seulement l'email. Le mot de passe doit être saisi manuellement.
2. **Vérifier que les comptes démo n'existent pas en base** — ou changer leurs mots de passe immédiatement dans Supabase.
3. **Passer `APP_DEBUG=false`** sur Wasmer (variable d'environnement) pour ne plus exposer les traces de pile en production.
4. **Protéger les routes de diagnostic** — ajouter `middleware('auth:sanctum')` sur les routes `/diagnostic/*`, `/symptoms/*`, `/evolution/*`.

---

### Étape 1 — Moteur de diagnostic honnête

Objectif : supprimer les valeurs inventées, ajouter une vraie logique d'hypothèse → test.

1. **Supprimer le `confidence: 0.85` fixe** — le remplacer par `null` ou une mention "données insuffisantes" tant qu'aucune logique réelle n'existe.
2. **Supprimer le calcul de coût arbitraire** — retourner `null` jusqu'à ce que les prix soient dans la base.
3. **Corriger `getByDevice()`** — implémenter le filtre par appareil via la table pivot `device_symptom`.
4. **Persister les sessions de diagnostic** — créer une table `diagnostic_sessions` pour sauvegarder le parcours.
5. **Implémenter `validateResults()`** — écrire le résultat confirmé en base.
6. **Implémenter `history()`** — lire depuis la table `diagnostic_sessions`.
7. **Alimenter `analysisResults`** — relier chaque symptôme à une hypothèse structurée (composant probable + test recommandé).

---

### Étape 2 — Base de valeurs normales et procédures de test

Objectif : avoir des données mesurables et vérifiables pour chaque composant.

1. **Créer une table `component_normal_values`** — tensions nominales, résistances, fréquences par composant et modèle d'appareil. Ne mettre que des valeurs vérifiées avec une source.
2. **Créer une table `test_procedures`** — procédures de mesure avec outil requis, point de test, valeur attendue, interprétation.
3. **Lier les procédures aux symptômes** via la table pivot `symptom_component`.
4. **Afficher les tests dans le wizard** — à l'étape "Analyse", proposer des tests concrets avec valeurs normales.

---

### Étape 3 — Enrichissement de la base par les cas réels

Objectif : chaque réparation terminée enrichit la connaissance.

1. **Enrichir `EvolutionEvent`** — à la validation d'une réparation, enregistrer automatiquement : symptômes confirmés, composant remplacé, résultat.
2. **Créer une table `repair_cases`** — cas résolus avec symptômes, diagnostic, solution, durée, coût réel.
3. **Statistiques fiables** — fréquence réelle des pannes par modèle, taux de réussite par type de réparation.

---

### Étape 4 — Compléter les fonctionnalités incomplètes

1. **Dépannage** — implémenter les 29 catégories manquantes dans `DepannageController` ou migrer les données vers une table DB.
2. **Outils** — créer une table `tools` et migrer les données du contrôleur.
3. **Corriger le schéma** — harmoniser `severity_level` (entier) et `severity` (chaîne) entre migration et modèle.
4. **Supprimer les routes dupliquées** dans `routes/api.php`.
5. **`totalCost` dans ValidationStep** — calculer depuis les pièces de rechange réelles.
6. **`diagnostic_history`** dans localStorage — soit supprimer cette clé orpheline, soit la connecter à l'historique API.

---

## Résumé en langage simple

**Ce qui marche vraiment :** la base de données existe, les composants, symptômes, codes secrets et appareils sont dedans. L'analyse de diagnostic fait de vraies recherches en base (symptôme → composant → guide). Le module de réparation fonctionne de bout en bout. L'authentification Sanctum est en place.

**Ce qui est simulé :** le score de confiance (toujours 0.85), le calcul de coût (une formule inventée), la barre de progression (animation cosmétique), la validation du diagnostic (elle n'enregistre rien), l'historique (retourne toujours vide). Le dépannage n'a que 2 catégories sur 31 remplies. Les outils n'ont pas de table en base.

**Le problème de sécurité le plus grave :** trois mots de passe en clair (dont un Admin) sont visibles dans le code JavaScript envoyé à tous les navigateurs. C'est la première chose à corriger.

**Le vrai travail pour le moteur de diagnostic :** il faut une table de valeurs normales (tensions, résistances) liée aux composants, des procédures de test structurées, et un mécanisme pour enregistrer ce qui a vraiment été trouvé et réparé. Sans ça, le système fait des suggestions mais ne peut pas guider un test mesurable.
