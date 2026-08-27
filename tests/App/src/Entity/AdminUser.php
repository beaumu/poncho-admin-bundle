<?php

namespace Poncho\AdminBundle\Tests\App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Poncho\AdminBundle\Entity\BaseAdminUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[UniqueEntity('email')]
class AdminUser extends BaseAdminUser
{
    public function getRoles(): array
    {
        return ['ROLE_ADMIN'];
    }
}
