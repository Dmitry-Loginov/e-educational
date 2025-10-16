<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Form\AnswerType;
use App\Repository\AnswerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @Route("/answer")
 * @IsGranted("ROLE_TEACHER")
 * 
 */
class AnswerController extends AbstractController
{
    /**
     * @Route("/", name="answer_index", methods={"GET"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function index(AnswerRepository $answerRepository): Response
    {
        return $this->render('answer/index.html.twig', [
            'answers' => $answerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="answer_edit", methods={"GET"})
     * @IsGranted("ROLE_STUDENT")
     */
    public function edit(Answer $answer): Response
    {
        return $this->renderForm('answer/edit.html.twig', [
            'answer' => $answer,
        ]);
    }

    /**
     * @Route("/{id}", name="set_teacher_response", methods={"POST"})
     */
    public function SetMark(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $answer = $entityManager->getRepository(Answer::class)->find($id);
        $mark = $_POST['mark'];
        $comment = $_POST['commentTeacher'];
        $answer->setMark($mark);
        $answer->setCommentTeacher($comment);
        $entityManager->flush();
        return $this->redirectToRoute('answer_edit', ['id' => $id]);
    }

    /**
     * @Route("/delete/{id}", name="answer_delete", methods={"POST"})
     * @IsGranted("ROLE_STUDENT")
     */
    public function delete(Request $request, Answer $answer, EntityManagerInterface $entityManager): Response
    {
        $oldImage = '../public/' . $answer->getAnswerFlePath();
        unlink($oldImage);
        if ($this->isCsrfTokenValid('delete'.$answer->getId(), $request->request->get('_token'))) {
            $entityManager->remove($answer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('answer_index', [], Response::HTTP_SEE_OTHER);
    }
}
