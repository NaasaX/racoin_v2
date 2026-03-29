<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class TrailingSlashMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ($path !== '/' && str_ends_with($path, '/')) {
            $trimmedPath = rtrim($path, '/');
            $newUri = $uri->withPath($trimmedPath);

            if (strtoupper($request->getMethod()) === 'GET') {
                return $this->responseFactory
                    ->createResponse(301)
                    ->withHeader('Location', (string) $newUri);
            }

            $request = $request->withUri($newUri);
        }

        return $handler->handle($request);
    }
}
