<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\UserAttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class UserAttributeValueAdded
{
    /**
     * @var UserAttributeValue
     */
    public $userAttributeValue;

    /**
     * @var array
     */
    public $context;

    public function __construct(UserAttributeValue $userAttributeValue, array $context)
    {
        $this->userAttributeValue = $userAttributeValue;
        $this->context = $context;
    }
}
