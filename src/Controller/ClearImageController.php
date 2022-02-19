<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ClearImageController extends AbstractController
{
    /**
     * @Route("/clearImage", name="clearImage", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(): Response
    {  
        $uploaddir = '../public/images/uploads/';
        $myfiles = scandir($uploaddir);

        for ($i=3; $i < count($myfiles); $i++) { 
            unlink($uploaddir .  $myfiles[$i]);
        }
        return $this->redirectToRoute('home');
    }

}
