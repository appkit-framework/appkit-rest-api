<?php

namespace AppKit\Rest\Api;

use AppKit\Http\Server\Message\ServerHttpError;

class RestApiError extends ServerHttpError {
    private $errorCode;
    private $details;

    function __construct(
        $status,
        $errorCode = 'UNKNOWN_ERROR',
        $message = null,
        $details = [],
        $headers = [],
        $previous = null
    ) {
        parent::__construct($status, $message, $headers, $previous);
        $this -> errorCode = $errorCode;
        $this -> details = $details;
    }

    public function getErrorCode() {
        return $this -> errorCode;
    }

    public function getDetails() {
        return $this -> details;
    }
}
