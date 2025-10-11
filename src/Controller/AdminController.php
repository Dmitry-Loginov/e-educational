<?php
namespace App\Controller;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
/**
 * @Route("/admin")
  * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_edit", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function edit(User $user): Response
    {
        $roles = ['ROLE_GUEST', 'ROLE_STUDENT', 'ROLE_TEACHER', 'ROLE_ADMIN'];

        return $this->render('admin/edit.html.twig', [
            'user' => $user,
            'roles' => $roles,
            'button_label' => 'Сохранить',
        ]);
    }
    
    /**
     * @Route("/{id}", name="edit_user", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function SetUserRole(ManagerRegistry $doctrine, int $id): RedirectResponse
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        $user->setRoles([$_POST['role']])
            ->setEmail($_POST['email'])
            ->setName($_POST['name']);
        $entityManager->flush();
        return $this->redirectToRoute('admin_edit', ['id' => $id]);
    }
}
