<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Schema\Schema;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

abstract class ConstraintExtractor implements ConstraintExtractorInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    public function setMetadataFactory(MetadataFactoryInterface $metadataFactoryInterface)
    {
        $this->metadataFactory = $metadataFactoryInterface;
    }

    abstract public function supportConstraint(Constraint $constraint);

    abstract public function extractConstraint(Constraint $constraint, ConstraintExtractionContext $context);

    protected function assertSupportConstraint(Constraint $constraint)
    {
        if (!$this->supportConstraint($constraint)) {
            throw new InvalidArgumentException(sprintf('The constraint of type [%s] is not supported by [%s]', get_class($constraint), get_class($this)));
        }
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $type
     *
     * @return bool
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$type instanceof Schema) {
            return false;
        }

        if (!$source instanceof ReflectionClass) {
            return false;
        }

        $constraints = $this->getPropertiesConstraints(
            $source,
            $type,
            $this->getValidationGroups($extractionContext)
        );

        return count($constraints);
    }

    private function getValidationGroups(ExtractionContextInterface $extractionContext)
    {
        $context = $extractionContext->getParameter('model-context', []);

        return array_key_exists('validation-groups', $context) ? $context['validation-groups'] : null;
    }

    private function getPropertiesConstraints(ReflectionClass $reflectionClass, Schema $schema, array $groups = null)
    {
        $class = $reflectionClass->getName();
        if (!$this->metadataFactory->hasMetadataFor($class)) {
            return [];
        }

        if (null === $groups) {
            $groups = [Constraint::DEFAULT_GROUP];
        }

        $constraints = [];

        /* @var ClassMetadataInterface $classMetadata */
        $classMetadata = $this->metadataFactory->getMetadataFor($class);
        foreach ($classMetadata->getConstrainedProperties() as $propertyName) {
            //This is to prevent hading properties just because they have validation
            if (!isset($schema->properties[$propertyName])) {
                continue;
            }

            $constraints[$propertyName] = [];
            foreach ($classMetadata->getPropertyMetadata($propertyName) as $propertyMetadata) {
                /* @var $propertyMetadata */

                $propertyConstraints = [];
                foreach ($groups as $group) {
                    $propertyConstraints = array_merge(
                        $propertyConstraints,
                        $propertyMetadata->findConstraints($group)
                    );
                }

                $finalPropertyConstraints = [];

                foreach ($propertyConstraints as $current) {
                    if (!in_array($current, $finalPropertyConstraints)) {
                        $finalPropertyConstraints[] = $current;
                    }
                }

                $finalPropertyConstraints = array_filter(
                    $finalPropertyConstraints,
                    [$this, 'supportConstraint']
                );

                $constraints[$propertyName] = array_merge($constraints[$propertyName], $finalPropertyConstraints);
            }
        }

        return array_filter($constraints);
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param ReflectionClass $source
     * @param Schema          $target
     *
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $constraintExtractionContext = new ConstraintExtractionContext();
        $constraintExtractionContext->classSchema = $target;
        $constraintExtractionContext->context = 'property';

        $validationGroups = $this->getValidationGroups($extractionContext);

        $propertyConstraints = $this->getPropertiesConstraints($source, $target, $validationGroups);

        foreach ($propertyConstraints as $propertyName => $constraints) {
            foreach ($constraints as $constraint) {
                $constraintExtractionContext->propertySchema = $target->properties[$propertyName];
                $constraintExtractionContext->propertyName = $propertyName;
                $this->extractConstraint($constraint, $constraintExtractionContext);
            }
        }
    }
}
