<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\Group;
use App\Form\ThemeType;
use App\Form\GroupType;
use App\Repository\ThemeRepository;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @Route("/theme")
 * @IsGranted("ROLE_GUEST")
 */
class ThemeController extends AbstractController
{
    /**
     * @Route("/", name="theme_index", methods={"GET"})
     */
    public function index(ThemeRepository $themeRepository): Response
    {
        return $this->render('theme/index.html.twig', [
            'themes' => $themeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="theme_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $theme = new Theme();
        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $theme->setUser($this->getUser());
            $entityManager->persist($theme);
            $entityManager->flush();

            return $this->redirectToRoute('theme_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('theme/new.html.twig', [
            'theme' => $theme,
            'form' => $form,
            'themeId' => $theme->getId(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="theme_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function edit(Request $request, Theme $theme, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('theme_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('theme/edit.html.twig', [
            'theme' => $theme,
            'form' => $form,
            'themeId' => $theme->getId(),
        ]);
    }

    /**
     * @Route("/{id}", name="theme_delete", methods={"POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function delete(Request $request, Theme $theme, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$theme->getId(), $request->request->get('_token'))) {
            $entityManager->remove($theme);
            $entityManager->flush();
        }

        return $this->redirectToRoute('theme_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/group/theme/new/{groupId}", name="theme_new_for_group", methods={"GET", "POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function newForGroup(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, $groupId=false): Response
    {
        if ($groupId === false) {
            throw $this->createNotFoundException('Группа не найдена');
        }

        $theme = new Theme();
        $group = $doctrine->getRepository(Group::class)->find($groupId);
        $user = $this->getUser();
        $theme->setGroup($group);
        $theme->setUser($user);

        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($theme);
            $entityManager->flush();

            return $this->redirectToRoute('group_themes', ['id' => $group->getId()]);
        }

        return $this->render('theme/new.html.twig', [
            'theme' => $theme,
            'form' => $form->createView(),
        ]);
    }
}
