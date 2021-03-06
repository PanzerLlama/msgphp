<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\User\Command\DisableUser;
use MsgPhp\User\Event\UserDisabled;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DisableUserCommand extends UserCommand
{
    protected static $defaultName = 'user:disable';

    /**
     * @var StyleInterface
     */
    private $io;

    public function onMessageReceived($message): void
    {
        if ($message instanceof UserDisabled) {
            $this->io->success('Disabled user '.self::getUsername($message->user));
        }
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Disable a user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $userId = $this->getUser($input, $this->io)->getId();

        $this->dispatch(DisableUser::class, compact('userId'));

        return 0;
    }
}
