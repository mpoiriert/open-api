<?php

namespace Draw\Component\OpenApi\Tests\Exception;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExtractionImpossibleException::class)]
class ExtractionImpossibleExceptionTest extends TestCase
{
    private ExtractionImpossibleException $object;

    protected function setUp(): void
    {
        $this->object = new ExtractionImpossibleException();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            \Exception::class,
            $this->object
        );
    }
}
