# Suivi des Activités DSID

Application web interne de planification, suivi et reporting des activités de la Direction des Systèmes d’Information et de la Digitalisation.

## Socle technique

- Laravel 13 / PHP 8.3 avec PHP-FPM et Nginx ;
- PostgreSQL, exposé sur le port `7000` de l’hôte (port interne Docker `5432`) ;
- Blade et Bootstrap 5 ;
- stockage privé des documents, contrôle d’accès par rôle et journal d’audit.

## Démarrage avec Docker

Depuis le dossier parent contenant `docker-compose.yml` :

```powershell
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

Ouvrir ensuite [http://localhost:8080](http://localhost:8080). PostgreSQL est accessible depuis l’hôte via `localhost:7000`. Dans les conteneurs, l’application se connecte à `db:5432`.

La configuration de développement dans `dsid/.env` utilise la base `dsid`, l’utilisateur `dsid` et le mot de passe `momo7`. Ne réutilisez pas ces identifiants en production ; fournissez des secrets propres à l’environnement.

## Comptes de démonstration

Le seeder crée cinq comptes (un par rôle) :

| Rôle | Adresse |
|---|---|
| Administrateur | `admin@dsid.test` |
| Directeur | `directeur@dsid.test` |
| Chef de service | `chef@dsid.test` |
| Agent | `agent@dsid.test` |
| Lecteur | `lecteur@dsid.test` |

Le mot de passe initial est lu depuis `DSID_SEED_PASSWORD` (valeur locale : `ChangeMe123!`). Changez-le immédiatement et définissez un secret fort avant tout environnement partagé. La réexécution de `migrate --seed` remet le mot de passe de ces comptes à la valeur configurée.

## Commandes utiles

```powershell
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan route:list
docker compose exec app php artisan test
docker compose logs -f app nginx db
```

Les pièces jointes sont enregistrées sur le disque privé Laravel et ne sont servies qu’après authentification et vérification des droits. Les sauvegardes doivent couvrir la base PostgreSQL et le répertoire `dsid/storage/app/private`.

## Fonctionnalités livrées

- connexion, déconnexion, réinitialisation du mot de passe et limitation des tentatives ;
- gestion des rôles, utilisateurs, domaines, activités, tâches et documents ;
- tableau de bord, filtres d’activités, retards calculés et rapport imprimable ;
- progression d’une activité calculée depuis la moyenne de progression de ses tâches ;
- journal append-only des créations, mises à jour et archivages métier.

Les exports Excel/PDF automatiques, les axes SDSI administrables et la validation formelle par la direction restent des évolutions à compléter.
