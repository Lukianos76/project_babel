<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-users',
    description: 'List all users in the database',
)]
class ListUsersCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $users = $this->userRepository->findAll();

        if (empty($users)) {
            $io->warning('No users found in the database.');
            return Command::SUCCESS;
        }

        $io->success(sprintf('Found %d users:', count($users)));
        
        foreach ($users as $user) {
            $io->text(sprintf(
                '- %s (roles: %s)',
                $user->getEmail(),
                implode(', ', $user->getRoles())
            ));
        }

        return Command::SUCCESS;
    }
} 