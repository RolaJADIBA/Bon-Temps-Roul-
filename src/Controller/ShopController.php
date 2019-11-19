<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produits;
use App\Entity\PanierProduits;
use App\Repository\ProduitsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        // $this->session->remove('panier');

        if(!$this->session->get('panier')) {
            $this->session->set('panier', []);        
        }
    }

    /**
     * @Route("/shop/ajout/produit/{id}", name="ajoutPanier")
     */
    public function ajouterPanier($id, Request $request)
    {
        // Récupère le panier
        $panier = $this->session->get('panier');
    
        // $id = $request->request->get('id');
        // $panier[ID DU PRODUIT] => QUANTITE
        // Si la clès qui correspond à l'id du produit récupérer dans le formulaire existe alors, on récupère la quantité provenant du formulaire pour cette clés si il n'a de quantité rentré alors on fait en sorte que celle-ci soit automatiquement à 1
        if(array_key_exists($id, $panier)) {
            if($request->request->get('quantite') != null) {
                $panier[$id] = $request->request->get('quantite');
            }
        }
        else {
            $panier[$id] = $request->request->get('quantite') != null ? $request->request->get('quantite') : 1;
        }

        // Insère les nouvelles données dans le panier
        $this->session->set('panier', $panier);

        return $this->redirectToRoute('shop');

    }

    /**
     * @Route("/shop", name="shop")
     */
    public function index(Request $request)
    {

        $id = $request->request->get('id');        
        $panier = $this->session->get('panier');

        $produits = $this->getDoctrine()->getRepository(Produits::class)->findAll();
        $produits_panier = $this->getDoctrine()->getRepository(Produits::class)->findByArray(array_keys($panier));


        return $this->render('shop/index.html.twig', [
            'produits' => $produits,
            'produit_panier' => $produits_panier,
            'panier' => $panier
        ]);
    }

    /**
     * @Route("/shop/{id}", name="shop_show")
     */
    public function show_liste(Produits $produits)
    {
        return $this->render('shop/shop_show.html.twig', [
            'produits' => $produits,
        ]);
    }


    /**
     * @Route("/shop/panier/produit/delete/{id}", name="deleteProduitPanier")
     */
    public function deleteProduitPanier($id, Request $request)
    {

        // Récupère le panier
        $panier = $this->session->get('panier');

        // $id = $request->request->get('id');
        // $panier[ID DU PRODUIT] => QUANTITE
        if(array_key_exists($id, $panier)) {
            // if($request->request->get('quantite') != null) {
                $panier[$id] = $request->request->get('id');
                unset($panier[$id]);
                        //↑ Produit qu'on veut supprimé
            // }
        }

        // Insère les nouvelles données dans le panier
        $this->session->set('panier', $panier);
        // dd($panier);

        return $this->redirectToRoute('shop');

    }

    /**
     * @Route("/shop/afficher/panier", name="afficherPanier")
     */
    public function afficherPanier()
    {
        $panier = $this->session->get('panier');

        $produit_panier = $this->getDoctrine()->getRepository(Produits::class)->findByArray(array_keys($panier));

        return $this->render('shop/afficherPanier.html.twig', [
            'produit_panier' => $produit_panier,
            'panier' => $panier
        ]);
    }

    /**
     * @Route("/charge", name="stripe")
     */
    public function Stripe(Request $request)
    {
        // On récupère le total depuis le formulaire
        $total = $request->request->get('total');

        // Paramétrage de la clès API de STRIPE
        \Stripe\Stripe::setApiKey("sk_test_jLx93cRgmH1MgembEz0bB8lz00gXgJQOl3");

        // On créer une charge de paiement
        $charge = \Stripe\Charge::create([
          "amount" => $total * 100,
          "currency" => "eur",
          "source" => "tok_mastercard", // obtained with Stripe.js
          "description" => "paiement de ". $this->getUser()->getEmail(),
        ], [
        //   "idempotency_key" => "fp0unaRbAVdbpVf0",
        ]);

        return $this->redirectToRoute('ajoutPanierBdd', [
            'total' => $total
        ]);
        
    }

    /**
     * @Route("/shop/ajout/panier/{total}", name="ajoutPanierBdd")
     */
    public function ajoutPanier($total)
    {
        // On récupere la session panier
        $panier = $this->session->get('panier');

        // On créer un numéro de commande aléatoire
        $numero = rand ( 1, 50000 );

        //On récupère les produits dont l'id correspond aux clès du tableau session
        $produits = $this->getDoctrine()->getRepository(Produits::class)->findByArray(array_keys($panier));

        $entityManager = $this->getDoctrine()->getManager();

        //On créer l'objet panier, on set l'utilisateur, le montant total, son statut et son numéro de commande pour envoyé dans la base de données
        $panier1 = new Panier();
        $panier1->setUser($this->getUser());
        $panier1->setMontantTotal($total);
        $panier1->setStatut(0);
        $panier1->setNumeroCommande($numero);
        $entityManager->persist($panier1);

        // On boucle les produits récupérer, on créer un objet PanierProduit, on set le prix, la quantité, l'id du produit, l'id du panier et on envoi en base de données et on efface le panier en session.
        foreach($produits as $produit)
        {
            $panier_produit = new PanierProduits();
            $panier_produit->setPrix($produit->getPrix());
            $panier_produit->setQuantite($panier[$produit->getId()]);
            $panier_produit->setProduit($produit);
            $panier_produit->setPanier($panier1);
            
            $entityManager->persist($panier_produit);
        }

        $entityManager->flush();

        $this->session->remove('panier');

        $this->addFlash('paiement_success', 'Votre commande a bien été enregistré.');

        return $this->redirectToRoute('shop');
    }

}

/**
        \          Ah Shit,         /
         \                         /
          \       Here we go      /
           ]        again        [    ,'|
           ]                     [   /  |
           ]___               ___[ ,'   |
           ]  ]\             /[  [ |:   |
           ]  ] \           / [  [ |:   |
           ]  ]  ]         [  [  [ |:   |
           ]  ]  ]__     __[  [  [ |:   |
           ]  ]  ] ]\ _ /[ [  [  [ |:   |
           ]  ]  ] ] (#) [ [  [  [ :===='
           ]  ]  ]_].nHn.[_[  [  [
           ]  ]  ]  HHHHH. [  [  [
           ]  ] /   `HH("N  \ [  [
           ]__]/     HHH  "  \[__[
           ]         NNN         [
           ]         N/"         [
           ]         N H         [
          /          N            \
         /           q,            \
        /                           \

 */