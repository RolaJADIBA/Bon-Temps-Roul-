<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;



/**
 * @Route("/admin")
 * @IsGranted("ROLE_ADMIN")
 */
class UserAdminController extends AbstractController
{
    /**
     * @Route("/user", name="user_admin")
     */
    public function index()
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/user/{id}", name="user_show", methods={"GET"})
     */
    public function show( User $user): Response
    {


        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }


}
