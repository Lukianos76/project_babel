<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\PasswordResetToken;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class PasswordResetService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenGeneratorInterface $tokenGenerator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $frontendUrl
    ) {
    }

    public function requestReset(string $email): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        
        if (!$user) {
            $this->logger->warning('Password reset requested for non-existent email', ['email' => $email]);
            return false;
        }

        // Create a new password reset token
        $resetToken = new PasswordResetToken();
        $resetToken->setUser($user);
        $resetToken->setExpiresAt(new \DateTimeImmutable('+1 hour'));

        $this->entityManager->persist($resetToken);
        $this->entityManager->flush();

        $this->sendResetEmail($user, $resetToken->getToken());

        $this->logger->info('Password reset email sent', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail()
        ]);

        return true;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $resetToken = $this->entityManager->getRepository(PasswordResetToken::class)->findValidToken($token);

        if (!$resetToken) {
            $this->logger->warning('Invalid password reset token used', ['token' => substr($token, 0, 8) . '...']);
            return false;
        }

        if (!$resetToken->isValid()) {
            $this->logger->warning('Expired or used password reset token', [
                'user_id' => $resetToken->getUser()->getId(),
                'email' => $resetToken->getUser()->getEmail()
            ]);
            return false;
        }

        $user = $resetToken->getUser();
        $user->setPassword($newPassword);
        $resetToken->setUsed(true);

        $this->entityManager->flush();

        $this->logger->info('Password reset successful', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail()
        ]);

        return true;
    }

    private function sendResetEmail(User $user, string $token): void
    {
        $resetUrl = sprintf(
            '%s/reset-password?token=%s',
            rtrim($this->frontendUrl, '/'),
            $token
        );

        $email = (new Email())
            ->from('noreply@project-babel.com')
            ->to($user->getEmail())
            ->subject('Password reset')
            ->html(sprintf(
                '<p>Hello %s,</p>
                <p>You have requested to reset your password. Click the link below to proceed:</p>
                <p><a href="%s">Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request this reset, please ignore this email.</p>
                <p>Best regards,<br>Project Babel Team</p>',
                htmlspecialchars($user->getEmail()),
                htmlspecialchars($resetUrl)
            ));

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send password reset email', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 