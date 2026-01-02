<?php
namespace App\Controller;

use App\Entity\Subject;
use App\Entity\Lesson;
use App\Entity\User;
use App\Entity\Answer;
use App\Form\LessonType;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @Route("/lesson")
 * @IsGranted("ROLE_GUEST")
 */
class LessonController extends AbstractController
{
    /**
     * @Route("/{id}", name="lesson_delete", methods={"POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function delete(Request $request, Lesson $lesson, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lesson->getId(), $request->request->get('_token'))) {
            $entityManager->remove($lesson);
            $entityManager->flush();
        }

        return $this->redirectToRoute('subject_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/new/{subjectId}", name="lesson_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function new(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, $subjectId=false): Response
    {
        if ($subjectId === false) {
            return $this->redirectToRoute('error_not_fount_subject');
        }
        $lesson = new Lesson();
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $subject = $doctrine->getRepository(Subject::class)->find($subjectId);
            if($subject == null){
                return $this->redirectToRoute('error_not_fount_subject');
            }
            $lesson->setSubject($subject);

            $entityManager->persist($lesson);
            $entityManager->flush();

            return $this->redirectToRoute('subject_index');
        }

        return $this->renderForm('lesson/new.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
            'subjectId' => $subjectId,
        ]);
    }

    /**
     * @Route("/{id}", name="lesson_show", methods={"GET"})
     */
    public function show(Lesson $lesson, ManagerRegistry $doctrine): Response
    {
        $subjectId = $lesson->getSubject()->getId();
        $answer = $doctrine->getRepository(Answer::class)->findOneBy([
            'lesson' => $lesson,
            'user' => $this->getUser(),
        ]);

        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
            'subjectId' => $subjectId,
            'answer' => $answer,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="lesson_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function edit(Request $request, Lesson $lesson, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);
        $subject = $lesson->getSubject();

        if ($form->isSubmitted() && $form->isValid()) {
            
            $subject = $doctrine->getRepository(Subject::class)->find($subject->getId());    
            if($subject == null){
                return $this->redirectToRoute('error_not_fount_subject');
            }
            $entityManager->flush();

            return $this->redirectToRoute('subject_index');
        }

        return $this->renderForm('lesson/edit.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
            'subjectId' => $subject->getId(),
        ]);
    }

    /**
     * @Route("/send/{id}", name="send_image", methods={"POST"})
     * @IsGranted("ROLE_STUDENT")
     */
    public function sendAnswer(Request $request, Lesson $lesson, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {
        date_default_timezone_set('Europe/Minsk');
        $comment = $request->request->get('commentStudent', '');
        $answer = $doctrine->getRepository(Answer::class)->findOneBy([
            'lesson' => $lesson,
            'user' => $this->getUser(),
        ]);

        if (!$answer) {
            
            $answer = new Answer();
            $answerFlePath = "";
            $answer->setCommentStudent($comment);
            $good_name = array_filter($_FILES['image']);

            if (sizeof($good_name) > 1){
                $uniqName = Uuid::v4()->toRfc4122() . '-' . basename($_FILES['image']['name']);
                $uploadfile = '../public/images/uploads/' . $uniqName;
                $answerFlePath = '/images/uploads/' . $uniqName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile) == false) {
                    $subjectId = $lesson->getSubject()->getId();
                    return $this->render('lesson/show.html.twig', [
                        'lesson' => $lesson,
                        'subjectId' => $subjectId,
                        'inf_msg' => 'Произошла ошибка при отправке изображения.',
                    ]);
                }
            }

            $answer
            -> setLesson($lesson)
            -> setUser($this->getUser())
            ->setCommentStudent($_POST['commentStudent'])
            -> setDate(new \DateTime());

            if($answerFlePath != ""){
                $answer->setAnswerFlePath($answerFlePath);
            }

            $entityManager->persist($answer);
            $entityManager->flush();
        }
        else{

            $answerFlePath = "";
            $answer->setCommentStudent($comment);
            $good_name = array_filter($_FILES['image']);

            if (sizeof($good_name) > 1){
                $uniqName = Uuid::v4()->toRfc4122() . '-' . basename($_FILES['image']['name']);
                $uploadfile = '../public/images/uploads/' . $uniqName;
                $answerFlePath = '/images/uploads/' . $uniqName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile) == false) {
                    $subjectId = $lesson->getSubject()->getId();
                    return $this->render('lesson/show.html.twig', [
                        'lesson' => $lesson,
                        'subjectId' => $subjectId,
                        'inf_msg' => 'Произошла ошибка при отправке изображения.',
                    ]);
                }
            }

            $answer
            ->setCommentStudent($_POST['commentStudent'])
            ->setDate(new \DateTime());

            $oldImage = 'public' . $answer->getAnswerFlePath();
            try {
                if($oldImage != "public" and $answerFlePath != ""){
                    unlink($oldImage);
                }
            } catch (Exception $e) {
                echo('###################');
            }
            
            if($answerFlePath != ""){
                $answer->setMark(null);
                $answer->setAnswerFlePath($answerFlePath);
            }

            $entityManager->flush();
        }

        return $this->redirectToRoute('lesson_show', [
            'id' => $lesson->getId(),
        ]);
    }
}
