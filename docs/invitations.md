# Invitations de techniciens

Le responsable d'un atelier invite un technicien avec un **lien à usage unique**. Aucun e-mail n'est envoyé : le responsable transmet le lien lui-même (WhatsApp, SMS…).

## Parcours

1. **Responsable** (page *Gestion de l'atelier*) : choisit un rôle (Technicien ou Technicien senior), éventuellement une adresse e-mail pour réserver le lien, puis clique sur *Créer le lien*.
2. Le lien `https://…/rejoindre/<jeton>` est affiché **une seule fois**. Il est valable **7 jours** et ne sert **qu'une fois**.
3. **Invité** : ouvre le lien, voit le nom de l'atelier et son rôle, choisit nom, e-mail et mot de passe. Son compte est créé **dans l'atelier invitant** avec le rôle prévu.
4. **Responsable** : peut annuler un lien en attente, changer le rôle d'un membre ou le **retirer** (ses connexions sont coupées immédiatement).

## Garanties

| Règle | Comment |
|-------|---------|
| Une invitation ne donne **jamais** le rôle `Admin` ni de droits de plateforme | Rôles autorisés : `Technicien`, `Technicien senior` uniquement ; le rôle, l'atelier et les droits viennent de l'invitation, jamais de la requête |
| Le jeton n'est **pas stocké** | Seule son empreinte SHA-256 est en base ; la liste des invitations ne le renvoie jamais |
| Usage unique, même en cas de clics simultanés | Verrou de ligne dans une transaction ; un échec de validation ne consomme pas le lien |
| Lien réservé à une adresse | Si une adresse est indiquée, l'inscription est refusée avec une autre |
| Un responsable ne touche qu'à **son** atelier | Invitations et membres d'un autre atelier : 404 |
| Pas de fuite dans les journaux | Le journal enregistre le modèle de route (`api/invitations/{token}`), jamais l'adresse réelle |
| Anti-abus | 20 invitations en attente au plus par atelier ; 5 acceptations/min et 30/h par IP ; 30 vérifications/min par IP |

## Endpoints

| Endpoint | Méthode | Authentification | Détail |
|----------|---------|------------------|--------|
| `/api/workshop/invitations` | GET | Responsable d'atelier | Liste (sans jeton) |
| `/api/workshop/invitations` | POST | Responsable d'atelier | Corps : `role`, `email` (facultatif). Renvoie le jeton **une seule fois** (201) |
| `/api/workshop/invitations/{id}` | DELETE | Responsable d'atelier | Annule une invitation en attente (422 sinon) |
| `/api/workshop/members/{id}` | PUT | Responsable d'atelier | Corps : `role`. Impossible sur soi-même ou sur le responsable (422) |
| `/api/workshop/members/{id}` | DELETE | Responsable d'atelier | Retire le membre : jetons supprimés, plus d'atelier |
| `/api/invitations/{token}` | GET | Non | Nom de l'atelier, rôle, e-mail réservé. 404 inconnu ; 410 expiré, annulé ou déjà utilisé |
| `/api/invitations/{token}/accept` | POST | Non | Corps : `name`, `email`, `password`, `password_confirmation`. 201 + jeton de session |

## Mise en production

1. **Supabase > SQL Editor** : exécuter `docs/sql/lot4-invitations.sql` (rien à remplacer), **avant** de fusionner.
2. Fusionner le lot.

## Limites connues

- Un membre **retiré** garde un compte inerte (sans atelier) : son adresse e-mail reste prise. Il ne peut pas être ré-invité tant que la suppression de comptes n'existe pas.
- Un jeton figure dans l'adresse de l'API : l'application ne l'écrit pas dans ses journaux, mais les journaux d'accès de l'hébergeur peuvent le voir. Il reste à usage unique et expire en 7 jours.
- Pas d'envoi automatique de l'invitation par e-mail.
