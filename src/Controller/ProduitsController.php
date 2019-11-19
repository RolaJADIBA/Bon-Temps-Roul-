<?php

namespace App\Controller;

use App\Entity\Produits;
use App\Form\ProduitsType;
use App\Repository\ProduitsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/admin")
 * @IsGranted("ROLE_ADMIN")
 */
class ProduitsController extends AbstractController
{
    /**
     * @Route("/produits", name="produits_index", methods={"GET"})
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        // Sélectionner tous les articles
        $requete = $this->getDoctrine()->getRepository(Produits::class)->findAll();

        $produit = $paginator->paginate(
            $requete,
            $request->query->getInt('page', 1),
            10
        );
        
        
        return $this->render('admin/produits/index.html.twig', [
            'produits' => $produit,
        ]);
    }

    /**
     * @Route("/produits/ajout", name="produits_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $produit = new Produits();

        $lists = [];

        $form = $this->createForm(ProduitsType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload un Image

                $images = $form->get('images')->getData();

                foreach($images as $image){

                    $nouveau_nom = md5(uniqid()).'.'.$image->guessExtension();

                    //Récupère les informations du fichier
                    $image->move(
                        $this->getParameter('image_produits'),
                        $nouveau_nom
                    );
                    $lists[] = $nouveau_nom;
                }
                $produit->setImages($lists);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('produits_index');
        }

        return $this->render('admin/produits/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/produits/{id}", name="produits_show", methods={"GET"})
     */
    public function show( Produits $produit): Response
    {

        return $this->render('admin/produits/show.html.twig', [
            'produits' => $produit,
        ]);
    }

    /**
     * @Route("/produits/edit/{id}", name="produits_edit")
     */
    public function edit(Request $request, Produits $produit): Response
    {
        $ancienne_photos = [];
        $ancienne_photos = $produit->getImages();
        
        $form = $this->createForm(ProduitsType::class, $produit);
        $form->handleRequest($request);
        $lists = [];

        if ($form->isSubmitted() && $form->isValid()) {

            // Si getImage() est non null, on modifie la photo
            if($produit->getImages()) {
                
                // Supprime l'ancienne photo
                $filesystem= new Filesystem();

                foreach($ancienne_photos as $ancienne_photo){
                $filesystem->remove('img/produits/'. $ancienne_photo);
                }

                $images = $form->get('images')->getData();

                foreach($images as $image){

                $nouveau_nom = md5(uniqid()) .'.'. $image->guessExtension();

                $image->move(
                    $this->getParameter('image_produits'),
                    $nouveau_nom
                );
                $lists[] = $nouveau_nom;
                $produit->setImages($lists);
            }

            } 

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('produits_index');
        }

        return $this->render('admin/produits/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/produits/{id}/supprimer", name="produits_delete")
     */
    public function suppprimerProduit(Produits $id, Produits $produit)
    {
        $ancienne_photos = [];
        $ancienne_photos = $produit->getImages();

        // Supprime la photo du serveur si getImage() n'est pas NULL
        if($id->getImages()){
            
            $filesystem = new Filesystem();

            foreach($ancienne_photos as $ancienne_photo){

            $filesystem->remove('img/produits/'. $ancienne_photo);

            }       
        }
        //Si $id n'est pas vide, on supprime la catégorie
        if($id){

            // Supprime lèarticle en BDD
           $doctrine = $this->getDoctrine()->getManager();
           $doctrine->remove($id);
           $doctrine->flush();
           
           $this->addFlash('success','Le produit est correctement supprimée');
        }
        else{
            $this->addFlash('error', "Le produit n'existe pas");
        }
        // Retour sur le tableau listant les catégories
        return $this->redirectToRoute('produits_index');
    }
}