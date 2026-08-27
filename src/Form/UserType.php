<?php

namespace Poncho\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Poncho\AdminBundle\Lib\Form\PasswordTogglableType;
use Poncho\AdminBundle\PonchoAdminConfiguration;

class UserType extends AbstractType
{
    public function __construct(private readonly PonchoAdminConfiguration $config)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('active', CheckboxType::class, [
            'label' => 'label.active',
            'translation_domain' => 'PonchoAdmin',
            'required' => false,
        ]);

        $builder->add('firstname', TextType::class, [
            'label' => 'label.firstname',
            'translation_domain' => 'PonchoAdmin'
        ]);

        $builder->add('lastname', TextType::class, [
            'label' => 'label.lastname',
            'translation_domain' => 'PonchoAdmin'
        ]);

        $builder->add('email', EmailType::class, [
            'label' => 'label.email',
            'translation_domain' => 'PonchoAdmin'
        ]);

        $params = [
            'label' => 'label.password',
            'translation_domain' => 'PonchoAdmin',
            'required' => $options['password_required'],
        ];

        if (!$options['password_required']) {
            $params['attr']['placeholder'] = 'message.leave_empty_to_keep_current_password';
        }

        $builder->add('plainPassword', PasswordTogglableType::class, $params);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->config->userClass(),
            'password_required' => false,
        ]);
    }
}
