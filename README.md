# enderlab
Micro framework middleware PSR-15

[![Build Status](https://travis-ci.org/ender9108/enderlab.svg?branch=master)](https://travis-ci.org/ender9108/enderlab)
[![Coverage Status](https://coveralls.io/repos/github/ender9108/enderlab/badge.svg?branch=master)](https://coveralls.io/github/ender9108/enderlab?branch=master)


## Requirements
- psr/http-message
- guzzlehttp/psr7
- http-interop/response-sender
- http-interop/http-middleware
- psr/container


## Author
Alexandre Berthelot <alexandreberthelot9108@gmail.com>


## Basic Usage
```php
<?php
require dirname(__FILE__).'/../vendor/autoload.php';

use EnderLab\Application\AppFactory;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;

$app = AppFactory::create('../config/config.php');
$app->pipe(new \Middlewares\Whoops());
$app->pipe(function(ServerRequestInterface $request, DelegateInterface $delegate) {
    $response = $delegate->process($request);
    $response->getBody()->write('<br>Middleware callable !!!<br>');

    return $response;
});

\Http\Response\send($app->run());
```


## Create middleware
```php
<?php
namespace App\MyTest

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoggerMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        /* ... */
        /* My treatment */
        /* Return response */
        /* ... */
    }
}
```