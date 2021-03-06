<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\ChangeUserCredential;
use MsgPhp\User\Event\Domain\ChangeCredential;
use MsgPhp\User\Event\UserCredentialChanged;
use MsgPhp\User\Repository\UserRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserCredentialHandler
{
    use EventSourcingCommandHandlerTrait;
    use MessageDispatchingTrait;

    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(ChangeUserCredential $command): void
    {
        $fields = $command->fields;
        $user = $this->repository->find($command->userId);
        $oldCredential = $user->getCredential();

        if ($this->handleEvent($user, $this->factory->create(ChangeCredential::class, compact('fields')))) {
            $this->repository->save($user);
            $this->dispatch(UserCredentialChanged::class, compact('user', 'oldCredential'));
        }
    }
}
