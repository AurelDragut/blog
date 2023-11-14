<?php

namespace App\Controller;

use App\Entity\Blogpost;
use App\Repository\BlogpostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PagesController extends AbstractController
{
    /**
     * @param BlogpostRepository $blogpostRepository
     * @return Response
     */
    #[Route('/', name: 'app_homepage')]
    public function index(BlogpostRepository $blogpostRepository): Response
    {
        $blogposts = $blogpostRepository->findAll();
        return $this->render('pages/index.html.twig', [
            'blogposts' => $blogposts,
        ]);
    }
}
