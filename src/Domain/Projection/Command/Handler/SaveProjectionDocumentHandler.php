<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\Domain\Projection\Command\SaveProjectionDocument;
use MsgPhp\Domain\Projection\Event\ProjectionDocumentSaved;
use MsgPhp\Domain\Projection\ProjectionDocument;
use MsgPhp\Domain\Projection\ProjectionRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SaveProjectionDocumentHandler
{
    use MessageDispatchingTrait;

    /**
     * @var ProjectionRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, ProjectionRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(SaveProjectionDocument $command): void
    {
        $document = new ProjectionDocument($command->type, $command->id, $command->body);

        $this->repository->save($document);
        $this->dispatch(ProjectionDocumentSaved::class, compact('document'));
    }
}
