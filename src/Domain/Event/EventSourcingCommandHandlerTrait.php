<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Event;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EventSourcingCommandHandlerTrait
{
    /**
     * @param object $target
     */
    private function handleEvent($target, DomainEvent $event): bool
    {
        if (!$target instanceof DomainEventHandler) {
            throw new \LogicException('Event target "'.\get_class($target).'" must be an instance of "'.DomainEventHandler::class.'" to handle event "'.\get_class($event).'".');
        }

        return $target->handleEvent($event);
    }
}
