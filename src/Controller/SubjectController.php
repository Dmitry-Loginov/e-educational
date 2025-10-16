<?php

namespace App\Controller;

use App\Entity\Subject;
use App\Entity\Group;
use App\Form\SubjectType;
use App\Form\GroupType;
use App\Repository\SubjectRepository;
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
 * @Route("/subject")
 * @IsGranted("ROLE_GUEST")
 */
class SubjectController extends AbstractController
{
    /**
     * @Route("/", name="subject_index", methods={"GET"})
     */
    public function index(SubjectRepository $subjectRepository): Response
    {
        return $this->render('subject/index.html.twig', [
            'subjects' => $subjectRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="subject_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subject = new Subject();
        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subject->setUser($this->getUser());
            $entityManager->persist($subject);
            $entityManager->flush();

            return $this->redirectToRoute('subject_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('subject/new.html.twig', [
            'subject' => $subject,
            'form' => $form,
            'subjectId' => $subject->getId(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="subject_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function edit(Request $request, subject $subject, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('subject_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('subject/edit.html.twig', [
            'subject' => $subject,
            'form' => $form,
            'subjectId' => $subject->getId(),
        ]);
    }

    /**
     * @Route("/{id}", name="subject_delete", methods={"POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function delete(Request $request, subject $subject, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subject->getId(), $request->request->get('_token'))) {
            $entityManager->remove($subject);
            $entityManager->flush();
        }

        return $this->redirectToRoute('subject_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/group/subject/new/{groupId}", name="subject_new_for_group", methods={"GET", "POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function newForGroup(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, $groupId=false): Response
    {
        if ($groupId === false) {
            throw $this->createNotFoundException('Группа не найдена');
        }

        $subject = new Subject();
        $group = $doctrine->getRepository(Group::class)->find($groupId);
        $user = $this->getUser();
        $subject->setGroup($group);
        $subject->setUser($user);

        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subject);
            $entityManager->flush();

            return $this->redirectToRoute('group_subjects', ['id' => $group->getId()]);
        }

        return $this->render('subject/new.html.twig', [
            'subject' => $subject,
            'form' => $form->createView(),
        ]);
    }
}
