<?php

namespace Poncho\AdminBundle\Entity;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class BaseAdminUser implements EquatableInterface, \Serializable, UserInterface, PasswordAuthenticatedUserInterface
{
    public ?int $id = null;

    public \DateTimeInterface $createdAt;

    public bool $active = true;

    public ?string $firstname = null;

    public ?string $lastname = null;

    public ?string $password = null;

    /**
     * Used only by form
     */
    public ?string $plainPassword = null;

    public ?string $email = null;

    public ?string $passwordResetToken = null;

    public ?string $passwordResetSelector = null;

    public ?\DateTimeInterface $passwordResetRequestedAt = null;

    public ?\DateTimeInterface $passwordResetExpiresAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getFullName(): string
    {
        return \sprintf('%s %s', $this->firstname, $this->lastname);
    }

    public function isPasswordResetExpired(): bool
    {
        // No reset in progress means there is nothing valid to honour.
        if (null === $this->passwordResetExpiresAt) {
            return true;
        }

        return $this->passwordResetExpiresAt->getTimestamp() <= time();
    }

    public function erasePasswordReset(): void
    {
        $this->passwordResetToken = null;
        $this->passwordResetSelector = null;
        $this->passwordResetExpiresAt = null;
    }

    // Equatable implementation

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        if ($this->getPassword() !== $user->getPassword()) {
            return false;
        }

        if ($this->getUserIdentifier() !== $user->getUserIdentifier()) {
            return false;
        }

        return true;
    }

    // Serializable implementation

    public function __serialize(): array
    {
        return [
            $this->id,
            $this->password,
            $this->email
        ];
    }

    final public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    public function __unserialize(array $data): void
    {
        [$this->id, $this->password, $this->email] = $data;
    }

    final public function unserialize(string $data): void
    {
        $this->__unserialize(unserialize($data));
    }

    // UserInterface implementation

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Clears the plain-text password once it has been hashed.
     *
     * plainPassword is never serialized (see __serialize()), so it cannot leak
     * into the session; this only drops it from the in-memory object.
     */
    public function erasePlainPassword(): void
    {
        $this->plainPassword = null;
    }

    /**
     * Symfony 7.3+ no longer calls this method when it carries #[\Deprecated],
     * which is the signal that its logic lives elsewhere (here: __serialize()
     * and erasePlainPassword()). Symfony 6.4 still calls it after login, so it
     * keeps clearing the plain password rather than becoming a no-op.
     *
     * @deprecated since poncho/admin-bundle 1.2, use erasePlainPassword() instead
     */
    #[\Deprecated(message: 'use erasePlainPassword() instead', since: 'poncho/admin-bundle 1.2')]
    public function eraseCredentials(): void
    {
        $this->erasePlainPassword();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /*
     * Keep for backward compatibility with Symfony 5.3
     */
    final public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    // Std implementation

    public function __toString(): string
    {
        return $this->getUserIdentifier();
    }
}
