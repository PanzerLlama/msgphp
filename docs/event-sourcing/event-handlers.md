# Event Handlers

A domain event handler is bound to `MsgPhp\Domain\Event\DomainEventHandler`. Its purpose is to implement the handling of
[domain events](events.md) within a certain context.

Usually an entity implements it in order to mutate its own state (i.e. self-handling). It enforces the entity state to
be valid by design as it encapsulates all write operations.

In practice domain events can be recorded on trigger, or privately within the implementation. It allows to re-play them
at any point in time afterwards. Consider this a design choice to be made upfront, e.g. in case your entity design
requires its history to be accessible.

## API

### `handleEvent(DomainEvent $event): bool`

Handles the given domain event for a known subject. A boolean return value tells if the domain event is actually handled
yes or no.

## Implementations

### `MsgPhp\Domain\Event\DomainEventHandlerTrait`

A utility trait implementing the event handler API. By convention it maps events to methods named `on<EventClassName>Event()`.

#### Basic Example

```php
<?php

use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;

// --- SETUP ---

class ChangeValue implements DomainEvent
{
    public $newValue;
    
    public function __construct($value)
    {
        $this->newValue = $value;
    }
}

class MyEntity implements DomainEventHandler
{
    use DomainEventHandlerTrait;
    
    public $value;
    
    private function onChangeValueEvent(ChangeValue $event): bool
    {
        if ($this->value === $event->newValue) {
            return false;
        }

        $this->value = $event->newValue;

        return true;
    }
    
}

// --- USAGE ---

$entity = new MyEntity();

if ($entity->handleEvent(new ChangeValue('new value'))) {
    // do something
}
```
