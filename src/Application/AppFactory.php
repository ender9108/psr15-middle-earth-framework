<?php

namespace EnderLab\MiddleEarth\Application;

use DI\ContainerBuilder;
use EnderLab\MiddleEarth\Dispatcher\Dispatcher;
use EnderLab\MiddleEarth\Dispatcher\DispatcherInterface;
use EnderLab\MiddleEarth\Router\Router;
use EnderLab\MiddleEarth\Router\RouterInterface;
use FilesystemIterator;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

final class AppFactory
{
    /**
     * Build App object and load config.
     *
     * @param string|ContainerInterface|null $containerConfig
     * @param RouterInterface|null           $router
     * @param DispatcherInterface|null       $dispatcher
     * @param null|ResponseInterface         $defaultResponse
     *
     * @throws \EnderLab\MiddleEarth\Router\RouterException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     *
     * @return App
     */
    public static function create(
        $containerConfig = null,
        ?RouterInterface $router = null,
        ?DispatcherInterface $dispatcher = null,
        ?ResponseInterface $defaultResponse = null
    ): App {
        $container = self::buildContainer($containerConfig);
        $dispatcher = $dispatcher ?: new Dispatcher();
        $defaultResponse = $defaultResponse ?: new Response();
        $router = $router ?: new Router(
            [],
            ($container->has('router.options') ? $container->get('router.options') : [])
        );

        if ($container->has('router.routes') && is_array($container->get('router.routes'))) {
            $router->addRoutes($container->get('router.routes'));
        }

        $container->set(Dispatcher::class, $dispatcher);
        $container->set(Router::class, $router);
        $container->set(Response::class, $defaultResponse);

        $app = new App($container, $router, $dispatcher, $defaultResponse);

        if ($container->has('app.env') && is_string($container->get('app.env'))) {
            $app->setEnv($container->get('app.env'));
        }

        if ($container->has('app.error.handler') && true === (bool) $container->get('app.error.handler')) {
            $app->enableErrorHandler();
        }

        return $app;
    }

    /**
     * @param null $containerConfig
     *
     * @throws \Exception
     *
     * @return ContainerInterface
     */
    private static function buildContainer($containerConfig = null): ContainerInterface
    {
        $containerBuilder = new ContainerBuilder();
        $env = $_ENV['ENV'] ?? App::ENV_PROD;

        if (App::ENV_PROD === $env) {
            /*$containerBuilder->enableCompilation('tmp/cache');

            if (extension_loaded('acpu')) {
                $containerBuilder->enableDefinitionCache();
            }*/
        }

        if (is_string($containerConfig)) {
            if (is_dir($containerConfig)) {
                $iterator = new FilesystemIterator($containerConfig, FilesystemIterator::SKIP_DOTS);

                foreach ($iterator as $file) {
                    $containerBuilder->addDefinitions($file->getPathname());
                }
            } elseif (file_exists($containerConfig)) {
                $containerBuilder->addDefinitions($containerConfig);
            } else {
                throw new InvalidArgumentException('Config file "' . $containerConfig . '" doesn\'t exists.');
            }

            $container = $containerBuilder->build();
        } elseif (is_array($containerConfig)) {
            $containerBuilder->addDefinitions($containerConfig);
            $container = $containerBuilder->build();
        } else {
            $container = $containerBuilder->build();
        }

        return $container;
    }
}
