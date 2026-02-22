<?php
require __DIR__ . '/vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader, [
    'cache' => false,
    'autoescape' => false,
]);

$twig->addFilter(new TwigFilter('string_to_slug', function ($string) {
    $string = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
    $string = trim($string, "- \t\n\r\0\x0B");
    return strtolower($string);
}));

$twig->addFilter(new TwigFilter('group_by_tag', function ($tags) {
    $grouped = [];
    foreach ($tags as $tag) {
        $grouped[$tag['Tag']][] = $tag;
    }
    return $grouped;
}));

$twig->addFilter(new TwigFilter('json_decode', function ($json) {
    return json_decode($json, true);
}));

$twig->addFunction(new Twig\TwigFunction('get_function_header', function ($Type) {
    switch ($Type) {
        case 'forward': return '<div class="card border-info mb-2"><div class="card-header bg-info text-white">Forwards</div>';
        case 'native': return '<div class="card border-success mb-2"><div class="card-header bg-success text-white">Natives</div>';
        case 'stock': return '<div class="card border-warning mb-2"><div class="card-header bg-warning text-dark">Stocks</div>';
        case 'functag': return '<div class="card border-danger mb-2"><div class="card-header bg-danger text-white">Functags</div>';
    }
    return '<div class="card border-primary mb-2"><div class="card-header bg-primary text-white">' . $Type . '</div>';
}));

