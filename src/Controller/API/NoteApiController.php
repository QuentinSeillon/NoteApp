<?php

namespace App\Controller\API;

use App\Repository\NoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class NoteApiController extends AbstractController
{
    #[Route('api/notes')]
    public function index(NoteRepository $noteRepo)
    {
        $notes = $noteRepo->findAll();
        return $this->json($notes, 200, [], [
            'groups' => ['notes.show']
        ]);
    }
}
