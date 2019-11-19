<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Produits;
use App\Repository\ArticlesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    // /**
    //  * @Route("/blog", name="blog")
    //  */
    // public function index()
    // {
    //     return $this->render('blog/index.html.twig', [
    //         'controller_name' => 'BlogController',
    //     ]);
    // }

    /**
     * @Route("/a_propos", name="a_propos")
     */
    public function about()
    {
        return $this->render('blog/about.html.twig');
    }

     /**
     * @Route("/", name="accueil_blog")
     */
    public function accueil()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $article = $entityManager->getRepository(Articles::class)->findAll();
        $produit = $entityManager->getRepository(Produits::class)->findAll();

        return $this->render('blog/accueil.html.twig', [
            'articles' => $article,
            'produits' => $produit,
        ]);
    }

    /**
     * @Route("/blog", name="blog", methods={"GET"})
     */
    public function index(ArticlesRepository $articlesRepository)
    {
        return $this->render('blog/index.html.twig', [
            'articles' => $articlesRepository->findAll(),
        ]);
    }

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Articles $article)
    {
        return $this->render('blog/blog_show.html.twig', [
            'article' => $article,
        ]);
    }

    

}
