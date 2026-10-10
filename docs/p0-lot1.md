# Lot P0 n° 1 : sécurité et valeurs inventées

## Règles d'accès (API)

| Endpoint | Méthode | Authentification | Rôle requis |
|----------|---------|------------------|-------------|
| `/api/health` | GET | Non | Aucun (n'expose plus l'environnement) |
| `/api/auth/login` | POST | Non | Aucun · **5 essais/min** par e-mail+IP (429 ensuite) |
| `/api/auth/register` | POST | Non | Aucun · voir `docs/lot2-ateliers.md` |
| `/api/workshop` | GET, PUT | Oui | Responsable d'atelier (rôle `Admin`) · voir `docs/espaces.md` |
| `/api/admin/overview` | GET | Oui | Administrateur de plateforme · voir `docs/espaces.md` |
| `/api/auth/me`, `/api/auth/logout` | GET, POST | Oui | Tous |
| `/api/repairs` (liste, création, lecture, modification, import) | GET, POST, PUT | Oui | Tous · limité à **son atelier** |
| `/api/repairs/{id}` | DELETE | Oui | Technicien senior ou Admin |
| `/api/diagnostic/*` | tous | Oui | Tous |
| `/api/components/map` | POST | Oui | Tous |
| `/api/codes/resolve`, `/api/codes/validate` | POST | Oui | Tous |
| `/api/tools/check-inventory` | POST | Oui | Tous |
| `/api/evolution` | POST, PUT, DELETE | Oui | Administrateur de plateforme (voir `docs/lot2-ateliers.md`) |
| `/api/mcp`, `/api/mcp/info`, `/api/mcp/servers` | GET, POST | Clé `X-API-Key` | Clé listée dans `MCP_API_KEYS` |
| Lectures du catalogue (`devices`, `components`, `symptoms`, `codes`, `evolution` GET…) | GET | Non (inchangé) | Aucun |

Le rôle `Admin` passe toutes les restrictions de rôle.
Codes d'erreur : `401` non authentifié, `403` droits insuffisants, `429` trop de tentatives, `422` validation.

## Variables d'environnement

| Variable | Rôle | Défaut |
|----------|------|--------|
| `SANCTUM_EXPIRATION` | Durée de vie des tokens, en minutes | 10080 (7 jours) |
| `FRONTEND_URL`, `CORS_ALLOWED_ORIGINS` | Origines CORS autorisées en plus de la liste de base | vide |
| `MCP_API_KEYS` | Clés MCP séparées par des virgules ; vide = MCP fermé | vide |

## Après déploiement : vérifications

1. `GET /api/devices/brands` renvoie la liste des marques (la base répond).
2. Connexion avec votre compte administrateur sur le site.
3. Les anciens comptes de démo ne permettent plus de se connecter.
4. Un technicien reçoit `403` en supprimant une réparation.
5. `GET /api/mcp/info` sans clé renvoie `401`.
6. Si le site ne charge plus depuis Vercel : vérifier `FRONTEND_URL` / `CORS_ALLOWED_ORIGINS` (CORS).

## Limites connues (non traitées dans ce lot)

- Résultat de la suite backend : **111 tests réussis, 2 en attente de décision produit** (`EvolutionTest`, audit N8), 0 échec. Avant ce lot, 47 tests sur 93 échouaient déjà sur `main`.
- Les données seedées sont encodées deux fois (audit N9) : l'API renvoie certaines listes sous forme de chaînes.
- La limitation de connexion dépend du magasin de cache (`CACHE_STORE`) : avec `array`, elle ne persiste pas entre requêtes. Utiliser `database` ou `redis`/`file` en production.
- Lectures du catalogue encore publiques (décision produit à prendre).
- 190 erreurs de types TypeScript préexistantes (non bloquantes en CI).
- Estimations de coût de main-d'œuvre et coûts du module Dépannage (N6, N7).
