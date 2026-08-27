<?php

namespace Poncho\AdminBundle\Service;

use Poncho\AdminBundle\Entity\BaseAdminUser;
use Poncho\AdminBundle\Exception\ResetPasswordException;

interface UserManagerInterface
{
    public function create(): BaseAdminUser;

    public function find(int $id): ?BaseAdminUser;

    public function updatePassword(BaseAdminUser $user): void;

    public function delete(BaseAdminUser $user): void;

    public function save(BaseAdminUser $user): void;

    /**
     * @throws ResetPasswordException
     */
    public function sendResetPasswordEmail(string $email): void;

    /**
     * @throws ResetPasswordException
     */
    public function validateResetPasswordTokenAndFetchUser(string $token): BaseAdminUser;
}
