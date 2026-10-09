# Audit technique initial : Aide Phone (version vérifiée)

- **Dépôt analysé :** `fpythonz-arch/aide-phone-repair`, branche `main`, dernier commit `6ef2aa6` (21 commits, du 23 août au 2 octobre 2026)
- **Date de l'audit :** 3 octobre 2026 · **mis à jour le 8 octobre 2026** (voir §13)
- **Méthode :** clone complet du dépôt, lecture du code, recherche dans tout l'historique git, vérification de types du frontend. Aucun fichier du projet n'a été modifié.
- **Règle de ce document :** aucun mot de passe, token ou clé n'y est écrit. Les secrets trouvés sont décrits, jamais recopiés.
- **Légende :** ✅ vérifié dans le code · ⚠️ déduit, non prouvé · ❓ non vérifiable depuis le dépôt

> Ce document **remplace** l'ancien `docs/audit-initial.md`, qui contenait lui-même les mots de passe de démonstration en clair (lignes 74 à 76).

---

## 0. À faire en premier (aucun code à écrire)

Ces actions se font sur GitHub, Supabase et ton hébergeur, pas dans le code. Elles passent avant tout le reste.

| # | Action | Pourquoi |
|---|--------|----------|
| 1 | **Révoquer le token GitHub personnel** (GitHub → Settings → Developer settings → Personal access tokens) | Un token `ghp_…` a été commité dans `structure.txt` (commit `8c957dd`, 23 août) puis « supprimé » (commit `f88209c`). Le dépôt est public : le token reste lisible dans l'historique. |
| 2 | **Changer le mot de passe de la base Supabase** | Le même fichier contenait l'hôte et le mot de passe de la base. |
| 3 | **Régénérer les clés API Supabase** (anon, et service_role si elle a servi) | Une clé `anon` (JWT) figurait dans le même fichier. |
| 4 | **Régénérer `APP_KEY`** sur l'hébergeur | Elle figurait aussi dans ce fichier. |
| 5 | **Changer ou supprimer les comptes de démo en production** | Ils sont recréés à chaque démarrage (voir S2). Tant que le code n'est pas corrigé, les changer ne suffit pas : ils reviendront. |

Supprimer un fichier d'un dépôt ne l'efface pas de l'historique. Seule la **rotation** (changer les secrets) protège vraiment. Réécrire l'historique (`git filter-repo`) est utile ensuite, mais ne remplace jamais la rotation : des copies du dépôt ont pu être faites entre-temps.

---

## 1. Résumé en langage simple

**Ce qui marche :** le site est en ligne, les utilisateurs peuvent se connecter, le module Réparations fonctionne de bout en bout, et le catalogue (composants, symptômes, codes secrets, appareils) vient bien de la base de données. Il y a environ 90 tests backend écrits.

**Ce qui est faux ou simulé :** la « confiance » du diagnostic est un chiffre fixe (0,85). Le coût est une formule inventée. Une seconde « probabilité », cachée dans un autre fichier, repose sur une valeur par défaut de 50 % et un bonus arbitraire. La validation et l'historique du diagnostic n'enregistrent rien.

**Le danger principal :** des secrets sont lisibles publiquement dans l'historique GitHub, et les comptes administrateur de démonstration sont recréés à chaque déploiement avec des mots de passe connus. N'importe qui peut donc, très probablement, se connecter en admin.

**Ce que ça change pour ton projet :** l'application n'est pas encore un moteur de diagnostic. C'est un catalogue avec un assistant de choix de composants. Le chemin symptôme → hypothèses → test → mesure → réévaluation n'existe pas dans le code. Il faut le construire, mais seulement après avoir sécurisé la base.

---

## 2. Architecture actuelle

```
Vue 3 + TS (Vercel)  ──HTTPS──▶  Laravel 12 (Docker, Wasmer)  ──▶  PostgreSQL (Supabase)
  frontend/                       app/, routes/api.php              tables créées en partie à la main
  Tailwind v4, Axios              Sanctum (token Bearer)
                                  MCP maison (app/MCP, 5 « serveurs »)
```

