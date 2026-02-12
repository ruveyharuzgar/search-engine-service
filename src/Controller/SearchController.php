<?php

namespace App\Controller;

use App\DTO\SearchRequestDTO;
use App\Service\SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api')]
class SearchController extends AbstractController
{
    public function __construct(
        private SearchService $searchService
    ) {}

    #[Route('/search', name: 'api_search', methods: ['GET'])]
    #[OA\Get(
        path: '/api/search',
        summary: 'Content search',
        tags: ['Search']
    )]
    #[OA\Parameter(
        name: 'keyword',
        in: 'query',
        description: 'Search keyword',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'type',
        in: 'query',
        description: 'Content type (video, article)',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['video', 'article'])
    )]
    #[OA\Parameter(
        name: 'sortBy',
        in: 'query',
        description: 'Sort criteria',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['score', 'date'], default: 'score')
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Page number',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        in: 'query',
        description: 'Results per page',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 10)
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                new OA\Property(property: 'pagination', type: 'object')
            ]
        )
    )]
    public function search(Request $request): JsonResponse
    {
        try {
            // Primary parameter is 'keyword', but support 'query' for backward compatibility
            $keyword = $request->query->get('keyword') ?? $request->query->get('query');
            
            $searchRequest = new SearchRequestDTO(
                keyword: $keyword,
                type: $request->query->get('type'),
                sortBy: $request->query->get('sortBy') ?? $request->query->get('sort_by', 'score'),
                page: (int) $request->query->get('page', 1),
                perPage: (int) ($request->query->get('perPage') ?? $request->query->get('per_page', 10))
            );

            $result = $this->searchService->search($searchRequest);

            return $this->json([
                'success' => true,
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/sync', name: 'api_sync', methods: ['POST'])]
    #[OA\Post(
        path: '/api/sync',
        summary: 'Provider\'lardan verileri senkronize et',
        tags: ['Yönetim']
    )]
    #[OA\Response(
        response: 200,
        description: 'Başarılı',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'synced_count', type: 'integer')
            ]
        )
    )]
    public function sync(): JsonResponse
    {
        try {
            $count = $this->searchService->syncContents();

            return $this->json([
                'success' => true,
                'message' => 'Veriler başarıyla senkronize edildi',
                'synced_count' => $count
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
