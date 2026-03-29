<?php

declare(strict_types=1);

namespace App\Http;

use controller\KeyGenerator;
use controller\Search;
use controller\addItem;
use controller\getCategorie;
use controller\getDepartment;
use controller\index;
use controller\item;
use controller\viewAnnonceur;
use model\Annonce;
use model\Annonceur;
use model\Categorie;
use model\Departement;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Twig\Environment;

final class Routes
{
    public static function register(App $app, Environment $twig): void
    {
        $menu = [
            [
                'href' => './index.php',
                'text' => 'Accueil',
            ],
        ];

        $chemin = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $cat = new getCategorie();
        $dpt = new getDepartment();

        $app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $menu, $chemin, $cat): ResponseInterface {
            $controller = new index();
            $controller->displayAllAnnonce($twig, $menu, $chemin, $cat->getCategories());

            return $response;
        });

        $app->get('/item/{n}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($twig, $menu, $chemin, $cat): ResponseInterface {
            $controller = new item();
            $controller->afficherItem($twig, $menu, $chemin, (int) $args['n'], $cat->getCategories());

            return $response;
        });

        $app->get('/add', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $menu, $chemin, $cat, $dpt): ResponseInterface {
            $controller = new addItem();
            $controller->addItemView($twig, $menu, $chemin, $cat->getCategories(), $dpt->getAllDepartments());

            return $response;
        });

        $app->post('/add', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $menu, $chemin): ResponseInterface {
            $controller = new addItem();
            $controller->addNewItem($twig, $menu, $chemin, (array) $request->getParsedBody());

            return $response;
        });

        $app->get('/item/{id}/edit', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($twig, $menu, $chemin): ResponseInterface {
            $controller = new item();
            $controller->modifyGet($twig, $menu, $chemin, (int) $args['id']);

            return $response;
        });

        $app->post('/item/{id}/edit', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($twig, $menu, $chemin, $cat, $dpt): ResponseInterface {
            $controller = new item();
            $controller->modifyPost($twig, $menu, $chemin, (int) $args['id'], $cat->getCategories(), $dpt->getAllDepartments());

            return $response;
        });

        $app->post('/item/{id}/confirm', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($twig, $menu, $chemin): ResponseInterface {
            $controller = new item();
            $controller->edit($twig, $menu, $chemin, (array) $request->getParsedBody(), (int) $args['id']);

            return $response;
        });

        $app->get('/search', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $menu, $chemin, $cat): ResponseInterface {
            $controller = new Search();
            $controller->show($twig, $menu, $chemin, $cat->getCategories());

            return $response;
        });

        $app->post('/search', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $menu, $chemin, $cat): ResponseInterface {
            $controller = new Search();
            $controller->research((array) $request->getParsedBody(), $twig, $menu, $chemin, $cat->getCategories());

            return $response;
        });

        $app->get('/annonceur/{n}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($twig, $menu, $chemin, $cat): ResponseInterface {
            $controller = new viewAnnonceur();
            $controller->afficherAnnonceur($twig, $menu, $chemin, (int) $args['n'], $cat->getCategories());

            return $response;
        });

        $app->get('/del/{n}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($twig, $menu, $chemin): ResponseInterface {
            $controller = new item();
            $controller->supprimerItemGet($twig, $menu, $chemin, (int) $args['n']);

            return $response;
        });

        $app->post('/del/{n}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($twig, $menu, $chemin, $cat): ResponseInterface {
            $controller = new item();
            $controller->supprimerItemPost($twig, $menu, $chemin, (int) $args['n'], $cat->getCategories());

            return $response;
        });

        $app->get('/cat/{n}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($twig, $menu, $chemin, $cat): ResponseInterface {
            $controller = new getCategorie();
            $controller->displayCategorie($twig, $menu, $chemin, $cat->getCategories(), (int) $args['n']);

            return $response;
        });

        $app->get('/api', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $chemin): ResponseInterface {
            $template = $twig->load('api.html.twig');
            $breadcrumb = [
                [
                    'href' => $chemin,
                    'text' => 'Acceuil',
                ],
                [
                    'href' => $chemin . '/api',
                    'text' => 'Api',
                ],
            ];

            $response->getBody()->write($template->render(['breadcrumb' => $breadcrumb, 'chemin' => $chemin]));

            return $response;
        });

        $app->get('/api/docs', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
            $specPath = __DIR__ . '/../../public/openapi.yaml';
            if (!is_file($specPath)) {
                $response->getBody()->write('OpenAPI spec not generated yet. Run: composer openapi');

                return $response->withStatus(404);
            }

            $response->getBody()->write((string) file_get_contents($specPath));

            return $response->withHeader('Content-Type', 'application/yaml');
        });

        $app->group('/api', function (RouteCollectorProxy $group) use ($twig, $menu, $chemin, $cat): void {
            $group->get('/annonce/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
                $annonceId = (int) $args['id'];
                $fields = ['id_annonce', 'id_categorie as categorie', 'id_annonceur as annonceur', 'id_departement as departement', 'prix', 'date', 'titre', 'description', 'ville'];
                $annonce = Annonce::select($fields)->find($annonceId);

                if ($annonce === null) {
                    return $response->withStatus(404);
                }

                $annonce->categorie = Categorie::find($annonce->categorie);
                $annonce->annonceur = Annonceur::select('email', 'nom_annonceur', 'telephone')->find($annonce->annonceur);
                $annonce->departement = Departement::select('id_departement', 'nom_departement')->find($annonce->departement);
                $annonce->links = ['self' => ['href' => '/api/annonce/' . $annonce->id_annonce]];

                return self::json($response, $annonce);
            });

            $group->get('/annonces', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
                $fields = ['id_annonce', 'prix', 'titre', 'ville'];
                $annonces = Annonce::all($fields);

                foreach ($annonces as $annonce) {
                    $annonce->links = ['self' => ['href' => '/api/annonce/' . $annonce->id_annonce]];
                }

                return self::json($response, $annonces);
            });

            $group->get('/categorie/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
                $categoryId = (int) $args['id'];
                $annonces = Annonce::select('id_annonce', 'prix', 'titre', 'ville')
                    ->where('id_categorie', '=', $categoryId)
                    ->get();

                foreach ($annonces as $annonce) {
                    $annonce->links = ['self' => ['href' => '/api/annonce/' . $annonce->id_annonce]];
                }

                $category = Categorie::find($categoryId);
                if ($category === null) {
                    return $response->withStatus(404);
                }

                $category->links = ['self' => ['href' => '/api/categorie/' . $categoryId]];
                $category->annonces = $annonces;

                return self::json($response, $category);
            });

            $group->get('/categories', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
                $categories = Categorie::get();
                foreach ($categories as $category) {
                    $category->links = ['self' => ['href' => '/api/categorie/' . $category->id_categorie]];
                }

                return self::json($response, $categories);
            });

            $group->get('/key', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $menu, $chemin, $cat): ResponseInterface {
                $controller = new KeyGenerator();
                $controller->show($twig, $menu, $chemin, $cat->getCategories());

                return $response;
            });

            $group->post('/key', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $menu, $chemin, $cat): ResponseInterface {
                $input = (array) $request->getParsedBody();
                $controller = new KeyGenerator();
                $controller->generateKey($twig, $menu, $chemin, $cat->getCategories(), (string) ($input['nom'] ?? ''));

                return $response;
            });
        });
    }

    private static function json(ResponseInterface $response, mixed $payload, int $status = 200): ResponseInterface
    {
        $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            $encoded = json_encode(['error' => 'Unable to encode JSON response'], JSON_UNESCAPED_SLASHES);
            $status = 500;
        }

        $response->getBody()->write((string) $encoded);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
