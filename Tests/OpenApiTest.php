<?php

namespace Draw\Component\OpenApi\Tests;

use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\Exception\ExtractionCompletedException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(OpenApi::class)]
class OpenApiTest extends TestCase
{
    private OpenApi $object;

    protected function setUp(): void
    {
        $this->object = new OpenApi();
    }

    #[DataProvider('provideExtractSwaggerSchemaCases')]
    public function testExtractSwaggerSchema(string $file): void
    {
        $schema = $this->object->extract(file_get_contents($file));
        static::assertInstanceOf(Root::class, $schema);

        static::assertJsonStringEqualsJsonString(file_get_contents($file), $this->object->dump($schema, false));
    }

    public static function provideExtractSwaggerSchemaCases(): iterable
    {
        foreach (glob(__DIR__.'/fixture/schema/*.json') as $file) {
            yield basename($file) => [$file];
        }
    }

    public function testValidateError(): void
    {
        $this->expectException(ConstraintViolationListException::class);

        $schema = new Root();
        $schema->swagger = '';

        $this->object->validate($schema);
    }

    public function testExtractExtractionCompletedException(): void
    {
        $this->object = new OpenApi([
            $extractor1 = $this->createMock(ExtractorInterface::class),
            $extractor2 = $this->createMock(ExtractorInterface::class),
        ]);

        $extractor1->expects(static::once())->method('canExtract')->willReturn(true);
        $extractor1->expects(static::once())->method('extract')->willThrowException(new ExtractionCompletedException());
        $extractor2->expects(static::never())->method('canExtract');

        $this->object->extract('');
    }
}
