<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\PanierProduits;
use App\Entity\User;
use App\Form\UserEditConnexionType;
use App\Form\UserEditPassType;
use App\Form\UserEditType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
// use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Dompdf\Dompdf;
use Dompdf\Options;

class UserController extends AbstractController
{
    /**
     * @Route("/compte", name="account")
     */
    public function index()
    {
        return $this->render('user/index.html.twig', [
            
        ]);
    }

    /**
     * @Route("/compte/accueil", name="account_home")
     */
    public function profil()
    {
        return $this->render('user/compte.html.twig', [
            
        ]);
    }

    /**
     * @Route("/compte/commande", name="account_commande")
     */
    public function compteCommande()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $panier = $entityManager->getRepository(Panier::class)->findBy(['user' => $this->getUSer()]);


        return $this->render('user/commande.html.twig', [
            'paniers' => $panier,
        ]);
    }

    /**
     * @Route("/compte/commande/{id}", name="account_commande_voir")
     */
    public function compteCommandeVoir($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $panier = $entityManager->getRepository(Panier::class)->find($id);
        $produitPaniers = $entityManager->getRepository(PanierProduits::class)->findBy(['panier' => $id]);


        return $this->render('user/commande_voir.html.twig', [
            'panier' => $panier,
            'produitPaniers' => $produitPaniers

            
        ]);
    }

    /**
     * @Route("/generer/facture/{id}", name="genererFactureClients")
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


    /**
     * @Route("/compte/profil", name="account_view")
     */
    public function user_profil()
    {
        return $this->render('user/profil_view.html.twig', [
            
        ]);
    }


    /**
     * @Route("/compte/profil/edit/info-connexion", name="account_edit_info_connexion")
     */
    public function user_edit_info_connexion(Request $request)
    {
        $form = $this->createForm(UserEditConnexionType::class, $this->getUser());
        $form->handleRequest($request); 

        if($form->isSubmitted() && $form->isValid()) 
        {
            $doctrine = $this->getDoctrine()->getManager();
            $doctrine->persist($this->getUser());
            $doctrine->flush();

            $this->addFlash('success', 'Vos informations ont été mise à jour !');
        }
        return $this->render('user/edit_info_connexion.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/compte/profil/edit/info-personnel", name="account_edit_info_personnel")
     */
    public function user_edit_info_personnel(Request $request)
    {
        $form = $this->createForm(UserEditType::class, $this->getUser());
        $form->handleRequest($request); 

        if($form->isSubmitted() && $form->isValid()) 
        {
            $doctrine = $this->getDoctrine()->getManager();
            $doctrine->persist($this->getUser());
            $doctrine->flush();

            $this->addFlash('success', 'Vos informations ont été mise à jour !');
        }
        return $this->render('user/edit_info_personnel.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/compte/profil/edit/info-securite", name="account_edit_info_security")
     */

    public function EditPass (Request $request, UserPasswordEncoderInterface $encoder)
    {
        $form = $this->createForm(UserEditPassType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {


            $valide = $encoder->isPasswordValid($this->getUser(), $form->get('password')->getData());

            if($valide){
                $user = $this->getUser();
                $password = $form->get('newpass')->getData();
                $hash_password = $encoder->encodePassword($user, $password);

                $user->setPassword($hash_password);

                $doctrine = $this->getDoctrine()->getManager();
                $doctrine->persist($user);
                $doctrine->flush();

                $this->addFlash('success', 'Le mot de passe est modifier');
                return $this->redirectToRoute('app_logout');

            }
            else{
                
                $this->addFlash('error', 'Le mot de passe actuel est incorrect');
            }
        }


        return $this->render('user/edit_info_pass.html.twig', array (
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/reset/password", name="reset_password")
     */
    public function resetPassword(Request $request,  \Swift_Mailer $mailer)
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->findOneByEmail($email);

            if($user != null){
                $token = md5(uniqid());
                $user->setToken($token);

                $doctrine = $this->getDoctrine()->getManager();
                $doctrine->persist($user);
                $doctrine->flush();

                $url = 'http://projet-magalie.test/reset/password/'.$token;
                $text = 'Bonjour, voici votre lien de réinitialisation de votre mot de passe ' . $url;

                $mail = (new \Swift_Message('test'))
                ->setFrom($email)
                ->setTo('magalipraticienne@yahoo.com')
                ->setBody($text);

                $mailer->send($mail);
                $this->addFlash('emailreset-success', 'Un e-mail à été envoyé, cliquer sur le lien pour réinitialiser votre mot de passe.');


            }else{
                $this->addFlash('emailreset-incorrect', "Votre adresse e-mail n'existe pas");
            }
        }
        return $this->render('reset/index.html.twig', [
            
        ]);
    }

    /**
     * @Route("/reset/password/{token}", name="reset_password_action")
     */
    public function resetPasswordAction($token, Request $request,  \Swift_Mailer $mailer, UserPasswordEncoderInterface $encoder)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $resultat = $entityManager->getRepository(User::class)->findOneBy(array('token' => $token));
        $password = $request->request->get('password');
        
        if($resultat != null){
            $hash_password = $encoder->encodePassword($resultat, $password);

            if($request->getMethod() == "POST"){

                $resultat->setPassword($hash_password);

                $doctrine = $this->getDoctrine()->getManager();
                $doctrine->persist($resultat);
                $doctrine->flush();

                $resultat->setToken('');
                $doctrine = $this->getDoctrine()->getManager();
                $doctrine->persist($resultat);
                $doctrine->flush();


                $text = 'Votre mot de passe a bien été réinitialisé, un email de confirmation vous a été envoyé ';

                $mail = (new \Swift_Message('test'))
                    ->setFrom('server@server.com')
                    ->setTo($resultat->getEmail())
                    ->setBody($text);

                $mailer->send($mail);
                $this->addFlash('reset-success', 'Mot de passe changer');  
            }
           
        }else{
            $this->addFlash('reset-error', "Erreur, le token est invalide");

        }
       
        return $this->render('reset/action.html.twig', [
            'resultats' => $resultat
        ]);
    }

    /**
     * @Route("/commande/en_attente", name="commande_en_attente")
     */
    public function commande_en_attente()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $panier = $entityManager->getRepository(Panier::class)->findBy(['statut' => 0, 'user' => $this->getUser()]);


        return $this->render('user/commande_en_attente.html.twig', [
            'paniers' => $panier,
        ]);

    }

     /**
     * @Route("/commande/en_preparation", name="commande_en_preparation")
     */
    public function commande_en_preparation()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $panier = $entityManager->getRepository(Panier::class)->findBy(['statut' => 1, 'user' => $this->getUser()]);


        return $this->render('user/commande_en_preparation.html.twig', [
            'paniers' => $panier,
        ]);

    }

    /**
     * @Route("/commande/en_livraison", name="commande_en_livraison")
     */
    public function commande_en_livraison()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $panier = $entityManager->getRepository(Panier::class)->findBy(['statut' => 2, 'user' => $this->getUser()]);


        return $this->render('user/commande_en_livraison.html.twig', [
            'paniers' => $panier,
        ]);

    }

    /**
     * @Route("/commande/livree", name="commande_livree")
     */
    public function commande_livree()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $panier = $entityManager->getRepository(Panier::class)->findBy(['statut' => 3, 'user' => $this->getUser()]);


        return $this->render('user/commande_livree.html.twig', [
            'paniers' => $panier,
        ]);

    }

    /**
     * @Route("/commande/annulee", name="commande_annulee")
     */
    public function commande_annulee()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $panier = $entityManager->getRepository(Panier::class)->findBy(['statut' => 4, 'user' => $this->getUser()]);


        return $this->render('user/commande_annulee.html.twig', [
            'paniers' => $panier,
        ]);

    }

    

}