| Élément | Constat |
|---------|---------|
| Backend | Laravel 12, PHP 8.2, Sanctum 4. ✅ |
| Frontend | Vue 3 + TypeScript + Vite + Tailwind v4, 18 routes Vue. ✅ |
| Base de données | PostgreSQL sur Supabase. Le JOURNAL indique que les tables ont été créées à la main dans l'éditeur SQL : ❓ le schéma réel de production peut différer des migrations. |
| Authentification | Token Sanctum envoyé en `Bearer`. ✅ |
| Déploiement | Trois configurations coexistent : Wasmer (utilisé), Koyeb (`koyeb.yaml` périmé, pointe vers un dossier `backend/` qui n'existe plus) et Vercel (frontend). |
| MCP | 5 serveurs (codes, composants, diagnostic, évolution, outils). Aucun des outils cibles (`diagnostic.start`, `diagnostic.add_measurement`, etc.) n'existe. |

---

## 3. Ce qui fonctionne vraiment ✅

| Fonctionnalité | Détail |
|----------------|--------|
| Connexion / déconnexion / profil | `POST /api/auth/login`, `logout`, `GET /me`. Mot de passe haché, token créé par Sanctum. |
| Réparations (CRUD) | Protégé par `auth:sanctum`. Numérotation automatique, statuts, priorités, import limité à 500 lignes avec validation. |
| Catalogue | Appareils, symptômes, composants, codes secrets : requêtes réelles en base, avec jeux de données importants (le seeder des composants fait 1 258 lignes). |
| Évolution (timeline) | CRUD et agrégats réels. |
| Tests backend | 9 fichiers, environ 90 méthodes, base SQLite en mémoire. ❓ Non exécutés (voir §11). |

**Limite des données :** aucun seeder ne cite de source. Les composants et symptômes du catalogue ne sont rattachés à aucune documentation. Ils ne peuvent pas être traités comme des « données techniques sourcées » au sens de tes règles.

---

## 4. Ce qui est simulé, incomplet ou cassé

### 4.1 Valeurs inventées (contraires à ta règle « ne jamais fabriquer »)

| Problème | Où | Détail |
|----------|----|--------|
| Confiance fixe | `DiagnosticController::analyze()` | `'confidence' => 0.85`, sans aucun calcul. ✅ |
| Coût inventé | idem | `min = nb_composants × 25`, `max = nb_composants × 85`. ✅ |
| **Probabilité cachée (absente de l'ancien audit)** | `app/Services/ComponentMapper.php` | Valeur par défaut `?? 50`, puis bonus de +15 % par symptôme correspondant (plafonné à 3), plafonné à 100. La colonne `probability` de la table pivot vaut 50,00 par défaut, et je n'ai trouvé aucun code du dépôt qui la renseigne. ✅ Résultat probable : des scores quasi uniformes qui n'ont aucun sens statistique. |
| Disponibilité par défaut | `DiagnosticFlow` | `?? 'in_stock'` : une pièce inconnue est affichée « en stock ». ✅ |

### 4.2 Fonctions qui ne font rien

| Problème | Détail |
|----------|--------|
| `validateResults()` | Valide la requête puis renvoie `success: true` **sans rien écrire en base**. ✅ |
| `history()` | Renvoie toujours `[]`. ✅ |
| `nextSteps()` | Trois phrases fixes, indépendantes de la session. ✅ |
| Session de diagnostic | Un identifiant est généré mais jamais stocké. ✅ |
| `SymptomController::getByDevice()` | Ignore l'identifiant de l'appareil et renvoie tous les symptômes. ✅ Le modèle `Symptom` référence une table `device_symptom` qu'aucune migration ne crée. |
| Routes `/api/resources/*` | Trois endpoints qui renvoient toujours `data: []`. ✅ Interdit par ta règle sur les endpoints vides. |

### 4.3 Routes cassées

Sur 61 routes pointant vers un contrôleur, **2 pointent vers une méthode inexistante** (erreur 500 garantie) :

- `POST /api/tools/{slug}/execute` → `ToolController::execute` n'existe pas ✅
- `GET /api/components/components/{slug}` → `ComponentController::showBySlug` n'existe pas ✅

De plus, `/components/components` et `/components/components/{id}` sont masquées par la route `/{component}` déclarée avant elles : ce sont des routes mortes. ✅

### 4.4 Frontend

| Problème | Détail |
|----------|--------|
| **190 erreurs TypeScript dans 41 fichiers** | Constaté avec `vue-tsc`. Le build utilise `vite build` seul : les erreurs de types sont masquées (commit `402186e`). ✅ |
| Configuration TypeScript incomplète | `tsconfig.app.json` étend `@vue/tsconfig`, qui n'est pas déclaré dans `package.json`. ✅ Les 190 erreurs sont donc peut-être un minimum. |
| Composants dupliqués | 6 noms de fichiers existent en double dans des dossiers différents (`BatteryHealthTool`, `ComponentDetail`, `ComponentExplorer`, `IMEITool`, `ScreenTestTool`, `SymptomGrid`) ; deux dossiers `composants/` et `components/components/`. ✅ |
| Barre de progression, `totalCost`, `analysisResults` | ❓ Affirmés par l'ancien audit, non revérifiés par moi. |
| Dépannage : 29 catégories sur 31 vides, outils sans table | ❓ Même statut. |

---

## 5. Risques de sécurité (par gravité)

### 🔴 Critique

**S1. Secrets publics dans l'historique git** ✅
Le fichier `structure.txt` (ajouté en `8c957dd`, retiré en `f88209c`) contenait : un token GitHub personnel, une clé Supabase `anon`, l'hôte et le mot de passe de la base, et `APP_KEY`. Le dépôt est public. **Considère ces secrets comme compromis.** Voir §0. L'ancien audit concluait « bonne nouvelle, `.env` n'est pas commité » : c'est exact pour `.env`, mais cela a masqué ce problème, qui est plus grave.

**S2. Comptes de démonstration (dont deux admins) recréés à chaque déploiement** ✅
Le script `docker/start.sh` exécutait `db:seed --class=UserSeeder` à chaque démarrage, avec `updateOrCreate`, qui **réinitialise le mot de passe** de chaque compte à une valeur connue publiquement. *(Mise à jour : les logs de production montrent que l'app tourne sur le runtime PHP de Wasmer, pas par ce script ; la recréation à chaque déploiement n'est donc pas confirmée. Les comptes existaient en tout cas bien en base : constaté.)* Il existe quatre comptes, dont `admin@aidephone.com` (non mentionné par l'ancien audit). Ces mots de passe apparaissent dans : `UserSeeder.php`, `LoginView.vue` (envoyé à tous les navigateurs), `JOURNAL.md` (lignes 206 à 209), `AuthTest.php` et l'ancien audit. Tout ça viole ta règle « aucun mot de passe dans README, JOURNAL, code ».

### 🟠 Important

**S3. Aucun contrôle de permissions (RBAC)** ✅
Le champ `role` est stocké et renvoyé, mais **jamais lu** : aucun `Gate`, `Policy`, `authorize` ni middleware de rôle dans `app/` ni `routes/`. Un « Technicien » peut tout faire, et chaque utilisateur connecté voit et modifie **toutes** les réparations (nom, téléphone, email des clients) : il n'y a pas de filtre par technicien ni par atelier. Le `role` est aussi copié dans `localStorage`, donc modifiable par l'utilisateur côté navigateur.

**S4. Écritures publiques sans authentification** ✅
Sont ouverts à tous : `POST/PUT/DELETE /api/evolution` (n'importe qui peut modifier ou supprimer des données), `POST /api/diagnostic/*`, `POST /api/tools/check-inventory`, `POST /api/codes/*`. L'ancien audit ne signalait que `POST /evolution`.

**S5. Aucune limitation de tentatives sur la connexion** ✅
Pas de `throttle` sur `POST /api/auth/login`. Combiné à S2, c'est une porte ouverte au test massif de mots de passe.

**S6. Tokens sans expiration, stockés dans `localStorage`** ✅ / ⚠️
Il n'y a pas de `config/sanctum.php` : les valeurs par défaut du package s'appliquent, et ce défaut est « jamais d'expiration » ⚠️. Un token volé (XSS) reste donc valable indéfiniment. Il est lu depuis `localStorage` si « Se souvenir de moi » est coché, sinon depuis `sessionStorage` ✅.

**S7. Authentification MCP contournable selon l'environnement** ✅
`MCPAuthMiddleware::isValidApiKey()` renvoie `true` pour **n'importe quelle clé** si `APP_ENV` vaut `local` ou `testing`. Or `.env.example` met `APP_ENV=local` et `APP_DEBUG=true` : si ce fichier est copié tel quel en production, le MCP est ouvert. De plus, `GET /api/mcp/info` et `/servers` sont publics, la comparaison des clés n'est pas à temps constant, et `explode(',', '')` produit une clé vide si la variable n'est pas définie. Les clés « de dev » citées par l'ancien audit viennent d'un `.env` local que je ne peux pas voir ❓.

### 🟡 Moyen

| # | Constat |
|---|---------|
| S8 | **Données personnelles dans les logs** ✅ : `RequestLoggerMiddleware` journalise le corps des requêtes POST/PUT/PATCH. Les champs `password`, `token` et similaires sont masqués (bien), mais `client_name`, `client_phone`, `client_email`, IMEI sont écrits en clair dans `requests.log`. |
| S9 | **Deux systèmes CORS superposés** ✅ : un middleware maison (`CorsMiddleware`) et la config Laravel `config/cors.php`, avec des listes d'origines différentes. Le domaine Vercel de la config (`aide-phone-repair-three.vercel.app`) diffère de celui affiché sur GitHub (`aide-phone-repair.vercel.app`) : ❓ à vérifier. |
| S10 | **`/api/health` expose l'environnement** (`app()->environment()`) ✅. |
| S11 | **`APP_DEBUG` en production** ❓ : le Dockerfile ne le fixe pas, il vient de l'hébergeur. L'ancien audit affirme qu'il vaut `true` ; je ne peux pas le confirmer. |
| S12 | **Uploads/photos** : aucun upload n'existe encore. À sécuriser dès la première fonction photo (type MIME, taille, stockage hors de `public/`). |
| S13 | **Mass assignment** : `User::$fillable` contient `role`. Pas exploitable aujourd'hui (aucune route ne fait `User::create($request->all())`), mais dangereux dès qu'on ajoute une inscription ou un profil modifiable. |
| S14 | **CSRF** : sans objet tant que l'API reste en token Bearer (pas de cookie de session). À réévaluer si tu passes à des cookies. |

---

## 6. Ce qui est stocké dans `localStorage` / `sessionStorage` ✅

| Clé | Contenu | Problème |
|-----|---------|----------|
| `token` | Token Sanctum | Lisible par tout script de la page (XSS). Pas d'expiration (S6). |
| `ap_session` | `{ name, email, role, … }` | Le rôle y est modifiable ; ne jamais s'en servir pour autoriser. |
| `diagnostic_history` | Liste de diagnostics validés | **Donnée métier critique stockée côté navigateur**, écrite par `ValidationStep.vue`, jamais relue. Perdue au changement d'appareil ou de navigateur. À remplacer par la base. |
| `ap_repairs`, `ap_repairs_migrated_*` | Anciennes réparations locales | Migration unique vers l'API, copies de sauvegarde jamais nettoyées. |
| `ap_theme` | `dark` / `light` | Légitime : préférence d'interface. À conserver. |

---

## 7. Modèle de données actuel

**Tables (migrations) :** `users` (+ `role`), `symptoms`, `components`, `symptom_component` (pivot avec `probability` par défaut 50), `repair_guides`, `replacement_parts`, `secret_codes`, `evolution_events`, `devices`, `clients`, `repair_counters`, `repairs`, `personal_access_tokens`.

**Incohérences :**
- `symptoms.severity_level` (entier) dans la migration, mais le modèle et les contrôleurs utilisent `severity` (texte). ✅ Cohérent avec l'hypothèse d'un schéma de production créé à la main.
- `Symptom` référence `device_symptom`, qui n'existe dans aucune migration. ✅
- `Repair` a à la fois `technician_id` (clé étrangère) et `technician` (texte libre). ✅
- Aucune notion d'**atelier** (`workshop`) : impossible d'isoler les données par atelier.

**Absent par rapport à ta vision (tout est à créer) :** `DiagnosticSession`, `Hypothesis`, `DiagnosticTest`, `Measurement`, `MeasurementPoint`, `Evidence`, `Fault`, `RepairOutcome`, `KnowledgeSource`, `ExpectedValue`, `Board`, `Pin`, `Net`, `Rail`, ainsi que l'inventaire (fournisseurs, prix, stock).

---

## 8. Dette technique

| Sujet | Détail |
|-------|--------|
| Logique métier | Une partie du calcul de diagnostic est dans un contrôleur (`analyze()`), une autre dans deux services (`ComponentMapper`, `DiagnosticFlow`) avec des règles différentes. À fusionner dans un seul domaine. |
| Convention d'API | Réponses non uniformes (`success` / `data` / `data` seul). Routes mélangeant français et anglais. |
| Dossiers parasites | `bootstrap/bootstrap/` est une copie erronée à supprimer. `frontend/.env.production` contient un commentaire périmé sur Render. |
| Documentation | `README.md` est le modèle Laravel par défaut : il ne décrit pas le projet. |
| Tests | Annotations `/** @test */` : dépréciées en PHPUnit 11 (version utilisée), supprimées en PHPUnit 12. Aucun test frontend, aucun test des permissions, aucune intégration continue (pas de `.github/`). |
| Cache | `ComponentMapper` met en cache les résultats 30 minutes sur la seule liste de symptômes : un changement de données reste invisible pendant ce temps. |

---

## 9. Écarts avec l'architecture cible

| Couche cible | État actuel |
|--------------|-------------|
| Auth + RBAC | Auth : oui. RBAC : **absent**. |
| Repair Domain | Partiel (CRUD réparations, sans lien avec le diagnostic). |
| Diagnostic Domain | **Absent** (seulement un classement de composants par symptôme). |
| Knowledge Domain | Données sans source. Pas de `KnowledgeSource`. |
| Inventory Domain | `replacement_parts` basique, sans stock ni fournisseurs. |
| Training Domain | Absent. |
| Knowledge Graph | Absent. |
| Recherche documentaire / vectorielle | Absente. |
| AI Orchestrator | Absent. |
| MCP | Squelette existant, outils non alignés avec la cible. |
| Offline / PWA | Absent (aucun service worker). |

---

## 10. Plan de migration P0 → P5

Principe : chaque étape se termine par des tests qui passent et **ta validation** avant la suivante. Un moteur de diagnostic construit sur une base non sécurisée est à refaire.

### P0. Sécurité, socle et nettoyage *(prérequis de tout le reste)*

| Étape | Contenu | Livrable vérifiable |
|-------|---------|---------------------|
| P0.0 | Actions humaines du §0 (rotation des secrets) | Tu confirmes que c'est fait |
| P0.1 | Supprimer le seed du `start.sh`. Comptes de démo créés uniquement en local, avec mots de passe aléatoires générés ou lus dans `.env`. Retirer tous les mots de passe de `LoginView.vue`, `JOURNAL.md`, `AuthTest.php` et de l'audit. | `grep` sur le dépôt : zéro mot de passe |
| P0.2 | Authentification : limitation de tentatives sur la connexion, expiration des tokens, `auth:sanctum` sur toutes les routes d'écriture. | Tests : 429 après N essais, 401 sans token |
| P0.3 | RBAC minimal (3 rôles : admin, technicien senior, technicien) via Policies. Filtre des réparations selon le rôle. | Tests de permissions par rôle |
| P0.4 | MCP : supprimer le contournement par environnement, clés hachées, endpoints d'info protégés. | Tests : clé invalide refusée dans tous les environnements |
| P0.5 | Logs sans données personnelles. Un seul système CORS, origines lues dans `.env`. `/health` sans environnement. | Tests + revue de config |
| P0.6 | Retirer les valeurs inventées : `confidence` devient `null` avec le message « Données insuffisantes pour estimer correctement cette hypothèse. » ; coût `null` ; fin du `?? 50` ; fin du `?? 'in_stock'` (affiche « inconnu »). | Tests : aucune valeur par défaut plausible |
| P0.7 | Ménage : supprimer les 2 routes cassées, les routes mortes, les 3 endpoints vides, `bootstrap/bootstrap/`, `koyeb.yaml`. Une convention REST et un format de réponse uniques. | `route:list` propre, doc des endpoints |
| P0.8 | Socle de tests : exécuter la suite existante en CI (GitHub Actions), migrer `@test` vers `#[Test]`, installer Vitest, ajouter `@vue/tsconfig`. Plan de correction des 190 erreurs de types. | CI verte |
| P0.9 | Migrer `diagnostic_history` du navigateur vers la base. Aligner le schéma (`severity`) par migration. | Migration réversible + test |

### P1. Moteur de session
`DiagnosticSession` avec machine à états (INITIAL → … → RESOLVED / UNRESOLVED) et historique de chaque transition, reprise de session, `Hypothesis`, `DiagnosticTest`, `Measurement`, `Evidence`, `RepairOutcome`. Les anciens endpoints `/diagnostic/*` sont remplacés par de vrais, pas supprimés avant remplacement.

### P2. Valeurs de référence et moteur de décision
`KnowledgeSource` (source, auteur, modèle, révision, niveau de confiance) et `ExpectedValue`. Comparaison mesure/attendu → NORMAL / ANORMAL / INCONNU (INCONNU si aucune plage sourcée). Mise à jour des hypothèses par les preuves ; choix du prochain test par réduction maximale d'incertitude ; explication systématique du « pourquoi ». Cas de diagnostic de référence pour tester le moteur.

### P3. Graphe électronique
`DeviceModel → Board → Component → Pin → Net → Rail`, positions X/Y, couche, boîtier, rotation, révision. Fiche composant.

### P4. IA, RAG, MCP avancé
IA au-dessus du moteur, jamais source de vérité. Outils MCP cibles, chacun sécurisé et documenté.

### P5. Offline, formation, stock, multi-ateliers, métriques
Mode hors ligne avec conflits de synchronisation explicites, module Training, inventaire avancé (FCFA), ateliers, indicateurs de précision.

> **Point d'attention sur le moteur (P2) :** un score d'hypothèse ne peut être honnête que s'il repose sur des **fréquences ou vraisemblances sourcées**. Au départ, il n'y en a aucune dans la base. Le moteur devra donc afficher « données insuffisantes » pour la plupart des hypothèses, et ne gagner en précision qu'à mesure que les réparations confirmées s'accumulent. C'est le comportement voulu, pas un défaut.

---

## 11. Ce que je n'ai pas pu vérifier

| Sujet | Raison |
|-------|--------|
| **Résultat des tests backend** | *(Mis à jour)* Exécutés depuis : voir §13 et `docs/p0-lot1.md`. |
| **État réel de la base de production** | Je n'ai pas accès à Supabase. Je ne sais pas si les comptes de démo y existent (mais S2 dit qu'ils sont recréés à chaque démarrage), ni si la table `symptom_component` est remplie, ni si le schéma a dérivé. |
| **Variables d'environnement de production** | `.env` n'est pas dans le dépôt (bien). `APP_DEBUG`, `APP_ENV` et `MCP_API_KEYS` de production sont inconnus. |
| **Le dépôt est-il cloné ailleurs ?** | Le token et les clés ont été publics pendant plusieurs semaines : suppose une compromission possible et vérifie l'activité récente sur GitHub (Settings → Security log) et sur Supabase (logs de connexion). |
| Dépannage, outils, barre de progression | Affirmations de l'ancien audit, non relues par moi. |

---

## 12. Décisions dont j'ai besoin avant de coder

1. **Données de production :** y a-t-il de vraies réparations de vrais clients dans Supabase aujourd'hui, ou seulement des données de test ? (Cela décide si on peut repartir d'une base propre.)
2. **Hébergement :** on garde Supabase + Wasmer + Vercel, ou tu envisages de changer ? (Cela évite de configurer P0 deux fois.)
3. **Qui voit quoi :** un technicien doit-il voir seulement **ses** réparations, ou toutes celles de l'atelier ? (Décide la règle de permission de P0.3.)

**Prochaine étape proposée :** P0.1 à P0.6 en un seul lot, avec tests, une fois le §0 confirmé.

---

## 13. Mises à jour après vérification (8 octobre 2026)

**Réalisé par vous (hors code) :** token GitHub révoqué ; mot de passe de la base changé ; clés `anon` / `service_role` désactivées et ancien secret JWT révoqué ; `APP_KEY` régénérée ; comptes de démo neutralisés en base et compte administrateur personnel créé ; connexion à la base rétablie via le **pooler Supabase en mode Session** (Wasmer n'a pas d'IPv6 sortant : la connexion directe expirait).

**Constatations supplémentaires :**

| # | Constat | Gravité |
|---|---------|---------|
| N1 | `symptom_component.probability` est `NOT NULL DEFAULT 50.00` et aucun seeder ne la renseigne : **toutes les « probabilités » du catalogue valent probablement 50** (valeur inventée inscrite dans le schéma). | Haute (intégrité des données) |
| N2 | Le moteur MCP (`Engine::validateAndScore`) calculait une confiance `min(0.95, 0.4 + 0.15 × nb_étapes)` : formule arbitraire. | Haute |
| N3 | `app/Exceptions/Handler.php` n'est branché nulle part (Laravel 12) : une clé MCP invalide produisait un **500** au lieu d'un 401. | Moyenne |
| N4 | `DiagnosticTest` décrivait une ancienne API (noms de symptômes, réponse à plat) et échouait donc déjà avant toute modification. | Moyenne |
| N5 | Le message « Email ou mot de passe incorrect » s'affichait pour toute panne (serveur injoignable, erreur 500). | Faible |
| N6 | Les estimations de main-d'œuvre de `ComponentMapper::estimateProfessionalCost` reposent sur des coefficients inventés, en euros (le produit vise le FCFA). À traiter avec la base de prix (P2). | Moyenne |
| N7 | Les coûts affichés par le module Dépannage (`useDepannage.ts`) sont des données statiques locales sans source. | Moyenne |
| N8 | `EvolutionController::store` : sans `symptom_id`, l'événement est rattaché **au premier symptôme de la base** (ou au n° 1) : donnée inventée. La règle « `repair_successful` obligatoire si `repair_attempted` » a aussi été retirée. Deux tests restent en attente de décision produit (P1). | Moyenne |
| N9 | `ComponentSeeder` (et d'autres) appliquent `json_encode()` à des colonnes qui ont déjà le cast `array` : les données sont **encodées deux fois** et l'API renvoie des chaînes au lieu de listes. Cela faisait planter `/components/{id}/alternatives` (500) ; le code tolère désormais ce format, mais les données restent à corriger. | Moyenne |
| N10 | Avant ce lot, **47 tests sur 93 échouaient déjà** sur `main` (API de diagnostic et réponses MCP obsolètes, ordre de chargement des jeux de données, catégories renommées). La suite est maintenant entièrement verte. | Informatif |

**Traité dans le lot P0 n° 1 (voir `docs/p0-lot1.md`) :** S2 (code), S3, S4, S5, S6, S7, S8, S9, S10, S13, N2, N3, N4, N5, N10, la partie code de N1 et le plantage de N9. **Reste ouvert :** N6, N7, N8 et la correction des données de N9.

---

## 14. Lot 2 : ateliers privés et inscription libre (9 octobre 2026)

Voir `docs/lot2-ateliers.md`. Traite l'isolation des données (S3) pour l'ouverture de l'inscription.

**Constatations supplémentaires, découvertes en lançant la suite sur PostgreSQL (le moteur de production) :**

| # | Constat | Gravité |
|---|---------|---------|
| N11 | `CodeResolver::popular` trie par `view_count`, colonne qui n'existe pas dans `secret_codes` (seulement dans `repair_guides`). SQLite l'ignore en silence ; **PostgreSQL renvoie une erreur** : `/api/codes/popular` renvoie très probablement une erreur 500 en production. | Moyenne |
| N12 | Les seeders utilisent des identifiants fixes (`symptom_id => 1`…) : ils échouent sur PostgreSQL dans les tests, car les séquences ne sont pas remises à zéro. 44 tests de `CodeTest`, `ComponentTest`, `EvolutionTest` et `MCPTest` échouent sur PostgreSQL ; la CI actuelle tourne sur SQLite et ne le voit pas. À corriger avant de basculer la CI sur PostgreSQL. | Moyenne |
| N13 | La numérotation des réparations est commune à tous les ateliers. | Faible |

