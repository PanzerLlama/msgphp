<?php

declare(strict_types=1);

namespace MsgPhp\Eav\Command\Handler;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\Eav\Command\DeleteAttribute;
use MsgPhp\Eav\Event\AttributeDeleted;
use MsgPhp\Eav\Repository\AttributeRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteAttributeHandler
{
    use MessageDispatchingTrait;

    /**
     * @var AttributeRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, AttributeRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(DeleteAttribute $command): void
    {
        try {
            $attribute = $this->repository->find($command->attributeId);
        } catch (EntityNotFoundException $e) {
            return;
        }

        $this->repository->delete($attribute);
        $this->dispatch(AttributeDeleted::class, compact('attribute'));
    }
}
