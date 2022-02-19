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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

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
    public function sendMail(Request $request, Lesson $lesson, MailerInterface $mailer): Response
    {
        $email = (new Email())
        ->from('dimasik.loginovskiy.00@mail.ru')
        ->to('interestingfacts223@gmail.com')
        //->cc('cc@example.com')
        //->bcc('bcc@example.com')
        //->replyTo('fabien@example.com')
        //->priority(Email::PRIORITY_HIGH)
        ->subject('Time for Symfony Mailer!')
        ->text('Sending emails is fun again!')
        ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);
    }
}