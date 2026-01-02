<?php
namespace App\Controller;

use App\Entity\Group;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\SubjectRepository;

/**
 * @Route("/group")
 * @IsGranted("ROLE_GUEST")
 */
class GroupController extends AbstractController
{
    /** @Route("/", name="group_index") */
    public function index(GroupRepository $repo)
    {
        return $this->render('group/index.html.twig', [
            'groups' => $repo->findAll(),
        ]);
    }

    /** 
     * @Route("/new", name="group_new")
     * @IsGranted("ROLE_TEACHER")
     */
    public function new(Request $req, EntityManagerInterface $em)
    {
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($group);
            $em->flush();
            return $this->redirectToRoute('group_index');
        }

        return $this->render('group/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{id}", name="group_delete", methods={"POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function delete(Request $request, Group $group, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $group->getId(), $request->request->get('_token'))) {
            foreach ($group->getUserToGroups() as $link) {
                $em->remove($link);
            }
            $em->remove($group);
            $em->flush();
        }

        return $this->redirectToRoute('group_index');
    }


    /** @Route("/{id}", name="group_show") */
    public function show(Group $group)
    {
        return $this->render('group/show.html.twig', ['group' => $group]);
    }

    /** 
     * @Route("/{id}/edit", name="group_edit")
     * @IsGranted("ROLE_TEACHER")
     */
    public function edit(Request $req, Group $group, EntityManagerInterface $em)
    {
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('group_index');
        }
        return $this->render('group/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/group/{id}/subjects", name="group_subjects", methods={"GET"})
     */
    public function subjects(Group $group, SubjectRepository $subjectRepository): Response
    {
        return $this->render('group/subjects.html.twig', [
            'group' => $group,
            'subjects' => $group->getSubjects(),
        ]);
    }
}
