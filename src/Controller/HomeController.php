<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home.index')]
    public function index(Request $request, NoteRepository $noteRepo, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        // $user = new User();
        // $user->setEmail('test@gmail.com')->setUsername('Jhon')->setPassword($hasher->hashPassword($user, 'password'))->setRoles([]);
        // $em->persist($user);
        // $em->flush();

        $notes = $noteRepo->findAll();
        return $this->render('home/index.html.twig', [
            'title' => 'Home',
            'notes' => $notes
        ]);
    }
}
