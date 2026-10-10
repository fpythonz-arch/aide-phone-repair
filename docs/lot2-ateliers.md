# Lot 2 : ateliers privés et inscription libre

## Principe

Chaque inscription crée **un atelier privé** dont la personne est l'administratrice.
Les réparations et les clients appartiennent à un atelier et ne sont **jamais** visibles par un autre.
Le catalogue (composants, codes, symptômes, appareils) reste commun, en lecture.

## Deux niveaux de droits, volontairement séparés

| Niveau | Où | Valeurs | Sert à |
|--------|----|---------|--------|
| **Rôle d'atelier** | `users.role` | `Admin`, `Technicien senior`, `Technicien` | Ce qu'on peut faire dans son atelier (ex. supprimer une réparation : senior ou admin) |
| **Administrateur de plateforme** | `users.is_platform_admin` | `true` / `false` | Modifier les données **communes à tous** (événements d'évolution). Jamais attribué par une inscription |

Un « Admin » d'atelier n'a donc aucun pouvoir sur les autres ateliers ni sur les données communes.

## Isolation : comment elle est garantie

- `Repair` et `Client` utilisent `BelongsToWorkshop` : un filtre global limite toute requête à l'atelier de l'utilisateur connecté.
- Les données d'un autre atelier se comportent comme **inexistantes** (404, jamais 403) : on ne révèle même pas leur existence.
- `workshop_id` n'est **pas assignable en masse** : il vient de l'utilisateur connecté, jamais de la requête.
- Un utilisateur sans atelier ne voit **rien** (fermé par défaut).
- Les clients sont cloisonnés : le même numéro de téléphone crée un client distinct dans chaque atelier.
- Les tests `WorkshopIsolationTest` échouent (7 sur 9) si l'isolation est désactivée : ils détectent bien la faille.

## Endpoints ajoutés ou modifiés

| Endpoint | Méthode | Authentification | Détail |
|----------|---------|------------------|--------|
| `/api/auth/register` | POST | Non | Crée atelier + compte `Admin` d'atelier + token. 5/min et 30/h par IP (429 ensuite). 422 si données invalides |
| `/api/auth/login` | POST | Non | Adresse e-mail insensible à la casse |
| `/api/auth/me` | GET | Oui | Renvoie aussi `workshop` et `is_platform_admin` |
| `/api/evolution` (POST, PUT, DELETE) | | Oui | **Administrateur de plateforme uniquement** (avant : technicien senior) |

Corps de `POST /api/auth/register` : `name`, `workshop_name`, `email`, `password` (10 caractères minimum, lettres et chiffres), `password_confirmation`.

## Mise en production (dans cet ordre)

1. **Supabase > SQL Editor** : coller et exécuter `docs/sql/lot2-ateliers.sql` (sans rien modifier). Résultat attendu : 1 atelier, 0 ligne sans atelier.
2. **Fusionner** le lot sur GitHub (Wasmer et Vercel redéploient).
3. **Supabase > SQL Editor** : exécuter `docs/sql/lot2-admin-plateforme.sql` après avoir remplacé `REMPLACE_PAR_TON_EMAIL` par ton e-mail. Le script refuse de s'exécuter si le compte n'est pas trouvé.
4. Facultatif, recommandé : `docs/sql/rls-supabase-optionnel.sql`.

Les migrations Laravel équivalentes existent mais ne sont pas exécutées automatiquement par l'hébergement actuel : les scripts SQL ci-dessus en tiennent lieu. La migration est idempotente et peut être rejouée sans erreur après eux.

## Limites connues

- Numérotation des réparations (`REP-AAAA-NNN`) commune à la plateforme et non par atelier.
- Pas de vérification de l'adresse e-mail, ni de « mot de passe oublié » (nécessitent l'envoi d'e-mails).
- Pas encore d'invitation de techniciens dans un atelier existant.
- Un compte reste sans limite de durée de conservation des données.
- Les données de démonstration de l'ancien navigateur (`localStorage`) ne sont pas importées pour un nouveau compte (volontaire).
- La suite de tests est exécutée sur SQLite ; 44 tests de classes antérieures échouent sur PostgreSQL (voir audit N11).
