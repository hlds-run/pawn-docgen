<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use App\Repository\{FileRepository, FunctionRepository, ConstantRepository};
use App\Service\PageContextBuilder;

class FileController
{
    private Environment $twig;
    private FileRepository $fileRepo;
    private FunctionRepository $functionRepo;
    private ConstantRepository $constantRepo;
    private PageContextBuilder $contextBuilder;

    public function __construct(
        Environment $twig,
        FileRepository $fileRepo,
        FunctionRepository $functionRepo,
        ConstantRepository $constantRepo,
        PageContextBuilder $contextBuilder
    ) {
        $this->twig = $twig;
        $this->fileRepo = $fileRepo;
        $this->functionRepo = $functionRepo;
        $this->constantRepo = $constantRepo;
        $this->contextBuilder = $contextBuilder;
    }

    public function view(Request $request, Response $response, array $args): Response
    {
        $includeName = htmlspecialchars($args['file'] ?? '', ENT_QUOTES, 'UTF-8');
        $currentOpenFile = $includeName;

        $constants = $this->constantRepo->getByFile($includeName);

        if (!empty($constants)) {
            $includes = $this->fileRepo->getAll();
            $functions = $this->functionRepo->getAll();

            $context = $this->contextBuilder->build([
                'current_open_file' => $currentOpenFile,
                'includes' => $includes,
                'functions' => $functions,
                'results' => $constants,
                'page_name' => $includeName,
            ]);

            $body = $this->render('constants.html.twig', $context);
            $response->getBody()->write($body);
            return $response;
        }

        $functions = $this->functionRepo->getByFile($includeName);

        if (!empty($functions)) {
            $includes = $this->fileRepo->getAll();
            $allFunctions = $this->functionRepo->getAll();

            $context = $this->contextBuilder->build([
                'current_open_file' => $currentOpenFile,
                'includes' => $includes,
                'functions' => $allFunctions,
                'page_functions' => $functions,
            ]);

            $body = $this->render('functions.html.twig', $context);
            $response->getBody()->write($body);
            return $response;
        }

        $content = $this->fileRepo->getContentByName($includeName);

        if ($content === null) {
            return $this->notFound($request, $response);
        }

        $includes = $this->fileRepo->getAll();
        $allFunctions = $this->functionRepo->getAll();

        $context = $this->contextBuilder->build([
            'current_open_file' => $currentOpenFile,
            'includes' => $includes,
            'functions' => $allFunctions,
            'is_raw_view' => true,
            'page_file' => ['Content' => $content],
        ]);

        $body = $this->render('raw.html.twig', $context);
        $response->getBody()->write($body);
        return $response;
    }

    public function raw(Request $request, Response $response, array $args): Response
    {
        $includeName = htmlspecialchars($args['file'] ?? '', ENT_QUOTES, 'UTF-8');
        $currentOpenFile = $includeName;

        $file = $this->fileRepo->getByName($includeName);

        if ($file === null) {
            return $this->notFound($request, $response);
        }

        $includes = $this->fileRepo->getAll();
        $functions = $this->functionRepo->getAll();

        $context = $this->contextBuilder->build([
            'current_open_file' => $currentOpenFile,
            'includes' => $includes,
            'functions' => $functions,
            'is_raw_view' => true,
            'page_file' => $file,
        ]);

        $body = $this->render('raw.html.twig', $context);
        $response->getBody()->write($body);
        return $response;
    }

    public function functions(Request $request, Response $response, array $args): Response
    {
        $includeName = htmlspecialchars($args['file'] ?? '', ENT_QUOTES, 'UTF-8');
        $currentOpenFile = $includeName;

        $functions = $this->functionRepo->getByFile($includeName);

        if (empty($functions)) {
            return $response->withHeader('Location', '/' . $includeName)->withStatus(302);
        }

        $includes = $this->fileRepo->getAll();
        $allFunctions = $this->functionRepo->getAll();

        $context = $this->contextBuilder->build([
            'current_open_file' => $currentOpenFile,
            'includes' => $includes,
            'functions' => $allFunctions,
            'page_functions' => $functions,
        ]);

        $body = $this->render('functions.html.twig', $context);
        $response->getBody()->write($body);
        return $response;
    }

    public function function(Request $request, Response $response, array $args): Response
    {
        $includeName = htmlspecialchars($args['file'] ?? '', ENT_QUOTES, 'UTF-8');
        $functionName = htmlspecialchars($args['function'] ?? '', ENT_QUOTES, 'UTF-8');
        $currentOpenFile = $includeName;

        $function = $this->functionRepo->getByNameAndFile($functionName, $includeName);

        if ($function === null) {
            return $this->notFound($request, $response);
        }

        $tags = json_decode($function['Tags'] ?? '[]', true) ?? [];
        $parameters = [];
        $otherTags = [];

        foreach ($tags as $tag) {
            if ($tag['Tag'] === 'param') {
                $parameters[] = $tag;
            } else {
                $otherTags[] = $tag;
            }
        }

        $includes = $this->fileRepo->getAll();
        $allFunctions = $this->functionRepo->getAll();

        $context = $this->contextBuilder->build([
            'current_open_file' => $currentOpenFile,
            'current_open_function' => $function['Function'],
            'includes' => $includes,
            'functions' => $allFunctions,
            'page_function' => $function,
        ]);

        $context['parameters'] = $parameters;
        $context['other_tags'] = $otherTags;

        $body = $this->render('function.html.twig', $context);
        $response->getBody()->write($body);
        return $response;
    }

    private function notFound(Request $request, Response $response): Response
    {
        $includes = $this->fileRepo->getAll();
        $functions = $this->functionRepo->getAll();

        $context = $this->contextBuilder->build([
            'includes' => $includes,
            'functions' => $functions,
        ]);

        $body = $this->render('404.html.twig', $context);
        $response->getBody()->write($body);
        return $response->withStatus(404);
    }

    private function render(string $template, array $context): string
    {
        return $this->twig->render('header.html.twig', $context)
            . $this->twig->render($template, $context)
            . $this->twig->render('footer.html.twig', $context);
    }
}
