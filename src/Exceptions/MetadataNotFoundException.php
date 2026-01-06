<?php

namespace SEOInjector\Exceptions;

class MetadataNotFoundException extends SEOInjectorException
{
    public function __construct(string $url)
    {
        parent::__construct("Metadata not found for URL: {$url}");
    }
}