<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/TwigSetup.php';
require __DIR__ . '/src/Helpers.php';

$twig = createTwig();

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

$RenderLayout = true;

if (substr($Path, 0, 8) === '__search') {
    $RenderLayout = false;
}

$CurrentOpenFile = false;
$CurrentOpenFunction = false;
$Includes = [];
$Functions = [];

if ($RenderLayout) {
    $Includes = $Database->query('SELECT `ID`, `IncludeName` FROM `' . $Columns['Files'] . '` ORDER BY `IncludeName` ASC')->fetchAll(PDO::FETCH_KEY_PAIR);

    $STH = $Database->query('SELECT `Function`, `Type`, `Comment`, `IncludeName` FROM `' . $Columns['Functions'] . '` ORDER BY `Type` ASC, `Function` ASC');

    while ($Function = $STH->fetch()) {
        $Functions[$Function['IncludeName']][] = [
            'Function' => $Function['Function'],
            'Comment' => $Function['Comment'],
            'Type' => $Function['Type'],
        ];
    }
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

    $Action = !empty($Path[1]) ? htmlspecialchars($Path[1], ENT_QUOTES, 'UTF-8') : false;

    if (isset($Path[0])) {
        $IncludeName = htmlspecialchars($Path[0], ENT_QUOTES, 'UTF-8');

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
