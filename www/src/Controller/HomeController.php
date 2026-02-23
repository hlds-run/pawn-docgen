<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use App\Repository\{FileRepository, FunctionRepository, ConstantRepository};
use App\Service\PageContextBuilder;

class HomeController
{
    private Environment $twig;
    private FileRepository $fileRepo;
    private FunctionRepository $functionRepo;
    private PageContextBuilder $contextBuilder;

    public function __construct(
        Environment $twig,
        FileRepository $fileRepo,
        FunctionRepository $functionRepo,
        PageContextBuilder $contextBuilder
    ) {
        $this->twig = $twig;
        $this->fileRepo = $fileRepo;
        $this->functionRepo = $functionRepo;
        $this->contextBuilder = $contextBuilder;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $includes = $this->fileRepo->getAll();
        $functions = $this->functionRepo->getAll();

        $context = $this->contextBuilder->build([
            'includes' => $includes,
            'functions' => $functions,
        ]);

        $body = $this->twig->render('header.html.twig', $context)
            . $this->twig->render('main.html.twig', $context)
            . $this->twig->render('footer.html.twig', $context);
        
        $response->getBody()->write($body);
        return $response;
    }
}
