<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use App\Repository\{FileRepository, FunctionRepository};
use App\Service\PageContextBuilder;

class SystemController
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

    public function robots(Request $request, Response $response): Response
    {
        $scheme = 'https';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $sitemapUrl = $scheme . '://' . $host . '/sitemap.xml';

        $body = $this->twig->render('robots.txt.html.twig', ['sitemap_url' => $sitemapUrl]);
        $response = $response->withHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->getBody()->write($body);
        return $response;
    }

    public function sitemap(Request $request, Response $response): Response
    {
        $scheme = 'https';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $baseUrlFull = $scheme . '://' . $host . '/';

        $files = $this->fileRepo->getAll();
        $functionsByFile = $this->functionRepo->getForSitemap();

        $body = $this->twig->render('sitemap.xml.html.twig', [
            'base_url_full' => $baseUrlFull,
            'files' => $files,
            'functions_by_file' => $functionsByFile,
        ]);

        $response = $response->withHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->getBody()->write($body);
        return $response;
    }
}
