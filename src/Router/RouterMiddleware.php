<?php

namespace EnderLab\MiddleEarth\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterMiddleware implements MiddlewareInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var RouterInterface
     */
    private $response;

    /**
     * RouterMiddleware constructor.
     *
     * @param RouterInterface   $router
     * @param ResponseInterface $response
     */
    public function __construct(RouterInterface $router, ResponseInterface $response)
    {
        $this->router = $router;
        $this->response = $response;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $requestHandler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
        $route = $this->router->match($request);

        if (null === $route) {
            return $requestHandler->handle($request);
        }

        $request = $request->withAttribute(Route::class, $route);

        foreach ($route->getParams() as $label => $value) {
            $request = $request->withAttribute($label, $value);
        }

        return $requestHandler->handle($request);
    }
}
