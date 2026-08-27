<?php

namespace Poncho\AdminBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Poncho\AdminBundle\Entity\BaseAdminUser;
use Poncho\AdminBundle\Exception\ResetPasswordException;
use Poncho\AdminBundle\PonchoAdminConfiguration;

class UserManager implements UserManagerInterface
{
    private const SELECTOR_LENGTH = 24;

    protected readonly string $class;
    protected readonly ObjectRepository $repo;

    public function __construct(
        protected readonly TranslatorInterface $translator,
        protected readonly EntityManagerInterface $em,
        protected readonly MailerInterface $mailer,
        protected readonly UserPasswordHasherInterface $passwordHasher,
        protected readonly PonchoAdminConfiguration $config
    ) {
        $this->class = $config->userClass();
        $this->repo = $this->em->getRepository($this->class);
    }

    public function create(): BaseAdminUser
    {
        return new $this->class();
    }

    public function find(int $id): ?BaseAdminUser
    {
        return $this->repo->find($id);
    }

    public function delete(BaseAdminUser $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    public function updatePassword(BaseAdminUser $user): void
    {
        if (!empty($user->plainPassword)) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->plainPassword));

            $user->eraseCredentials();
            $user->erasePasswordReset();
        }
    }

    public function save(BaseAdminUser $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function sendResetPasswordEmail(string $email): void
    {
        /** @var BaseAdminUser|null $user */
        $user = $this->repo->findOneBy(['email' => $email, 'active' => true]);

        // no active user for this email
        if (null === $user) {
            throw new ResetPasswordException('user_not_found');
        }

        // generate secret token
        $token = random_bytes(16); // 32 char on hex

        // generate a selector to identify resource
        $selector = bin2hex(random_bytes(12)); // 24 char

        $user->passwordResetSelector = $selector;
        $user->passwordResetToken = hash('sha256', $token); // hash token on db to avoid expose secret reset token
        $user->passwordResetRequestedAt = new \DateTimeImmutable();
        $user->passwordResetExpiresAt = new \DateTimeImmutable(\sprintf('+%d seconds', $this->config->userPasswordResetTtl()));

        $this->save($user);

        // send email
        $publicToken = $selector . bin2hex($token); //  56 char

        $email = new TemplatedEmail();
        $email
            ->subject($this->translator->trans('password_resetting.email.subject', [], 'PonchoAdmin'))
            ->from($this->config->userPasswordResetEmailAddress())
            ->to($user->email)
            ->htmlTemplate('@PonchoAdmin/email/password_reset.html.twig')
            ->context([
                'user' => $user,
                'token' => $publicToken
            ]);

        $this->mailer->send($email);
    }

    public function validateResetPasswordTokenAndFetchUser(string $token): BaseAdminUser
    {
        // decode token
        if (\strlen($token) <= self::SELECTOR_LENGTH) {
            throw new ResetPasswordException('invalid_password_reset_token');
        }

        $selector = substr($token, 0, self::SELECTOR_LENGTH);
        $token = hex2bin(substr($token, self::SELECTOR_LENGTH));

        if (false === $token) {
            throw new ResetPasswordException('invalid_password_reset_token');
        }

        // find user with selector
        /** @var BaseAdminUser|null $user */
        $user = $this->repo->findOneBy(['passwordResetSelector' => $selector, 'active' => true]);
        if (null === $user) {
            throw new ResetPasswordException('invalid_password_reset_token');
        }

        // compare hash
        $expected = hash('sha256', $token);
        if (!hash_equals($expected, $user->passwordResetToken)) {
            throw new ResetPasswordException('invalid_password_reset_token');
        }

        // check password reset is not expired
        if ($user->isPasswordResetExpired()) {
            throw new ResetPasswordException('expired_password_reset_token');
        }

        // then return user !
        return $user;
    }
}
