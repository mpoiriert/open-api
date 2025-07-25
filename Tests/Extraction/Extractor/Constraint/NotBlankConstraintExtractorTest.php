<?php

namespace Draw\Component\OpenApi\Tests\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Extraction\Extractor\Constraint\ConstraintExtractionContext;
use Draw\Component\OpenApi\Extraction\Extractor\Constraint\NotBlankConstraintExtractor;
use Draw\Component\OpenApi\Schema\QueryParameter;
use Draw\Component\OpenApi\Schema\Schema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @internal
 */
#[CoversClass(NotBlankConstraintExtractor::class)]
class NotBlankConstraintExtractorTest extends TestCase
{
    private NotBlankConstraintExtractor $object;

    protected function setUp(): void
    {
        $this->object = new NotBlankConstraintExtractor();
    }

    #[DataProvider('provideSupportCases')]
    public function testSupport(Constraint $constraint, bool $expected): void
    {
        static::assertSame($expected, $this->object->supportConstraint($constraint));
    }

    public static function provideSupportCases(): iterable
    {
        yield 'other-constraint' => [
            new IsNull(),
            false,
        ];

        yield 'not-blank' => [
            new NotBlank(),
            true,
        ];
    }

    public function testExtractConstraintBaseParameter(): void
    {
        $constraint = new NotBlank();
        $context = new ConstraintExtractionContext();
        $context->validationConfiguration = new QueryParameter();

        $this->object->extractConstraint($constraint, $context);

        static::assertSame('not empty', $context->validationConfiguration->format);
        static::assertTrue($context->validationConfiguration->required);
    }

    public function testExtractConstraintSchema(): void
    {
        $constraint = new NotBlank();
        $context = new ConstraintExtractionContext();
        $context->propertyName = 'test';
        $context->classSchema = $context->validationConfiguration = new Schema();

        $this->object->extractConstraint($constraint, $context);

        static::assertSame('not empty', $context->validationConfiguration->format);
        static::assertContains('test', $context->classSchema->required);
    }

    public function testExtractConstraintBaseParameterAllowNull(): void
    {
        $constraint = new NotBlank();
        $constraint->allowNull = true;
        $context = new ConstraintExtractionContext();
        $context->validationConfiguration = new QueryParameter();

        $this->object->extractConstraint($constraint, $context);

        static::assertSame('not empty', $context->validationConfiguration->format);
        static::assertNull($context->validationConfiguration->required);
    }
}
