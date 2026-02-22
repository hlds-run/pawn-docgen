<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

function createTwig(): Environment
{
    $loader = new FilesystemLoader(__DIR__ . '/../templates');
    $twig = new Environment($loader, [
        'cache' => false,
        'autoescape' => 'html',
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

    return $twig;
}
