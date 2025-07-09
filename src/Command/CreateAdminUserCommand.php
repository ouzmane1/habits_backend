<?php

namespace App\Command;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un utilisateur avec le rôle ROLE_ADMIN',
)]
class CreateAdminUserCommand extends Command
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $io->title('Création d\'un administrateur');

        // Demande l’email
        $questionEmail = new Question('Email de l\'admin : ');
        $email = $helper->ask($input, $output, $questionEmail);

        // Demande le nom
        $questionName = new Question('Nom complet : ');
        $name = $helper->ask($input, $output, $questionName);

        // Demander le mot de passe
        $questionPassword = new Question('Mot de passe : ');
        $questionPassword->setHidden(true);
        $questionPassword->setHiddenFallback(false);
        $plainPassword = $helper->ask($input, $output, $questionPassword);

        // Crée l'utilisateur
        $admin = new Users();
        $admin->setEmail($email);
        $admin->setName($name);
        $admin->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($admin, $plainPassword);
        $admin->setPassword($hashedPassword);

        $this->em->persist($admin);
        $this->em->flush();

        $io->success("Administrateur {$email} créé avec succès.");

        return Command::SUCCESS;
    }

    // php bin/console app:create-admin
}
