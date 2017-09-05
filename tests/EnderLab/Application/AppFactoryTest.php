<?php

namespace Tests\EnderLab\Application;

use EnderLab\Application\App;
use EnderLab\Application\AppFactory;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Router\Router;
use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class AppFactoryTest extends TestCase
{
    public function testCreateAppWithoutArg(): void
    {
        $app = AppFactory::create();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testCreateAppWithArg(): void
    {
        $app = AppFactory::create(
            [
                'global.env' => \DI\env('global_env', 'dev'),
                'routes'     => [
                    \DI\object(\EnderLab\Router\Route::class)->constructor(
                        '/blog/:id/:pouette',
                        function (ServerRequestInterface $request, DelegateInterface $delegate) {
                            $response = $delegate->process($request);
                            $response->getBody()->write('<br>Middleware callable !!!<br>');

                            return $response;
                        },
                        'GET',
                        'first_route_test',
                        ['id' => '\\d+', 'pouette' => '\\w+']
                    )
                ],
                'logger.name'    => 'default-logger',
                'logger.file'    => __DIR__ . '/../logs/app.log',
                'logger.handler' => [
                    \DI\object(
                        \Monolog\Handler\StreamHandler::class
                    )->constructor(\DI\get('logger.file'))
                ],
                'logger.processor' => [/*\DI\object(\Monolog\Processor\WebProcessor::class)*/],
                'logger'           => \DI\object(
                    \Monolog\Logger::class
                )->constructor(
                    \DI\get('logger.name'),
                    \DI\get('logger.handler'),
                    \DI\get('logger.processor')
                )
            ],
            new Dispatcher(),
            new Router()
        );
        $this->assertInstanceOf(App::class, $app);
    }
}
