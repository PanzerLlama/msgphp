<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\GenericPaginatedDomainCollection;

final class GenericPaginatedDomainCollectionTest extends DomainCollectionTestCase
{
    public function testDefaultPagination(): void
    {
        $collection = new GenericPaginatedDomainCollection([1, 2, 3, 4]);

        self::assertSame(0., $collection->getOffset());
        self::assertSame(0., $collection->getLimit());
        self::assertSame(1., $collection->getCurrentPage());
        self::assertSame(1., $collection->getLastPage());
        self::assertSame(4., $collection->getTotalCount());
        self::assertCount(4, $collection);
    }

    public function testPagination(): void
    {
        $collection = new GenericPaginatedDomainCollection([], 8., 2., 2., 12.);

        self::assertSame(8., $collection->getOffset());
        self::assertSame(2., $collection->getLimit());
        self::assertSame(5., $collection->getCurrentPage());
        self::assertSame(6., $collection->getLastPage());
        self::assertSame(12., $collection->getTotalCount());
        self::assertCount(2, $collection);
    }

    protected static function createCollection(array $elements): DomainCollection
    {
        return new GenericPaginatedDomainCollection($elements);
    }
}
