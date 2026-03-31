<?php

namespace AppKit\Rest\Api;

use AppKit\Rest\Api\Middleware\Internal\RestApiRouterMiddleware;
use AppKit\Rest\Api\ErrorHandler\RestApiErrorHandler;

use AppKit\Http\Server\Resource\AbstractHttpResource;
use AppKit\Http\Server\Message\ServerHttpResponse;
use AppKit\Health\HealthIndicatorInterface;

class RestApiResource extends AbstractHttpResource {
    private $apis = [];

    function __construct($log) {
        parent::__construct($log -> withModule(static::class));

        $this -> pipeline -> addMiddleware(
            new RestApiRouterMiddleware(
                $this -> log,
                function($routeCollector) {
                    $this -> collectRoutes($routeCollector);
                }
            )
        );

        $this -> errorHandlerMw -> setErrorHandler(
            new RestApiErrorHandler()
        );
    }

    protected function getAdditionalHealthData() {
        $data = [];

        foreach($this -> apis as [$api, $prefix])
            if($api instanceof HealthIndicatorInterface)
                $data['APIs'][get_class($api) . "at $prefix"] = $api;

        return $data;
    }

    public function addApi($api, $prefix = '/') {
        $prefix = '/'.trim($prefix, '/');
        $this -> apis[] = [$api, $prefix];
        $this -> log -> debug(
            'Registered API {api} at {prefix}',
            [
                'api' => get_class($api),
                'prefix' => $prefix
            ]
        );
    }

    protected function handleRequest($request) {
        $handler = $request -> getAttribute('routeHandler');
        $response = $handler($request);

        if(! $response instanceof ServerHttpResponse)
            return new RestApiResponse($response);

        return $response;
    }

    private function collectRoutes($routeCollector) {
        foreach($this -> apis as [$api, $prefix]) {
            if($prefix == '/')
                $api -> setupRoutes($routeCollector);
            else
                $routeCollector -> addGroup(
                    $prefix,
                    [ $api, 'setupRoutes' ]
                );

            $this -> log -> debug(
                'Collected routes from API {api}',
                [ 'api' => get_class($api) ]
            );
        }
    }
}
