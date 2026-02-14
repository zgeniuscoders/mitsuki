<?php

namespace Mitsuki\Mitsuki\Attributes;

#[\Attribute] class Controller
{
    public function __construct(
        private string $baseUri
    )
    {
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function setBaseUri(string $baseUri): void
    {
        $this->baseUri = $baseUri;
    }
}