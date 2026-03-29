<?php

namespace AppKit\Rest\Api\ErrorHandler;

use AppKit\Rest\Api\RestApiResponse;
use AppKit\Rest\Api\RestApiError;

use AppKit\Http\Server\ErrorHandler\HttpErrorHandlerInterface;

class RestApiErrorHandler implements HttpErrorHandlerInterface {
    public function handleError($error, $request) {
        if($error instanceof RestApiError)
            return new RestApiResponse([
                'error' => [
                    'code' => $error -> getErrorCode(),
                    'message' => $error -> getMessage(),
                    'details' => $error -> getDetails()
                ]
            ]);

        return new RestApiResponse([
            'error' => [
                'code' => 'UNKNOWN_ERROR',
                'message' => $error -> getMessage(),
                'details' => []
            ]
        ]);
    }
}
