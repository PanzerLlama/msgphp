<?php

declare(strict_types=1);

namespace MsgPhp\User\Role;

use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface RoleProvider
{
    /**
     * @return string[]
     */
    public function getRoles(User $user): array;
}
