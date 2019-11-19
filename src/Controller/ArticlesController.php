<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticlesType;
use App\Repository\ArticlesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/admin")
 * @IsGranted("ROLE_ADMIN")
 */
class ArticlesController extends AbstractController
{
    /**
     * @Route("/articles", name="articles_index", methods={"GET"})
     */
    public function index(ArticlesRepository $articlesRepository): Response
    {
        return $this->render('admin/articles/index.html.twig', [
            'articles' => $articlesRepository->findAll(),
        ]);
    }

    /**
     * @Route("/articles/ajout", name="articles_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {

        $article = new Articles();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload un Image
            if($article->getImage())
            {
                $image = $form->get('image')->getData();
                $nouveau_nom = md5(uniqid()).'.'.$image->guessExtension();

                //Récupère les informations du fichier
                $image->move(
                    $this->getParameter('image_articles'),
                    $nouveau_nom
                );
                $article->setImage($nouveau_nom);
            }
            

            $article->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('articles_index');
        }

        return $this->render('admin/articles/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/articles/{id}", name="articles_show", methods={"GET"})
     */
    public function show(Articles $article): Response
    {
        return $this->render('admin/articles/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/articles/edit/{id}", name="articles_edit")
     */
    public function edit(Request $request, Articles $article): Response
    {

        $ancienne_photo = $article->getImage();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // Si getImage() est non null, on modifie la photo
            if($article->getImage()) {
                
                // Supprime l'ancienne photo
                $filesystem = new Filesystem();
                $filesystem->remove('img/articles/'. $ancienne_photo);

                $image = $form->get('image')->getData();
                $nouveau_nom = md5(uniqid()) .'.'. $image->guessExtension();

                $image->move(
                    $this->getParameter('image_articles'),
                    $nouveau_nom
                );

                $article->setImage($nouveau_nom);

            } else {
                // Si l'image est pas modifier
                $article->setImage($ancienne_photo);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('articles_index');
        }

        return $this->render('admin/articles/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
        
    }
    
    /**
     * @Route("/articles/{id}/supprimer", name="articles_delete")
     */
    public function delete(Articles $article)
    {
        if($article) {

            // Vérifie si il y a une image et la supprime de la bdd et du dossier ou est stocker l'image
            if($article->getImage()) {
                $filesystem =  new Filesystem();
                $filesystem->remove('img/articles/'. $article->getImage());
            }

            $doctrine = $this->getDoctrine()->getManager();
            $doctrine->remove($article);
            $doctrine->flush();
        }

        return $this->redirectToRoute('articles_index');
    }

    /**
     * @Route("/accueil", name="accueil_admin")
     */
    public function admin()
    {
        return $this->render('admin/accueil/index.html.twig');
    }
}
