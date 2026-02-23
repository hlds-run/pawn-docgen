<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Service\PageContextBuilder;
use App\Repository\FileRepository;
use App\Repository\FunctionRepository;
use App\Repository\ConstantRepository;

$settings = require __DIR__ . '/settings.php';

return function (ContainerBuilder $containerBuilder) use ($settings) {
    $containerBuilder->addDefinitions([
        Connection::class => function () use ($settings) {
            return DriverManager::getConnection($settings['database']);
        },

        FileRepository::class => function ($container) {
            return new FileRepository($container->get(Connection::class));
        },

        FunctionRepository::class => function ($container) {
            return new FunctionRepository($container->get(Connection::class));
        },

        ConstantRepository::class => function ($container) {
            return new ConstantRepository($container->get(Connection::class));
        },

        Environment::class => function ($container) {
            $loader = new FilesystemLoader(__DIR__ . '/../templates');
            $twig = new Environment($loader, [
                'cache' => false,
                'autoescape' => 'html',
            ]);

            $twig->addFilter(new \Twig\TwigFilter('string_to_slug', function (string $string): string {
                $string = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
                $string = trim($string, "- \t\n\r\0\x0B");
                return strtolower($string);
            }));

            $twig->addFilter(new \Twig\TwigFilter('group_by_tag', function (array $tags): array {
                $grouped = [];
                foreach ($tags as $tag) {
                    $grouped[$tag['Tag']][] = $tag;
                }
                return $grouped;
            }));

            $twig->addFilter(new \Twig\TwigFilter('json_decode', function (string $json) {
                return json_decode($json, true);
            }));

            return $twig;
        },

        PageContextBuilder::class => function ($container) use ($settings) {
            return new PageContextBuilder(
                $settings['app']['base_url'],
                $settings['app']['name'],
                $settings['app']['og_hmac_secret'],
                $settings['app']['og_hmac_symbols']
            );
        },

        'app_settings' => $settings,

        'baseUrl' => $settings['app']['base_url'],
    ]);
};
