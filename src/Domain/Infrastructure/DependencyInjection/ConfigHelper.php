<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\DependencyInjection;

use MsgPhp\Domain\Event\DomainEventHandler;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ConfigHelper
{
    public const DEFAULT_ID_TYPE = 'integer';

    private function __construct()
    {
    }

    public static function defaultBundleConfig(array $idTypeMapping): \Closure
    {
        return static function (array $value) use ($idTypeMapping): array {
            foreach ($idTypeMapping as $id => $mapping) {
                $types = array_keys($mapping);
                $type = $value['id_type_mapping'][$id] ?? ($value['id_type_mapping'][$id] = $value['default_id_type'] ?? reset($types));
                $mapping = self::resolveIdTypeMapping($mapping);

                if (!isset($value['class_mapping'][$id]) && isset($mapping[$type])) {
                    $value['class_mapping'][$id] = $mapping[$type];
                }
            }
            foreach ($value['id_type_mapping'] as $id => $type) {
                if (!isset($value['class_mapping'][$id])) {
                    throw new \LogicException('No class available for ID "'.$id.'" of data-type "'.$type.'".');
                }
            }
            unset($value['default_id_type']);

            return $value;
        };
    }

    public static function resolveCommandMappingConfig(array $commandMapping, array $classMapping, array &$config): void
    {
        foreach ($commandMapping as $class => $features) {
            $available = isset($classMapping[$class]);
            $handlerAvailable = $available && is_subclass_of($classMapping[$class], DomainEventHandler::class);

            foreach ($features as $feature => $info) {
                if (\is_array($info)) {
                    $config += array_fill_keys($info, $available && self::uses($classMapping[$class], $feature) ? $handlerAvailable : false);
                } else {
                    $config += [$info => $available];
                }
            }
        }
    }

    private static function uses(string $class, string $trait): bool
    {
        static $uses = [];

        if (!isset($uses[$class])) {
            $resolve = static function (string $class) use (&$resolve): array {
                $resolved = [];

                foreach (class_uses($class) as $used) {
                    $resolved[$used] = true;
                    $resolved += $resolve($used);
                }

                return $resolved;
            };

            $uses[$class] = $resolve($class);
        }

        return isset($uses[$class][$trait]);
    }

    private static function resolveIdTypeMapping(array $mapping): array
    {
        if (isset($mapping['scalar'])) {
            $mapping['string'] = $mapping['string'] ?? $mapping['scalar'];
            $mapping['integer'] = $mapping['integer'] ?? $mapping['scalar'];
        }

        if (isset($mapping['uuid'])) {
            $mapping['uuid_binary'] = $mapping['uuid_binary'] ?? $mapping['uuid'];
            $mapping['uuid_binary_ordered_time'] = $mapping['uuid_binary_ordered_time'] ?? $mapping['uuid_binary'];
        }

        return $mapping;
    }
}
