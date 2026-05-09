<?php

function getTruncatedDescription($text, $maxLength = 160): string
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

function renderPage($twig, $template, $context): void
{
    if (!$context['render_layout']) {
        echo $twig->render($template, $context);
        return;
    }

    $headerContext = array_merge($context, [
        'page_function' => $context['page_function'] ?? null,
    ]);
    echo $twig->render('header.html.twig', $headerContext);
    echo $twig->render($template, $context);
    echo $twig->render('footer.html.twig', $context);
}

function buildPageContext(
    string $BaseURL,
    string $Project,
    $CurrentOpenFile,
    $CurrentOpenFunction,
    $PageFunction,
    bool $IsRawView,
    $PageFunctions,
    $PageFile,
    $Includes,
    $Functions,
    array $OG_Params,
    string $OG_Signature
): array {
    $Scheme = 'https';
    $Host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
    $RequestURI = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $CurrentPageURL = $Scheme . '://' . $Host . $RequestURI;
    $OGRequestURI = $Scheme . '://' . $Host . '/og';

    $OG_Params['title'] = $OG_Params['title'] ?? $Project;
    $OG_Params['tag'] = $OG_Params['tag'] ?? 'Pawn API';
    ksort($OG_Params);
    $OG_QueryString = http_build_query($OG_Params);
    $SignedOGImageUrl = $OGRequestURI . '?' . $OG_QueryString . '&s=' . $OG_Signature;

    $MetaDescription = '';

    if (!empty($PageFunction)) {
        $MetaDescription = getTruncatedDescription($PageFunction['Comment']);
    } elseif (!empty($IsRawView)) {
        $MetaDescription = 'Source code of ' . $CurrentOpenFile . '.inc file';
    } elseif (!empty($PageFunctions)) {
        $MetaDescription = 'List of functions in ' . $CurrentOpenFile . '.inc file';
    } elseif (!empty($CurrentOpenFile)) {
        $MetaDescription = 'Constants and symbols from ' . $CurrentOpenFile . '.inc file';
    }

    if (empty($MetaDescription)) {
        $MetaDescription = $Project . ' Scripting API Reference - Browse functions, constants and symbols';
    }

    $OGType = 'website';
    $Title = $Project . ' Scripting API Reference';

    if (!empty($PageFunction)) {
        $Title = $PageFunction['Function'] . ' | Functions | ' . $CurrentOpenFile . ' | ' . $Project;
        $OGType = 'article';
    } elseif (!empty($IsRawView)) {
        $Title = 'File content | ' . $CurrentOpenFile . ' | ' . $Project;
        $OGType = 'article';
    } elseif (!empty($PageFunctions)) {
        $Title = 'Functions | ' . $CurrentOpenFile . ' | ' . $Project;
        $OGType = 'website';
    } elseif (!empty($CurrentOpenFile)) {
        $Title = 'Constants | ' . $CurrentOpenFile . ' | ' . $Project;
    }

    $breadcrumbItems = [];
    $position = 1;
    $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => $Project, 'item' => $BaseURL];

    if (!empty($CurrentOpenFile)) {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => $CurrentOpenFile . '.inc', 'item' => $BaseURL . $CurrentOpenFile];
    }

    if (!empty($PageFunction)) {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => 'Functions', 'item' => $BaseURL . $CurrentOpenFile . '/__functions'];
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => $PageFunction['Function'], 'item' => $BaseURL . $CurrentOpenFile . '/' . $PageFunction['Function']];
    } elseif (!empty($CurrentOpenFile) && isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], '__functions') !== false) {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => 'Functions', 'item' => $BaseURL . $CurrentOpenFile . '/__functions'];
    } elseif (!empty($CurrentOpenFile)) {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => 'Constants', 'item' => $BaseURL . $CurrentOpenFile];
    }

    $breadcrumbSchema = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbItems,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    $articleSchema = '';
    if (!empty($PageFunction)) {
        $articleSchema = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'TechArticle',
            'headline' => $PageFunction['Function'],
            'description' => getTruncatedDescription($PageFunction['Comment'], 160),
            'url' => $BaseURL . $CurrentOpenFile . '/' . $PageFunction['Function'],
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $Project . ' Scripting API Reference',
                'url' => $BaseURL
            ],
            'author' => [
                '@type' => 'Organization',
                'name' => 'AlliedModders'
            ],
            'datePublished' => date('Y-m-d'),
            'proficiencyLevel' => 'Intermediate',
            'keywords' => implode(', ', [$PageFunction['Function'], 'scripting', 'API', $CurrentOpenFile])
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    return [
        'base_url' => $BaseURL,
        'project' => $Project,
        'yandex_metrika_id' => getenv('YANDEX_METRIKA_ID') ?? null,
        'current_open_file' => $CurrentOpenFile,
        'current_open_function' => $CurrentOpenFunction,
        'includes' => $Includes ? array_values($Includes) : [],
        'functions' => $Functions,
        'meta_description' => $MetaDescription,
        'title' => $Title,
        'og_type' => $OGType,
        'current_page_url' => $CurrentPageURL ?? $BaseURL,
        'signed_og_image_url' => $SignedOGImageUrl,
        'breadcrumb_schema' => $breadcrumbSchema,
        'article_schema' => $articleSchema,
        'render_layout' => true,
        'page_function' => $PageFunction ?? null,
        'page_file' => $PageFile ?? null,
        'page_functions' => $PageFunctions ?? null,
        'page_name' => $PageName ?? null,
    ];
}
