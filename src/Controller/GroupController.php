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
use App\Repository\ThemeRepository;

/**
 * @Route("/group")
 */
class GroupController extends AbstractController
{
    /** @Route("/", name="group_index") */
    public function index(GroupRepository $repo)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('group/index.html.twig', [
            'groups' => $repo->findAll(),
        ]);
    }

    /** @Route("/new", name="group_new") */
    public function new(Request $req, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
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

    /** @Route("/{id}/delete", name="group_delete") */
    public function delete(Group $group, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // обнуляем группы у пользователей
        foreach ($group->getUserToGroups() as $link) {
            $em->remove($link);
        }
        $em->remove($group);
        $em->flush();

        return $this->redirectToRoute('group_index');
    }

    /** @Route("/{id}", name="group_show") */
    public function show(Group $group)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('group/show.html.twig', ['group' => $group]);
    }

    /** @Route("/{id}/edit", name="group_edit") */
    public function edit(Request $req, Group $group, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('group_index');
        }
        return $this->render('group/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/group/{id}/themes", name="group_themes", methods={"GET"})
     */
    public function themes(Group $group, ThemeRepository $themeRepository): Response
    {
        return $this->render('group/themes.html.twig', [
            'group' => $group,
            'themes' => $group->getThemes(),
        ]);
    }
}
