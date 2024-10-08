<?php

namespace Draw\Component\OpenApi\Tests\Versioning;

use Draw\Component\OpenApi\Versioning\RouteDefaultApiRouteVersionMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;

/**
 * @internal
 */
#[CoversClass(RouteDefaultApiRouteVersionMatcher::class)]
class RouteDefaultApiRouteVersionMatcherTest extends TestCase
{
    public function testMatchVersionTrue(): void
    {
        $matcher = new RouteDefaultApiRouteVersionMatcher();

        $route = new Route('/test');
        $route->setDefault('_api_version', 'v1');
        static::assertTrue($matcher->matchVersion('v1', $route));
    }

    public function testMatchVersionFalse(): void
    {
        $matcher = new RouteDefaultApiRouteVersionMatcher();

        $route = new Route('/test');
        static::assertFalse($matcher->matchVersion('v1', $route));
    }
}