function getTruncatedDescription($text, $maxLength = 160) {
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
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function getFunctionHeader($Type) {
    switch ($Type) {
        case 'forward': return '<div class="card border-info mb-2"><div class="card-header bg-info text-white">Forwards</div>';
        case 'native': return '<div class="card border-success mb-2"><div class="card-header bg-success text-white">Natives</div>';
        case 'stock': return '<div class="card border-warning mb-2"><div class="card-header bg-warning text-dark">Stocks</div>';
        case 'functag': return '<div class="card border-danger mb-2"><div class="card-header bg-danger text-white">Functags</div>';
    }
    return '<div class="card border-primary mb-2"><div class="card-header bg-primary text-white">' . $Type . '</div>';
}

require __DIR__ . '/../settings.php';

$Path = isset($_SERVER['QUERY_STRING']) ? trim($_SERVER['QUERY_STRING'], '/') : '';

if ($Path === 'robots.txt') {
    header('Content-Type: text/plain; charset=UTF-8');
    $Scheme = 'https';
    $Host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
    $SitemapURL = $Scheme . '://' . $Host . rtrim($BaseURL, '/') . '/sitemap.xml';
    echo $twig->render('robots.txt.html.twig', ['sitemap_url' => $SitemapURL]);
    exit;
}

if ($Path === 'sitemap.xml') {
    header('Content-Type: application/xml; charset=UTF-8');
    $Scheme = 'https';
    $Host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
    $BaseURLFull = $Scheme . '://' . $Host . rtrim($BaseURL, '/') . '/';

    $Files = $Database->query('SELECT `ID`, `IncludeName` FROM `' . $Columns['Files'] . '` ORDER BY `IncludeName` ASC')->fetchAll(PDO::FETCH_KEY_PAIR);

    $FunctionsByFile = [];
    $STH = $Database->query('SELECT `Function`, `IncludeName` FROM `' . $Columns['Functions'] . '` ORDER BY `IncludeName` ASC, `Function` ASC');
    while ($Function = $STH->fetch()) {
        $FunctionsByFile[$Function['IncludeName']][] = $Function;
    }

    echo $twig->render('sitemap.xml.html.twig', [
        'base_url_full' => $BaseURLFull,
        'files' => $Files,
        'functions_by_file' => $FunctionsByFile,
    ]);
    exit;
}

$RenderLayout = !isset($_SERVER['HTTP_X_PJAX']) || $_SERVER['HTTP_X_PJAX'] !== 'true';

if (substr($Path, 0, 8) === '__search') {
    $RenderLayout = false;
}

if ($RenderLayout) {
    $CurrentOpenFile = false;
    $CurrentOpenFunction = false;

    $Includes = $Database->query('SELECT `ID`, `IncludeName` FROM `' . $Columns['Files'] . '` ORDER BY `IncludeName` ASC')->fetchAll(PDO::FETCH_KEY_PAIR);

    $Functions = [];

    $STH = $Database->query('SELECT `Function`, `Type`, `Comment`, `IncludeName` FROM `' . $Columns['Functions'] . '` ORDER BY `Type` ASC, `Function` ASC');

    while ($Function = $STH->fetch()) {
        $Functions[$Function['IncludeName']][] = [
            'Function' => $Function['Function'],
            'Comment' => $Function['Comment'],
            'Type' => $Function['Type'],
        ];
    }
}

function buildPageContext($BaseURL, $Project, $CurrentOpenFile, $CurrentOpenFunction, $PageFunction, $IsRawView, $PageFunctions, $PageFile, $Includes, $Functions, $OG_Params, $OG_Signature) {
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
        $MetaDescription = 'Source code of ' . htmlspecialchars($CurrentOpenFile) . '.inc file';
    } elseif (!empty($PageFunctions)) {
        $MetaDescription = 'List of functions in ' . htmlspecialchars($CurrentOpenFile) . '.inc file';
    } elseif (!empty($CurrentOpenFile)) {
        $MetaDescription = 'Constants and symbols from ' . htmlspecialchars($CurrentOpenFile) . '.inc file';
    }

    if (empty($MetaDescription)) {
        $MetaDescription = htmlspecialchars($Project . ' Scripting API Reference - Browse functions, constants and symbols');
    }

    $OGType = 'website';
    $Title = $Project . ' Scripting API Reference';

    if (!empty($PageFunction)) {
        $Title = htmlspecialchars($PageFunction['Function']) . ' | Functions | ' . htmlspecialchars($CurrentOpenFile) . ' | ' . $Project;
        $OGType = 'article';
    } elseif (!empty($IsRawView)) {
        $Title = 'File content | ' . htmlspecialchars($CurrentOpenFile) . ' | ' . $Project;
        $OGType = 'article';
    } elseif (!empty($PageFunctions)) {
        $Title = 'Functions | ' . htmlspecialchars($CurrentOpenFile) . ' | ' . $Project;
        $OGType = 'website';
    } elseif (!empty($CurrentOpenFile)) {
        $Title = 'Constants | ' . htmlspecialchars($CurrentOpenFile) . ' | ' . $Project;
    }

    $breadcrumbItems = [];
    $position = 1;
    $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => $Project, 'item' => $BaseURL];

    if (!empty($CurrentOpenFile)) {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => $CurrentOpenFile . '.inc', 'item' => $BaseURL . $CurrentOpenFile];
    }

    if (!empty($PageFunction)) {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => 'Functions', 'item' => $BaseURL . $CurrentOpenFile . '/__functions'];
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => $PageFunction['Function'], 'item' => $BaseURL . $CurrentOpenFile . '/' . htmlspecialchars($PageFunction['Function'])];
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
            'url' => $BaseURL . $CurrentOpenFile . '/' . htmlspecialchars($PageFunction['Function']),
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
        'current_open_file' => $CurrentOpenFile,
        'current_open_function' => $CurrentOpenFunction,
        'includes' => $Includes ? array_values($Includes) : [],
        'functions' => $Functions,
        'meta_description' => $MetaDescription,
        'title' => $Title,
        'og_type' => $OGType,
        'current_page_url' => htmlspecialchars($CurrentPageURL ?? $BaseURL),
        'signed_og_image_url' => htmlspecialchars($SignedOGImageUrl),
        'breadcrumb_schema' => $breadcrumbSchema,
        'article_schema' => $articleSchema,
        'render_layout' => true,
        'page_function' => $PageFunction ?? null,
        'page_file' => $PageFile ?? null,
        'page_functions' => $PageFunctions ?? null,
        'page_name' => $PageName ?? null,
    ];
}

