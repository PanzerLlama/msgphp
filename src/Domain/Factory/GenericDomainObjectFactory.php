<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Exception\InvalidClassException;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;
use Symfony\Component\VarExporter\Instantiator;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class GenericDomainObjectFactory implements DomainObjectFactory
{
    /**
     * @psalm-var array<class-string, class-string>
     *
     * @var string[]
     */
    private $classMapping;

    /**
     * @var DomainObjectFactory|null
     */
    private $factory;

    /**
     * @psalm-param array<class-string, class-string> $classMapping
     *
     * @param string[] $classMapping
     */
    public function __construct(array $classMapping = [])
    {
        $this->classMapping = $classMapping;
    }

    public function setNestedFactory(?DomainObjectFactory $factory): void
    {
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function create(string $class, array $context = [])
    {
        $class = $this->getClass($class, $context);

        if (is_subclass_of($class, DomainId::class) || is_subclass_of($class, DomainCollection::class)) {
            return $class::fromValue(...$this->resolveArguments($class, 'fromValue', $context));
        }

        /** @psalm-suppress DocblockTypeContradiction */
        if (!class_exists($class)) {
            throw InvalidClassException::create($class);
        }

        return new $class(...$this->resolveArguments($class, '__construct', $context));
    }

    /**
     * @inheritdoc
     */
    public function reference(string $class, array $context = [])
    {
        if (!class_exists(Instantiator::class)) {
            throw new \LogicException('Method "'.__METHOD__.'()" requires "symfony/var-exporter".');
        }

        $class = $this->getClass($class, $context);
        $properties = [];
        foreach ($context as $key => $value) {
            if (property_exists($class, $key)) {
                $properties[$key] = $value;
                continue;
            }
        }

        try {
            return Instantiator::instantiate($class, $properties);
        } catch (ClassNotFoundException $e) {
            throw InvalidClassException::create($class);
        }
    }

    /**
     * @inheritdoc
     */
    public function getClass(string $class, array $context = []): string
    {
        return $this->classMapping[$class] ?? $class;
    }

    /**
     * @psalm-param class-string $class
     */
    private function resolveArguments(string $class, string $method, array $context): array
    {
        $arguments = [];

        foreach (ClassMethodResolver::resolve($class, $method) as $argument => $metadata) {
            if (\array_key_exists($argument, $context)) {
                $given = true;
                $value = $context[$argument];
            } elseif (!$metadata['required']) {
                $given = false;
                $value = $metadata['default'];
            } else {
                throw new \LogicException('No value available for argument $'.$argument.' in class method "'.$class.'::'.$method.'()".');
            }

            $type = $metadata['type'];
            if ($given && null !== $type && !\is_object($value) && (class_exists($type) || interface_exists($type, false))) {
                $arguments[] = ($this->factory ?? $this)->create($metadata['type'], (array) $value);
                continue;
            }

            $arguments[] = $value;
        }

        return $arguments;
    }
}
