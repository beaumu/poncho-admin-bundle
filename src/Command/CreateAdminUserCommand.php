<?php

namespace Poncho\AdminBundle\Command;

use Poncho\AdminBundle\Service\UserManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('poncho_admin:create:user', 'Create a new admin user.')]
class CreateAdminUserCommand extends Command
{
    private ?SymfonyStyle $io = null;

    public function __construct(private readonly UserManagerInterface $userManager)
    {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $u = $this->userManager->create();

        $u->firstname = $this->io->askQuestion(new Question('Firstname'));
        $u->lastname = $this->io->askQuestion(new Question('Lastname'));
        $u->email = $this->io->askQuestion(new Question('Email (must be unique)'));
        $u->plainPassword = $this->io->askHidden('Password');

        $this->userManager->save($u);

        $this->io->success('User created');

        return self::SUCCESS;
    }
}