function renderPage($twig, $template, $context) {
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

$OG_HMAC_SECRET = getenv('OG_HMAC_SECRET') ?: 'default_secret';
$CHECK_HMAC_SYMBOLS = (int)(getenv('CHECK_HMAC_SYMBOLS') ?: 8);
$OG_Params = [
    'title' => $Project,
    'subtitle' => '',
    'tag' => 'Pawn API',
    'theme' => 'dark'
];
$FullSignature = hash_hmac('sha256', $OG_Params['title'], $OG_HMAC_SECRET);
$OG_Signature = substr($FullSignature, 0, $CHECK_HMAC_SYMBOLS);

if ($Path) {
    $Path = explode('/', $Path, 2);

    $Action = !empty($Path[1]) ? filter_var($Path[1], FILTER_SANITIZE_STRING) : false;

    if (isset($Path[0])) {
        $IncludeName = filter_var($Path[0], FILTER_SANITIZE_STRING);

        if ($IncludeName === '__search') {
            if (empty($Action)) {
                exit;
            }

            $Action = '%' . Str_Replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $Action) . '%';

            $STH = $Database->prepare('SELECT `IncludeName` as `includeName`, `Comment` as `value` FROM `' . $Columns['Constants'] . '` WHERE `Constant` LIKE ? OR `Comment` LIKE ?');
            $STH->execute([$Action, $Action]);

            $Results = $STH->fetchAll();

            echo json_encode($Results);

            exit;
        }

        $HeaderTitle = $CurrentOpenFile = $IncludeName;

        if ($Action) {
            if ($Action === '__raw') {
                $STH = $Database->prepare('SELECT `Content` FROM `' . $Columns['Files'] . '` WHERE `IncludeName` = :includeName');
                $STH->bindValue(':includeName', $IncludeName, PDO::PARAM_STR);
                $STH->execute();

                $PageFile = $STH->fetch();

                if (empty($PageFile)) {
                    $context = buildPageContext($BaseURL, $Project, $CurrentOpenFile, $CurrentOpenFunction, null, false, null, null, $Includes, $Functions, $OG_Params, $OG_Signature);
                    $context['render_layout'] = $RenderLayout;
                    renderPage($twig, '404.html.twig', $context);
                    exit;
                }

                $IsRawView = true;

                $OG_Params['title'] = $CurrentOpenFile . '.inc';
                $OG_Params['tag'] = $Project;
                $FullSignature = hash_hmac('sha256', $OG_Params['title'], $OG_HMAC_SECRET);
                $OG_Signature = substr($FullSignature, 0, $CHECK_HMAC_SYMBOLS);

                $context = buildPageContext($BaseURL, $Project, $CurrentOpenFile, $CurrentOpenFunction, null, $IsRawView, null, $PageFile, $Includes, $Functions, $OG_Params, $OG_Signature);
                $context['render_layout'] = $RenderLayout;
                renderPage($twig, 'raw.html.twig', $context);
            } else if ($Action === '__functions') {
                $NotGoto = true;

                $STH = $Database->prepare('SELECT `Function`, `Comment` FROM `' . $Columns['Functions'] . '` WHERE `IncludeName` = :includeName');
                $STH->bindValue(':includeName', $IncludeName, PDO::PARAM_STR);
                $STH->execute();

                $PageFunctions = $STH->fetchAll();

                if (empty($PageFunctions)) {
                    if (isset($NotGoto)) {
                        header('Location: ' . $BaseURL . $IncludeName);
                        exit;
                    } else {
                        $STH = $Database->prepare('SELECT `Content` FROM `' . $Columns['Files'] . '` WHERE `IncludeName` = :includeName');
                        $STH->bindValue(':includeName', $IncludeName, PDO::PARAM_STR);
                        $STH->execute();

                        $PageFile = $STH->fetch();

                        $IsRawView = true;

                        $OG_Params['title'] = $CurrentOpenFile . '.inc';
                        $OG_Params['tag'] = $Project;
                        $FullSignature = hash_hmac('sha256', $OG_Params['title'], $OG_HMAC_SECRET);
                        $OG_Signature = substr($FullSignature, 0, $CHECK_HMAC_SYMBOLS);

                        $context = buildPageContext($BaseURL, $Project, $CurrentOpenFile, $CurrentOpenFunction, null, $IsRawView, null, $PageFile, $Includes, $Functions, $OG_Params, $OG_Signature);
                        $context['render_layout'] = $RenderLayout;
                        renderPage($twig, 'raw.html.twig', $context);
                        exit;
                    }
                }

                $HeaderTitle = 'Functions · ' . $HeaderTitle;

                $OG_Params['title'] = $CurrentOpenFile . '.inc';
                $OG_Params['tag'] = $Project;
                $FullSignature = hash_hmac('sha256', $OG_Params['title'], $OG_HMAC_SECRET);
                $OG_Signature = substr($FullSignature, 0, $CHECK_HMAC_SYMBOLS);

                $context = buildPageContext($BaseURL, $Project, $CurrentOpenFile, $CurrentOpenFunction, null, false, $PageFunctions, null, $Includes, $Functions, $OG_Params, $OG_Signature);
                $context['render_layout'] = $RenderLayout;
                $context['page_functions'] = $PageFunctions;
                renderPage($twig, 'functions.html.twig', $context);
            } else {
                $STH = $Database->prepare('SELECT `Function`, `FullFunction`, `Type`, `Comment`, `Tags`, `IncludeName` FROM `' . $Columns['Functions'] . '` WHERE `Function` = :functionName AND `IncludeName` = :includeName');
                $STH->bindValue(':includeName', $IncludeName, PDO::PARAM_STR);
                $STH->bindValue(':functionName', $Action, PDO::PARAM_STR);
                $STH->execute();

                $PageFunction = $STH->fetch();

                if (empty($PageFunction)) {
                    $context = buildPageContext($BaseURL, $Project, $CurrentOpenFile, $CurrentOpenFunction, null, false, null, null, $Includes, $Functions, $OG_Params, $OG_Signature);
                    $context['render_layout'] = $RenderLayout;
                    renderPage($twig, '404.html.twig', $context);
                    exit;
                }

                $CurrentOpenFunction = $PageFunction['Function'];

                $HeaderTitle = $PageFunction['Function'] . ' · ' . $HeaderTitle;

                $Tags = json_decode($PageFunction['Tags'], true);

                $Parameters = [];
                $OtherTags = [];

                foreach ($Tags as $Tag) {
                    if ($Tag['Tag'] === 'param') {
                        $Parameters[] = $Tag;
                    } else {
                        $OtherTags[] = $Tag;
                    }
                }

                $OG_Params['title'] = $PageFunction['Function'];
                $OG_Params['subtitle'] = $PageFunction['Comment'];
                $OG_Params['tag'] = $CurrentOpenFile . '.inc';
                $FullSignature = hash_hmac('sha256', $OG_Params['title'], $OG_HMAC_SECRET);
                $OG_Signature = substr($FullSignature, 0, $CHECK_HMAC_SYMBOLS);

                $context = buildPageContext($BaseURL, $Project, $CurrentOpenFile, $CurrentOpenFunction, $PageFunction, false, null, null, $Includes, $Functions, $OG_Params, $OG_Signature);
                $context['render_layout'] = $RenderLayout;
                $context['page_function'] = $PageFunction;
                $context['parameters'] = $Parameters;
                $context['other_tags'] = $OtherTags;
                renderPage($twig, 'function.html.twig', $context);
            }
        } else {
            $STH = $Database->prepare('SELECT `Constant`, `Comment`, `Tags` FROM `' . $Columns['Constants'] . '` WHERE `IncludeName` = :includeName');
            $STH->bindValue(':includeName', $IncludeName, PDO::PARAM_STR);
            $STH->execute();

            $Results = $STH->fetchAll();

            if (empty($Results)) {
                $STH = $Database->prepare('SELECT `Function`, `Comment` FROM `' . $Columns['Functions'] . '` WHERE `IncludeName` = :includeName');
                $STH->bindValue(':includeName', $IncludeName, PDO::PARAM_STR);
                $STH->execute();

                $PageFunctions = $STH->fetchAll();

                if (empty($PageFunctions)) {
                    $STH = $Database->prepare('SELECT `Content` FROM `' . $Columns['Files'] . '` WHERE `IncludeName` = :includeName');
                    $STH->bindValue(':includeName', $IncludeName, PDO::PARAM_STR);
                    $STH->execute();

                    $PageFile = $STH->fetch();

                    $IsRawView = true;

                    $OG_Params['title'] = $CurrentOpenFile . '.inc';
                    $OG_Params['tag'] = $Project;
                    $FullSignature = hash_hmac('sha256', $OG_Params['title'], $OG_HMAC_SECRET);
                    $OG_Signature = substr($FullSignature, 0, $CHECK_HMAC_SYMBOLS);

                    $context = buildPageContext($BaseURL, $Project, $CurrentOpenFile, $CurrentOpenFunction, null, $IsRawView, null, $PageFile, $Includes, $Functions, $OG_Params, $OG_Signature);
                    $context['render_layout'] = $RenderLayout;
                    renderPage($twig, 'raw.html.twig', $context);
                    exit;
                }

                $OG_Params['title'] = $CurrentOpenFile . '.inc';
                $OG_Params['tag'] = $Project;
                $FullSignature = hash_hmac('sha256', $OG_Params['title'], $OG_HMAC_SECRET);
                $OG_Signature = substr($FullSignature, 0, $CHECK_HMAC_SYMBOLS);

                $context = buildPageContext($BaseURL, $Project, $CurrentOpenFile, $CurrentOpenFunction, null, false, $PageFunctions, null, $Includes, $Functions, $OG_Params, $OG_Signature);
                $context['render_layout'] = $RenderLayout;
                $context['page_functions'] = $PageFunctions;
                renderPage($twig, 'functions.html.twig', $context);
                exit;
            }

            $PageName = $IncludeName;

            $HeaderTitle = 'Constants · ' . $HeaderTitle;

            $OG_Params['title'] = $CurrentOpenFile . '.inc';
            $OG_Params['tag'] = $Project;
            $FullSignature = hash_hmac('sha256', $OG_Params['title'], $OG_HMAC_SECRET);
            $OG_Signature = substr($FullSignature, 0, $CHECK_HMAC_SYMBOLS);

            $context = buildPageContext($BaseURL, $Project, $CurrentOpenFile, $CurrentOpenFunction, null, false, null, null, $Includes, $Functions, $OG_Params, $OG_Signature);
            $context['render_layout'] = $RenderLayout;
            $context['results'] = $Results;
            $context['page_name'] = $PageName;
            renderPage($twig, 'constants.html.twig', $context);
        }
    } else {
        $context = buildPageContext($BaseURL, $Project, false, false, null, false, null, null, $Includes, $Functions, $OG_Params, $OG_Signature);
        $context['render_layout'] = $RenderLayout;
        renderPage($twig, '404.html.twig', $context);
    }

    exit;
}

$context = buildPageContext($BaseURL, $Project, false, false, null, false, null, null, $Includes, $Functions, $OG_Params, $OG_Signature);
$context['render_layout'] = $RenderLayout;
renderPage($twig, 'main.html.twig', $context);
