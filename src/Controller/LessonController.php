<?php
namespace App\Controller;

use App\Entity\Theme;
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

        return $this->redirectToRoute('theme_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/new/{themeId}", name="lesson_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function new(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, $themeId=false): Response
    {
        if ($themeId === false) {
            return $this->redirectToRoute('error_not_fount_theme');
        }
        $lesson = new Lesson();
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $theme = $doctrine->getRepository(Theme::class)->find($themeId);
            if($theme == null){
                return $this->redirectToRoute('error_not_fount_theme');
            }
            $lesson->setTheme($theme);

            $entityManager->persist($lesson);
            $entityManager->flush();

            return $this->redirectToRoute('theme_index');
        }

        return $this->renderForm('lesson/new.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
            'themeId' => $themeId,
        ]);
    }

    /**
     * @Route("/{id}", name="lesson_show", methods={"GET"})
     */
    public function show(Lesson $lesson, ManagerRegistry $doctrine): Response
    {
        $themeId = $lesson->getTheme()->getId();
        $answer = $doctrine->getRepository(Answer::class)->findOneBy([
            'lesson' => $lesson,
            'user' => $this->getUser(),
        ]);

        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
            'themeId' => $themeId,
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
        $theme = $lesson->getTheme();

        if ($form->isSubmitted() && $form->isValid()) {
            
            $theme = $doctrine->getRepository(Theme::class)->find($theme->getId());    
            if($theme == null){
                return $this->redirectToRoute('error_not_fount_theme');
            }
            $entityManager->flush();

            return $this->redirectToRoute('theme_index');
        }

        return $this->renderForm('lesson/edit.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
            'themeId' => $theme->getId(),
        ]);
    }

    /**
     * @Route("/send/{id}", name="send_image", methods={"POST"})
     * @IsGranted("ROLE_STUDENT")
     */
    public function sendAnswer(Request $request, Lesson $lesson, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {
        $uploaddir = '../public/images/uploads/';
        $uploadfile = $uploaddir . Uuid::v4()->toRfc4122() . '-' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile) == false) {
            $themeId = $lesson->getTheme()->getId();
            return $this->render('lesson/show.html.twig', [
                'lesson' => $lesson,
                'themeId' => $themeId,
                'inf_msg' => 'Произошла ошибка при отправке изображения.',
            ]);
        } 
        date_default_timezone_set('Europe/Minsk');

        $answer = new Answer();
        $answer
        -> setLesson($lesson)
        -> setUser($this->getUser())
        -> setPathImage($uploadfile)
        -> setDate(new \DateTime());

        $entityManager->persist($answer);
        $entityManager->flush();

        return $this->redirectToRoute('lesson_show', [
            'id' => $lesson->getId(),
        ]);
    }
}
