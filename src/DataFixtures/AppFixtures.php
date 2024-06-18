<?php

namespace App\DataFixtures;

use App\Entity\Note;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher; 
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un uer 'normal'
        $user = new User();
        $user->setUsername('normalUser');
        $user->setEmail('user@gmail.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        // Création d'un uer 'admin'
        $userAdmin = new User();
        $userAdmin->setUsername('adminUser');
        $userAdmin->setEmail('admin@gmail.com');
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, 'password'));
        $manager->persist($userAdmin);

        for ($i = 0; $i < 20; $i++) {
            $note = new Note();
            $note->setTitle("Titre " . $i);
            $note->setContent("Content " . $i);
            $note->setUser($user);
            $manager->persist($note);
        }

        $manager->flush();
    }
}
