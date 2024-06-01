<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use HTMLPurifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    #[Route('/', name: 'home.index')]
    public function index(NoteRepository $noteRepo): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->render('home/index.html.twig', [
                'title' => 'Home'
            ]);
        }

        $notes = $noteRepo->findNotesByUser($user);

        return $this->render('home/index.html.twig', [
            'title' => 'Home',
            'notes' => $notes,
            'user' => $user
        ]);
    }
}
