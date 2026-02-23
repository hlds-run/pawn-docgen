<?php

declare(strict_types=1);

namespace App\Service;

class PageContextBuilder
{
    private string $baseUrl;
    private string $project;
    private string $ogSecret;
    private int $ogSymbols;

    public function __construct(string $baseUrl, string $project, string $ogSecret, int $ogSymbols)
    {
        $this->baseUrl = $baseUrl;
        $this->project = $project;
        $this->ogSecret = $ogSecret;
        $this->ogSymbols = $ogSymbols;
    }

    public function generateOgSignature(string $title): string
    {
        return substr(hash_hmac('sha256', $title, $this->ogSecret), 0, $this->ogSymbols);
    }

    public function build(array $data): array
    {
        $currentOpenFile = $data['current_open_file'] ?? false;
        $currentOpenFunction = $data['current_open_function'] ?? false;
        $pageFunction = $data['page_function'] ?? null;
        $isRawView = $data['is_raw_view'] ?? false;
        $pageFunctions = $data['page_functions'] ?? null;
        $pageFile = $data['page_file'] ?? null;
        $includes = $data['includes'] ?? [];
        $functions = $data['functions'] ?? [];
        $results = $data['results'] ?? null;
        $pageName = $data['page_name'] ?? null;

        $scheme = 'https';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $currentPageUrl = $scheme . '://' . $host . $requestUri;
        $ogRequestUri = $scheme . '://' . $host . '/og';

        $ogParams = [
            'title' => $this->project,
            'subtitle' => '',
            'tag' => 'Pawn API',
            'theme' => 'dark',
        ];

        if (!empty($pageFunction)) {
            $ogParams['title'] = $pageFunction['Function'];
            $ogParams['subtitle'] = $pageFunction['Comment'];
            $ogParams['tag'] = $currentOpenFile . '.inc';
        } elseif (!empty($currentOpenFile)) {
            $ogParams['title'] = $currentOpenFile . '.inc';
            $ogParams['tag'] = $this->project;
        }

        ksort($ogParams);
        $ogQueryString = http_build_query($ogParams);
        $signedOgImageUrl = $ogRequestUri . '?' . $ogQueryString . '&s=' . $this->generateOgSignature($ogParams['title']);

        $metaDescription = $this->buildMetaDescription($pageFunction, $isRawView, $pageFunctions, $currentOpenFile);
        if (empty($metaDescription)) {
            $metaDescription = $this->project . ' Scripting API Reference - Browse functions, constants and symbols';
        }

        $title = $this->buildTitle($pageFunction, $isRawView, $pageFunctions, $currentOpenFile);
        $ogType = !empty($pageFunction) || !empty($isRawView) ? 'article' : 'website';

        $breadcrumbItems = $this->buildBreadcrumbs($currentOpenFile, $pageFunction);

        $articleSchema = $this->buildArticleSchema($pageFunction, $currentOpenFile);

        return [
            'base_url' => $this->baseUrl,
            'project' => $this->project,
            'current_open_file' => $currentOpenFile,
            'current_open_function' => $currentOpenFunction,
            'includes' => $includes ? array_values($includes) : [],
            'functions' => $functions,
            'meta_description' => $metaDescription,
            'title' => $title,
            'og_type' => $ogType,
            'current_page_url' => $currentPageUrl ?: $this->baseUrl,
            'signed_og_image_url' => $signedOgImageUrl,
            'breadcrumb_schema' => json_encode($breadcrumbItems, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'article_schema' => $articleSchema,
            'render_layout' => true,
            'page_function' => $pageFunction,
            'page_file' => $pageFile,
            'page_functions' => $pageFunctions,
            'page_name' => $pageName,
            'results' => $results,
        ];
    }

    private function buildMetaDescription(?array $pageFunction, bool $isRawView, ?array $pageFunctions, mixed $currentOpenFile): string
    {
        if (!empty($pageFunction)) {
            return $this->truncateDescription($pageFunction['Comment']);
        }
        if (!empty($isRawView)) {
            return 'Source code of ' . $currentOpenFile . '.inc file';
        }
        if (!empty($pageFunctions)) {
            return 'List of functions in ' . $currentOpenFile . '.inc file';
        }
        if (!empty($currentOpenFile)) {
            return 'Constants and symbols from ' . $currentOpenFile . '.inc file';
        }
        return '';
    }

    private function buildTitle(?array $pageFunction, bool $isRawView, ?array $pageFunctions, mixed $currentOpenFile): string
    {
        if (!empty($pageFunction)) {
            return $pageFunction['Function'] . ' | Functions | ' . $currentOpenFile . ' | ' . $this->project;
        }
        if (!empty($isRawView)) {
            return 'File content | ' . $currentOpenFile . ' | ' . $this->project;
        }
        if (!empty($pageFunctions)) {
            return 'Functions | ' . $currentOpenFile . ' | ' . $this->project;
        }
        if (!empty($currentOpenFile)) {
            return 'Constants | ' . $currentOpenFile . ' | ' . $this->project;
        }
        return $this->project . ' Scripting API Reference';
    }

    private function buildBreadcrumbs(mixed $currentOpenFile, ?array $pageFunction): array
    {
        $items = [];
        $items[] = [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => $this->project,
            'item' => $this->baseUrl,
        ];

        if (!empty($currentOpenFile)) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $currentOpenFile . '.inc',
                'item' => $this->baseUrl . $currentOpenFile,
            ];
        }

        if (!empty($pageFunction)) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => 'Functions',
                'item' => $this->baseUrl . $currentOpenFile . '/__functions',
            ];
            $items[] = [
                '@type' => 'ListItem',
                'position' => 4,
                'name' => $pageFunction['Function'],
                'item' => $this->baseUrl . $currentOpenFile . '/' . $pageFunction['Function'],
            ];
        } elseif (!empty($currentOpenFile) && isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], '__functions') !== false) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => 'Functions',
                'item' => $this->baseUrl . $currentOpenFile . '/__functions',
            ];
        } elseif (!empty($currentOpenFile)) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => 'Constants',
                'item' => $this->baseUrl . $currentOpenFile,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    private function buildArticleSchema(?array $pageFunction, mixed $currentOpenFile): string
    {
        if (empty($pageFunction)) {
            return '';
        }

        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'TechArticle',
            'headline' => $pageFunction['Function'],
            'description' => $this->truncateDescription($pageFunction['Comment']),
            'url' => $this->baseUrl . $currentOpenFile . '/' . $pageFunction['Function'],
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $this->project . ' Scripting API Reference',
                'url' => $this->baseUrl,
            ],
            'author' => [
                '@type' => 'Organization',
                'name' => 'AlliedModders',
            ],
            'datePublished' => date('Y-m-d'),
            'proficiencyLevel' => 'Intermediate',
            'keywords' => implode(', ', [$pageFunction['Function'], 'scripting', 'API', $currentOpenFile]),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function truncateDescription(?string $text, int $maxLength = 160): string
    {
        if (empty($text)) {
            return '';
        }

        $text = trim(str_replace(["\n", "\r", "\t"], ' ', $text));

        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength);
            $lastSpace = strrpos($text, ' ');
            if ($lastSpace !== false) {
                $text = substr($text, 0, $lastSpace);
            }
            $text .= '...';
        }

        return $text;
    }
}
