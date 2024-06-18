<?php

namespace App\Controller\API;

use App\Entity\Note;
use App\Entity\User;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class NoteApiController extends AbstractController
{
    #[Route('api/notes', name: 'notes', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getAllNotes(NoteRepository $noteRepo, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllNotes-" . $page . "-" . $limit;

        $jsonNotes = $cache->get($idCache, function (ItemInterface $item) use ($noteRepo, $page, $limit, $serializer) {
            echo ("L'ELEMENT N'EST PAS ENCORE EN CACHE !\n");
            $item->tag("notesCache");
            $noteList = $noteRepo->findAllWithPagination($page, $limit);
            return $serializer->serialize($noteList, 'json', ['groups' => ['notes.show']]);
        });

        return new JsonResponse($jsonNotes, Response::HTTP_OK, [], true);
    }

    #[Route('/api/notes/{id}', name: 'detailNote', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getDetailNote(int $id, SerializerInterface $serializer, NoteRepository $noteRepo)
    {
        $note = $noteRepo->find($id);
        if ($note) {
            $jsonNote = $serializer->serialize($note, 'json', ['groups' => ['notes.show']]);
            return new JsonResponse($jsonNote, Response::HTTP_OK, ['accept' => 'json'], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/notes/{id}', name: 'deleteNote', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteNote(int $id, EntityManagerInterface $em, NoteRepository $noteRepo, TagAwareCacheInterface $cache)
    {
        $note = $noteRepo->find($id);
        if ($note) {
            $cache->invalidateTags(['notesCache']);
            $em->remove($note);
            $em->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/notes', name: 'createNote', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createNote(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Trouver l'utilisateur par son ID
        $user = $em->getRepository(User::class)->find($data['user']['id']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $note = $serializer->deserialize($request->getContent(), Note::class, 'json');

        $errors = $validator->validate($note);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'),  JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $note->setUser($user);

        $em->persist($note);
        $em->flush();

        $jsonNote = $serializer->serialize($note, 'json', ['groups' => ['note.show']]);

        $location = $urlGenerator->generate('detailNote', ['id' => $note->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonNote, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/notes/{id}', name: 'updateNote', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function updateNote(int $id, Request $request, EntityManagerInterface $em, Note $note, SerializerInterface $serializer, NoteRepository $noteRepo)
    {
        // Trouver la note par son ID
        $note = $em->getRepository(Note::class)->find($id);
        if (!$note) {
            return new JsonResponse(['error' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }

        // Désérialiser les données de la requête dans l'objet Note existant
        $serializer->deserialize($request->getContent(), Note::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $note]);

        // Décoder les données de la requête pour mettre à jour l'utilisateur
        $data = json_decode($request->getContent(), true);
        if (isset($data['user']['id'])) {
            $user = $em->getRepository(User::class)->find($data['user']['id']);
            if ($user) {
                $note->setUser($user);
            }
        }
        $content = $request->toArray();

        // Persister et flusher les modifications
        $em->persist($note);
        $em->flush();

        // Sérialiser la note pour la réponse
        $jsonNote = $serializer->serialize($note, 'json', ['groups' => ['note.show']]);

        return new JsonResponse($jsonNote, Response::HTTP_NO_CONTENT);
    }
}
