<?php

namespace SEOInjector\Exceptions;

class InvalidApiKeyException extends SEOInjectorException
{
    public function __construct(string $message = "Invalid API key provided")
    {
        parent::__construct($message);
    }
}