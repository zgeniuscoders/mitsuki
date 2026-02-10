<?php

namespace Mitsuki\Mitsuki\Routes;

#[\Attribute] class Route
{
    public function __construct(
        private string       $name,
        private string       $path,
        private array|string $methods,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getMethods(): array|string
    {
        return $this->methods;
    }

    public function setMethods(array|string $methods): void
    {
        $this->methods = $methods;
    }
}

