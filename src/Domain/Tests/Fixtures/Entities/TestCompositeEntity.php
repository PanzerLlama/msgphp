<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Tests\Fixtures\TestDomainId;

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestCompositeEntity extends BaseTestEntity
{
    /**
     * @var DomainId
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\Column(type="domain_id")
     */
    public $idA;

    /**
     * @var string
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\Column(type="string")
     */
    public $idB;

    public static function getIdFields(): array
    {
        return ['idA', 'idB'];
    }

    public static function getFieldValues(): array
    {
        return [
            'idA' => [new TestDomainId('-1'), new TestDomainId('0'), new TestDomainId('1')],
            'idB' => ['', 'foo'],
        ];
    }
}
