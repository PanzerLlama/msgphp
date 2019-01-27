# User Bundle Installation

Install the bundle using [Composer]:

```bash
composer require msgphp/user-bundle
```

!!! info
    When using [Symfony Flex] to manage your application the minimal bundle [recipe configuration] is applied
    automatically upon installation
    
## Configure the User Entity

```php
<?php

// src/Entity/User/User.php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\User\Entity\User as BaseUser;
use MsgPhp\User\UserIdInterface;

/**
 * @ORM\Entity()
 */
class User extends BaseUser
{
    /** @ORM\Id() @ORM\GeneratedValue() @ORM\Column(type="msgphp_user_id", length=191) */
    private $id;

    public function __construct(UserIdInterface $id)
    {
        $this->id = $id;
    }

    public function getId(): UserIdInterface
    {
        return $this->id;
    }
}
```

```yaml
# config/packages/msgphp_user.yaml

msgphp_user:
    class_mapping:
        MsgPhp\User\Entity\User: App\Entity\User\User
```

!!! note
    The extra `/User/` layer specifies the _domain_ the entity is bound to and can be left out depending on the
    complexity of your application

### Disable Required Constructor Argument

The required constructor argument enables to provide an [identifier](../../ddd/identifiers.md) upfront, so the user does
not have to be queried for it after. To disable it, use:

```php
<?php

// ...

public function __construct()
{
    $this->id = new MsgPhp\User\UserId(); // represents an "empty" ID (i.e. "new")
}
```

### Disable Automatic Identifier Hydration

Using the built-in `msgphp_user_id` [Doctrine type](../../infrastructure/doctrine-dbal.md#domain-identifier-type)
enables decoupling between your entity and its identifier. To disable it, use:

```php
<?php

// ...

/** @ORM\Id() @ORM\GeneratedValue() @ORM\Column(type="integer") */
private $id;

// ...

public function getId(): UserIdInterface
{
    return MsgPhp\User\UserId::fromValue($this->id);
}
```

## Configure the User Identity

The user is identified by a built-in [domain identifier](../../ddd/identifiers.md) of type `MsgPhp\User\UserIdInterface`.

The default data type is `integer` and uses the first-class `MsgPhp\User\UserId` implementation.

Optionally change the data type and implementation being used by MsgPHP:

```yaml
# config/packages/msgphp_user.yaml

msgphp_user:
    id_type_mapping:
        MsgPhp\User\UserIdInterface: bigint
    class_mapping:
        MsgPhp\User\UserIdInterface: App\Entity\User\UserId
```

### Using a UUID identifier

```yaml
# config/packages/msgphp_user.yaml

msgphp_user:
    id_type_mapping:
        MsgPhp\User\UserIdInterface: uuid # uuid_binary, uuid_binary_ordered_time
```

This changes the default implementation to `MsgPhp\User\Infra\Uuid\UserId`, a sub class of the default [UUID domain identifier](../../infrastructure/uuid.md#domain-identifier).

[Composer]: https://getcomposer.org
[Symfony Flex]: https://symfony.com/doc/current/setup/flex.html
[recipe configuration]: https://github.com/symfony/recipes-contrib/tree/master/msgphp/user-bundle
