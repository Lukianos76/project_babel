<?php

namespace App\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\User\UserProviderInterface;

#[AsCommand(
    name: 'app:verify-jwt',
    description: 'Verify a JWT token',
)]
class VerifyJwtCommand extends Command
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private UserProviderInterface $userProvider
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('token', InputArgument::REQUIRED, 'The JWT token to verify')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $token = $input->getArgument('token');

        try {
            $decodedToken = json_decode(base64_decode(explode('.', $token)[1]), true);
            $email = $decodedToken['email'] ?? null;
            
            if (!$email) {
                throw new \Exception('No email found in token');
            }

            $user = $this->userProvider->loadUserByIdentifier($email);
            $payload = $this->jwtManager->parse($token);

            $io->success('Token is valid!');
            $io->section('User:');
            $io->writeln(sprintf('Email: %s', $user->getUserIdentifier()));
            $io->writeln(sprintf('Roles: %s', implode(', ', $user->getRoles())));
            $io->section('Payload:');
            $io->writeln(json_encode($payload, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Token is invalid: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 