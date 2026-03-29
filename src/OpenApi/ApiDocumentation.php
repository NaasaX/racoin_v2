<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Racoin API',
    description: 'API REST pour les annonces Racoin.'
)]
#[OA\Server(url: '/api', description: 'Base API path')]
#[OA\Tag(name: 'Annonces', description: 'Consultation des annonces')]
#[OA\Tag(name: 'Categories', description: 'Consultation des categories')]
final class ApiDocumentation
{
    #[OA\Get(
        path: '/annonces',
        operationId: 'listAnnonces',
        summary: 'Lister les annonces',
        tags: ['Annonces'],
        responses: [
            new OA\Response(response: 200, description: 'Liste des annonces')
        ]
    )]
    public function listAnnonces(): void
    {
    }

    #[OA\Get(
        path: '/annonce/{id}',
        operationId: 'getAnnonce',
        summary: 'Recuperer une annonce par son identifiant',
        tags: ['Annonces'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Annonce trouvee'),
            new OA\Response(response: 404, description: 'Annonce introuvable'),
        ]
    )]
    public function getAnnonce(): void
    {
    }

    #[OA\Get(
        path: '/categories',
        operationId: 'listCategories',
        summary: 'Lister les categories',
        tags: ['Categories'],
        responses: [
            new OA\Response(response: 200, description: 'Liste des categories')
        ]
    )]
    public function listCategories(): void
    {
    }

    #[OA\Get(
        path: '/categorie/{id}',
        operationId: 'getCategorie',
        summary: 'Recuperer une categorie avec ses annonces',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Categorie trouvee'),
            new OA\Response(response: 404, description: 'Categorie introuvable'),
        ]
    )]
    public function getCategorie(): void
    {
    }
}
