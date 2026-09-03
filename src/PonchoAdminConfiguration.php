<?php

namespace Poncho\AdminBundle;

use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

class PonchoAdminConfiguration
{
    public function __construct(
        private readonly array $config,
        private readonly LogoutUrlGenerator $logoutUrlGenerator
    ) {
    }

    // Theme

    public function containerClass(): string
    {
        return $this->config['container_class'];
    }

    public function appName(): ?string
    {
        return $this->config['app_name'];
    }

    public function appLogo(): ?string
    {
        return $this->config['app_logo'];
    }

    /**
     * Logo variant for dark surfaces such as the sidebar. Falls back to appLogo().
     */
    public function appLogoInverse(): ?string
    {
        return $this->config['app_logo_inverse'] ?? $this->config['app_logo'];
    }

    // Menu

    public function menuName(): string
    {
        return $this->config['menu'];
    }

    // Security

    public function logoutPath(): ?string
    {
        return $this->logoutUrlGenerator->getLogoutPath();
    }

    // User

    public function userEnable(): bool
    {
        return $this->config['user']['enabled'];
    }

    public function userClass(): string
    {
        return $this->config['user']['class'];
    }

    public function userTable(): string
    {
        return $this->config['user']['table'];
    }

    public function userForm(): string
    {
        return $this->config['user']['form'];
    }

    public function userPasswordResetEmailAddress(): Address
    {
        return new Address($this->config['user']['password_reset_from_email'], $this->config['user']['password_reset_from_name'] ?? '');
    }

    public function userPasswordResetTtl(): int
    {
        return $this->config['user']['password_reset_ttl'];
    }

    public function userProfileEnable(): bool
    {
        return $this->config['user']['profile']['enabled'];
    }

    public function userProfileRoute(): string
    {
        return $this->config['user']['profile']['route'];
    }

    public function userProfileForm(): string
    {
        return $this->config['user']['profile']['form'];
    }

    // Notification

    public function notificationEnable(): bool
    {
        return $this->config['notification']['enabled'];
    }

    public function notificationPollInterval(): int
    {
        return $this->config['notification']['poll_interval'];
    }

    public function getBootstrapFormLayout(): string
    {
        return $this->config['form']['layout'];
    }
}
