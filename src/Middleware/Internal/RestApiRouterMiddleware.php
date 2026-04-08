<?php

namespace AppKit\Rest\Api\Middleware\Internal;

use AppKit\Rest\Api\RestApiError;

use AppKit\Http\Server\Middleware\ServerHttpMiddlewareInterface;
use AppKit\Http\Server\Router\HttpRouter;

class RestApiRouterMiddleware implements ServerHttpMiddlewareInterface {
    private $setupRoutesCallback;

    private $log;
    private $router;

    function __construct($log, $setupRoutesCallback) {
        $this -> setupRoutesCallback = $setupRoutesCallback;

        $this -> log = $log -> withModule(static::class);
    }

    public function processRequest($request, $next) {
        if(! $this -> router)
            $this -> router = new HttpRouter(
                $this -> setupRoutesCallback,
                HttpRouter::TS_LOOSE
            );

        [$match, $result, $params, $extra] = $this -> router -> matchRequest($request);

        $this -> log -> debug(
            'Matched request',
            [
                'requestPath' => $request -> getPath(),
                'match' => $match
            ]
        );

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
                [ 'allowedMethods' => $result ],
                [ 'Allow' => $result ]
            );

        $request -> setAttribute('routeHandler', $result)
            -> setAttribute('routeParams', $params)
            -> setAttribute('routeExtra', $extra);

        return $next($request);
    }
}
