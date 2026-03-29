## Racoin

Racoin est une application de vente en ligne entre particulier.

## Installation

Les commandes suivantes permettent d'installer les dépendances et de construire les fichiers statiques nécessaires au bon fonctionnement de l'application.

```bash
cp config/config.ini.dist config/config.ini
docker compose run --rm php composer install
docker compose run --rm php php sql/initdb.php
docker compose run node npm install
docker compose run node npm run build

```

## Utilisation

Pour lancer l'application, il suffit de lancer la commande suivante:

```bash
docker compose up
```

## Outils de qualite (refactoring)

Le projet inclut maintenant des outils pour securiser la refactorisation.

Executer les tests:

```bash
docker compose run --rm php composer test
```

Executer l'analyse statique:

```bash
docker compose run --rm php composer analyse
```

Executer les regles de style:

```bash
docker compose run --rm php composer lint
```

## Logs HTTP

Chaque requete HTTP est maintenant loggee dans:
`var/log/http.log`

## Documentation OpenAPI

Generer la specification OpenAPI:

```bash
docker compose run --rm php composer openapi
```

Fichier genere:
`public/openapi.yaml`

Endpoint de consultation:
`/api/docs`
