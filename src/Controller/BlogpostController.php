<?php

namespace App\Controller;

use App\Entity\Blogpost;
use App\Form\BlogpostType;
use App\Repository\BlogpostRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/blogpost')]
class BlogpostController extends AbstractController
{
    protected DateTimeImmutable $date;

    public function __construct() {
        $this->date = new DateTimeImmutable();
        $this->date->setDate(date('Y'),date('m'),date('d'));
        $this->date->setTime(date('H'),date('i'),date('s'));
    }

    /**
     * @param BlogpostRepository $blogpostRepository
     * @return Response
     */
    #[Route('/', name: 'app_blogpost_index', methods: ['GET'])]
    public function index(BlogpostRepository $blogpostRepository): Response
    {
        return $this->render('blogpost/index.html.twig', [
            'blogposts' => $blogpostRepository->findAll(),
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Security $security
     * @return Response
     */
    #[Route('/new', name: 'app_blogpost_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        /** Das Formular "Beitrag hinzufügen" wird erstellt */
        $blogpost = new Blogpost();
        $form = $this->createForm(BlogpostType::class, $blogpost);
        $form->handleRequest($request);

        /** wenn das Formular validiert und abgeschickt wurde, wird der Beitrag erstellt */
        if ($form->isSubmitted() && $form->isValid()) {
            $blogpost->setAuthor($security->getUser());
            $blogpost->setCreatedAt($this->date);
            $blogpost->setUpdatedAt($this->date);
            $entityManager->persist($blogpost);
            $entityManager->flush();

            return $this->redirectToRoute('app_blogpost_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blogpost/new.html.twig', [
            'blogpost' => $blogpost,
            'form' => $form,
        ]);
    }

    /**
     * @param Blogpost $blogpost
     * @return Response
     */
    #[Route('/{id}', name: 'app_blogpost_show', methods: ['GET'])]
    public function show(Blogpost $blogpost): Response
    {
        return $this->render('blogpost/show.html.twig', [
            'blogpost' => $blogpost,
        ]);
    }

    /**
     * @param Request $request
     * @param Blogpost $blogpost
     * @param EntityManagerInterface $entityManager
     * @param Security $security
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_blogpost_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Blogpost $blogpost, EntityManagerInterface $entityManager, Security $security): Response
    {
        /** prüfen, ob der eingeloggte Benutzer der Autor des Beitrags ist */
        if ($blogpost->getAuthor() !== $security->getUser()) {
            throw $this->createAccessDeniedException();
        }

        /** Post-Edit-Formular wird erstellt */
        $form = $this->createForm(BlogpostType::class, $blogpost);
        $form->handleRequest($request);

        /** wenn das Formular validiert und abgeschickt wurde, wird der Beitrag aktualisiert */
        if ($form->isSubmitted() && $form->isValid()) {
            $blogpost->setUpdatedAt($this->date);
            $entityManager->flush();

            return $this->redirectToRoute('app_blogpost_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blogpost/edit.html.twig', [
            'blogpost' => $blogpost,
            'form' => $form,
        ]);
    }

    /**
     * @param Request $request
     * @param Blogpost $blogpost
     * @param EntityManagerInterface $entityManager
     * @param Security $security
     * @return Response
     */
    #[Route('/{id}', name: 'app_blogpost_delete', methods: ['POST'])]
    public function delete(Request $request, Blogpost $blogpost, EntityManagerInterface $entityManager, Security $security): Response
    {
        /** prüfen, ob der eingeloggte Benutzer der Autor des Beitrags ist */
        if ($blogpost->getAuthor() !== $security->getUser()) {
            throw $this->createAccessDeniedException();
        }

        /** prüfen, ob das Token gültig ist, bevor der Beitrag gelöscht wird */
        if ($this->isCsrfTokenValid('delete'.$blogpost->getId(), $request->request->get('_token'))) {
            $entityManager->remove($blogpost);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_blogpost_index', [], Response::HTTP_SEE_OTHER);
    }
}
