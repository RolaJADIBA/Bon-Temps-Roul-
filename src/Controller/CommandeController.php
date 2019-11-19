<?php

namespace App\Controller;

use App\DataFixtures\Articles;
use App\Entity\Panier;
use App\Entity\PanierProduits;
use App\Entity\Produits;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Fpdf\Fpdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * @Route("/admin")
 * @IsGranted("ROLE_ADMIN")
 */
class CommandeController extends AbstractController
{
    /**
     * @Route("/commande", name="commande")
     */
    public function index()
    {

        $entityManager = $this->getDoctrine()->getManager();
        $panier = $entityManager->getRepository(Panier::class)->findAll();


        return $this->render('/admin/commande/index.html.twig', [
            'paniers' => $panier,
        ]);

    }

    /**
     * @Route("/commande/en/cours", name="commandesEnCours")
     */
    public function commandesEnCours()
    {

        $entityManager = $this->getDoctrine()->getManager();
        $panier = $entityManager->getRepository(Panier::class)->findAll();


        return $this->render('/admin/commande/commandesEnCours.html.twig', [
            'paniers' => $panier,
        ]);

    }

    /**
     * @Route("/commande/envoyer", name="commandesEnvoyer")
     */
    public function commandesEnvoyer()
    {

        $entityManager = $this->getDoctrine()->getManager();
        $panier = $entityManager->getRepository(Panier::class)->findAll();


        return $this->render('/admin/commande/commandesEnvoyer.html.twig', [
            'paniers' => $panier,
        ]);

    }



    /**
     * @Route("/commande/{id}", name="voirCommande")
     */
     public function voirCommande($id)
     {
 
         $entityManager = $this->getDoctrine()->getManager();
         $panier = $entityManager->getRepository(Panier::class)->find($id);
         $produitPaniers = $entityManager->getRepository(PanierProduits::class)->findBy(['panier' => $id]);


         return $this->render('/admin/commande/voir.html.twig', [
             'panier' => $panier,
             'produitPaniers' => $produitPaniers
         ]);
 
     }

    /**
     * @Route("/commande/modifier/statut/{id}/{statut}", name="modifierStatutCommande")
     */
    public function ModifierStatutCommande($id, $statut)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $panier = $entityManager->getRepository(Panier::class)->find($id);


        // En attente
        if($statut == '0') {
            $panier->setStatut(0);
        }

        // En préparation
        if($statut == '1') {
          $panier->setStatut(1);
        }

        // Envoyé
        if($statut == '2') {
            $panier->setStatut(2);
        }

        // Reçu
        if($statut == '3') {
            $panier->setStatut(3);
        }

        // Annuler
        if($statut == '4') {
            $panier->setStatut(4);
        }

        $entityManager->persist($panier);
        $entityManager->flush();

        return $this->redirectToRoute('voirCommande', [
            'id' => $id
        ]);

    }

    /**
     * @Route("/envoie/commande/{id}", name="envoieCommande")
     */
    public function envoieCommande($id)
    {  

        $entityManager = $this->getDoctrine()->getManager();
        $panier = $entityManager->getRepository(Panier::class)->find($id);

        $panier->setStatut(2);
        $entityManager->persist($panier);
        $entityManager->flush();


        return $this->redirectToRoute('commande');
    }

    function Header()
    {
        // Titre
        $this->SetFont('Arial', '', 18);
        $this->Cell(0, 6, 'Populations mondiales', 0, 1, 'C');
        $this->Ln(10);
        // Imprime l'en-tête du tableau si nécessaire
        parent::Header();
    }

    /**
     * @Route("/generer/facture/{id}", name="genererFacture")
     */
    public function genererFacture($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $paniers = $entityManager->getRepository(Panier::class)->find($id);
        $produitPaniers = $entityManager->getRepository(PanierProduits::class)->findBy(['panier' => $id]);


        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('pdf/mypdf.html.twig', [
            'title' => "Welcome to our PDF Test",
            'paniers' => $paniers,
            'produitPaniers' => $produitPaniers
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("doc.pdf", [
            "Attachment" => false
        ]);

    }

}
