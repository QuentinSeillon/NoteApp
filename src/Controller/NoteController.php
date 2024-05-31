<?php

namespace App\Controller;

use App\Entity\Note;
use App\Form\NoteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class NoteController extends AbstractController
{
    #[Route('/note/create', name: 'note.create')]
    public function noteCreate(Request $request, EntityManagerInterface $em)
    {
        $note = new Note();
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($note);
            $em->flush();
            $this->addFlash('success', 'La note a bien été ajouté');
            return $this->redirectToRoute('home.index');
        }
        return $this->render('note/create.html.twig', [
            'title' => 'Ajouter une note',
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'note.update', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function noteUpdate(Request $request,Note $note, EntityManagerInterface $em)
    {
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'La note a bien été modifiée');
            return $this->redirectToRoute('home.index');
        }
        return $this->render('note/update.html.twig', [
            'note' => $note,
            'form' => $form
        ]);
    }

    #[Route('{id}', name: 'note.delete', methods: ['DELETE'],  requirements: ['id' => Requirement::DIGITS])]
    public function delete(Note $note, EntityManagerInterface $em)
    {
        $em->remove($note);
        $em->flush();
        $this->addFlash('success', 'La note a bien été supprimée');
        return $this->redirectToRoute('home.index');
    }
}
