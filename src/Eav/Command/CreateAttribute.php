<?php

declare(strict_types=1);

namespace MsgPhp\Eav\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class CreateAttribute
{
    /**
     * @var array
     */
    public $context;

    public function __construct(array $context)
    {
        $this->context = $context;
    }
}
