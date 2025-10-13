<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends AbstractController
{
    /** 
    * @Route("/", name="home")
    */
    public function index(): Response
    {
        return is_null($this->getUser()) ? $this->render('welcome/index.html.twig') : $this->redirectToRoute('group_index');
    }
}
