<?php
namespace App\Controller;

use App\Entity\Theme;
use App\Entity\Lesson;
use App\Entity\User;
use App\Form\LessonType;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\Persistence\ManagerRegistry;

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
    public function show(Lesson $lesson): Response
    {
        $themeId = $lesson->getTheme()->getId();
        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
            'themeId' => $themeId,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="lesson_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_TEACHER")
     */
    public function edit(Request $request, Lesson $lesson, EntityManagerInterface $entityManager): Response
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
    public function sendImage(Request $request, Lesson $lesson): Response
    {
        $user = $this->getUser();
        $from_email = $user->getEmail();
        $recipient_email = $lesson->getTheme()->getUser()->getEmail();
        $message = "test message";
        $subject = 'TEST';
        $headers = "From:".$from_email."\r\n"; // Sender Email
        $rezult = mail('dimasik.loginovskiy.00@mail.ru', $subject, $message);
        return $this->redirectToRoute('lesson_show', ['id' => $lesson->getId()]);
    }
}
