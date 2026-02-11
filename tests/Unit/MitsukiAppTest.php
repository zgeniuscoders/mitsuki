<?php

test("test mitsuki app start", function () {
    $app = $this->app;
    $container = $app->getContainer();
    $this->assertTrue($container->has('cache.dir'));
});