<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repository\{ConstantRepository, FunctionRepository};

class SearchController
{
    private ConstantRepository $constantRepo;
    private FunctionRepository $functionRepo;

    public function __construct(
        ConstantRepository $constantRepo,
        FunctionRepository $functionRepo
    ) {
        $this->constantRepo = $constantRepo;
        $this->functionRepo = $functionRepo;
    }

    public function search(Request $request, Response $response, array $args): Response
    {
        $query = $args['query'] ?? '';

        if (empty($query)) {
            $response->getBody()->write('');
            return $response;
        }

        $functionResults = $this->functionRepo->search($query);
        $constantResults = $this->constantRepo->search($query);

        $results = array_merge($functionResults, $constantResults);

        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($results));
        return $response;
    }
}
