# Espaces par type d'utilisateur

Chaque type d'utilisateur a son espace, avec son menu. **Le serveur applique les droits** ; l'interface ne fait que masquer ce qui ne concerne pas l'utilisateur.

| Type | Libellé affiché | Menu | Endpoints réservés |
|------|-----------------|------|--------------------|
| Administrateur de la plateforme | Administrateur de la plateforme | + **Administration** | `GET /api/admin/overview`, écriture dans l'évolution |
| Responsable d'atelier (rôle `Admin`) | Responsable d'atelier | + **Gestion de l'atelier** | `GET`/`PUT /api/workshop` |
| Technicien senior | Technicien senior | Menu atelier, connaissances et outils | Suppression d'une réparation |
| Technicien | Technicien | Menu atelier, connaissances et outils | Aucun endpoint réservé |

Un compte créé par inscription est **Responsable de son propre atelier**, jamais administrateur de la plateforme.

## Ce que chaque espace voit

- **Administration** : nombre d'ateliers, de comptes et de réparations ; liste des ateliers (nom, date, membres, nombre de réparations) ; derniers comptes. **Jamais** de réparation ni de client : des compteurs seulement (vérifié par un test).
- **Gestion de l'atelier** : nom de l'atelier (modifiable), nombre de réparations, équipe (nom, e-mail, rôle) **de cet atelier uniquement**.
- **Évolution** : lisible par tous ; le bouton d'ajout n'apparaît que pour la plateforme.

## Protection en deux couches

1. **Serveur** (décisive) : middlewares `role:` et `platform.admin`, isolation par atelier. Un technicien qui appelle `/api/workshop` ou `/api/admin/overview` reçoit `403`.
2. **Interface** : menus masqués et accès direct par l'adresse refusé (`/atelier`, `/admin` renvoient au tableau de bord).

## Autres corrections de ce lot

- À la déconnexion, les données en mémoire (liste des réparations) sont vidées : aucune donnée d'un compte ne reste visible chez le suivant dans le même onglet.
- Le profil (rôle, atelier, droits) est resynchronisé avec le serveur à l'ouverture de l'application.

## Pas encore construits

Espace **Client** (suivi de sa réparation par lien ou QR code) et espace **Apprenant** (formation). L'invitation de techniciens dans un atelier existant n'est pas non plus faite : tant qu'elle ne l'est pas, l'équipe d'un atelier se limite à son responsable.
