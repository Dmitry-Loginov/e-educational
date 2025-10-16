<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    /**
     * @Route("/not_found_subject", name="error_not_fount_subject", methods={"GET"})
     */
    public function index():Response
    {  
        return $this->render('error/not_found_subject.html.twig');
    }
}
