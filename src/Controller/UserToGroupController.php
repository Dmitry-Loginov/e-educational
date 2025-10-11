<?php
namespace App\Controller;

use App\Entity\UserToGroup;
use App\Form\UserToGroupType;
use App\Repository\UserToGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/group/assign")
 */
class UserToGroupController extends AbstractController
{
    /** @Route("/", name="assign_index") */
    public function index(UserToGroupRepository $repo)
    {
        $this->denyAccessUnlessGranted(['ROLE_TEACHER', 'ROLE_ADMIN']);
        return $this->render('assign/index.html.twig', [
            'assignments' => $repo->findAll(),
        ]);
    }

    /** @Route("/new", name="assign_new") */
    public function new(Request $req, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted(['ROLE_TEACHER', 'ROLE_ADMIN']);
        $assign = new UserToGroup();
        $form = $this->createForm(UserToGroupType::class, $assign);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($assign);
            $em->flush();
            return $this->redirectToRoute('assign_index');
        }

        return $this->render('assign/new.html.twig', ['form' => $form]);
    }

    /** @Route("/{id}", name="assign_show") */
    public function show(UserToGroup $assignment)
    {
        $this->denyAccessUnlessGranted(['ROLE_TEACHER', 'ROLE_ADMIN']);
        return $this->render('assign/show.html.twig', ['assignment' => $assignment]);
    }

    /** @Route("/{id}/edit", name="assign_edit") */
    public function edit(Request $req, UserToGroup $assignment, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted(['ROLE_TEACHER', 'ROLE_ADMIN']);
        $form = $this->createForm(UserToGroupType::class, $assignment);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('assign_index');
        }
        return $this->render('assign/edit.html.twig', ['form' => $form->createView()]);
    }
}
