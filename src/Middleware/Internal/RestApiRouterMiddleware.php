<?php

namespace AppKit\Rest\Api\Middleware\Internal;

use AppKit\Rest\Api\RestApiError;

use AppKit\Http\Server\Middleware\ServerHttpMiddlewareInterface;
use AppKit\Http\Server\Router\HttpRouter;

class RestApiRouterMiddleware implements ServerHttpMiddlewareInterface {
    private $setupRoutesCallback;

    private $router;

    function __construct($setupRoutesCallback) {
        $this -> setupRoutesCallback = $setupRoutesCallback;
    }

    public function processRequest($request, $next) {
        if(! $this -> router)
            $this -> router = new HttpRouter($this -> setupRoutesCallback);

        [$match, $handler, $params, $extra, $allow] = $this -> router -> matchRequest($request);

        if($match == HttpRouter::NOT_FOUND)
            throw new RestApiError(
                404,
                'ENDPOINT_NOT_FOUND',
                'Endpoint not found'
            );
        if($match == HttpRouter::METHOD_NOT_ALLOWED)
            throw new RestApiError(
                405,
                'METHOD_NOT_ALLOWED',
                'Method not allowed',
                [ 'allowedMethods' => $allow ],
                [ 'Allow' => $allow ]
            );

        $request -> setAttribute('routeHandler', $handler)
            -> setAttribute('routeParams', $params)
            -> setAttribute('routeExtra', $extra);

        return $next($request);
    }
}
