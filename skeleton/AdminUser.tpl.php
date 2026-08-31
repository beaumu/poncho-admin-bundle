<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $repository->getFullName() ?>;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Poncho\AdminBundle\Entity\BaseAdminUser;

#[ORM\Entity(repositoryClass: <?= $repository->getShortName() ?>::class)]
#[UniqueEntity('email')]
class <?= $class_name ?> extends BaseAdminUser
{
    /**
     * 
     */
    public function getRoles(): array
    {
        return ['ROLE_ADMIN'];
    }
}
