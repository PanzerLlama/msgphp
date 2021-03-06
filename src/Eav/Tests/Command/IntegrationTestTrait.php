<?php

declare(strict_types=1);

namespace MsgPhp\Eav\Tests\Command;

use MsgPhp\Domain\Factory\GenericDomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Doctrine\Test\EntityManagerTestTrait;
use MsgPhp\Domain\Infrastructure\Messenger\Test\MessageBusTestTrait;
use MsgPhp\Eav\Attribute;
use MsgPhp\Eav\AttributeId;
use MsgPhp\Eav\Command;
use MsgPhp\Eav\Infrastructure\Doctrine\Repository;
use MsgPhp\Eav\Infrastructure\Doctrine\Type;
use MsgPhp\Eav\ScalarAttributeId;
use MsgPhp\Eav\Tests\Fixtures\Entities;

trait IntegrationTestTrait
{
    use EntityManagerTestTrait;
    use MessageBusTestTrait;

    public static function setUpBeforeClass(): void
    {
        self::initEm();
        self::initBus();
    }

    public static function tearDownAfterClass(): void
    {
        self::destroyBus();
        self::destroyEm();
    }

    protected function setUp(): void
    {
        self::prepareEm();
    }

    protected function tearDown(): void
    {
        self::cleanEm();
        self::cleanBus();
    }

    protected static function getMessageHandlers(): iterable
    {
        $factory = self::createDomainFactory();
        $bus = self::createDomainMessageBus();
        $repository = self::createAttributeRepository();

        yield Command\CreateAttribute::class => new Command\Handler\CreateAttributeHandler($factory, $bus, $repository);
        yield Command\DeleteAttribute::class => new Command\Handler\DeleteAttributeHandler($factory, $bus, $repository);
    }

    protected static function createSchema(): bool
    {
        return true;
    }

    protected static function getEntityMappings(): iterable
    {
        yield 'annot' => [
            'MsgPhp\\Eav\\Tests\\Fixtures\\Entities\\' => \dirname(__DIR__).'/Fixtures/Entities',
        ];
        yield 'xml' => [
            'MsgPhp\\Eav\\' => self::createEntityDistMapping(\dirname(__DIR__, 2).'/Infrastructure/Doctrine/Resources/dist-mapping'),
        ];
    }

    protected static function getEntityIdTypes(): iterable
    {
        yield Type\AttributeIdType::class => ScalarAttributeId::class;
    }

    private static function createDomainFactory(): DomainObjectFactory
    {
        return new DomainObjectFactory(new GenericDomainObjectFactory([
            AttributeId::class => ScalarAttributeId::class,
            Attribute::class => Entities\TestAttribute::class,
        ]), self::$em);
    }

    private static function createAttributeRepository(): Repository\AttributeRepository
    {
        return new Repository\AttributeRepository(Entities\TestAttribute::class, self::$em);
    }
}
