<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('admin/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    #[Route('admin/user/{id}/to/editor', name: 'app_user_to_editor')]
    public function changeRole(EntityManagerInterface $entityManager,User $user): Response{
        $user->setRoles(['ROLE_EDITOR','ROLE_USER']);
        $entityManager->flush();
        $this->addFlash('success','User role changed to editor successfully');
        return $this->redirectToRoute('app_user');
    }
    #[Route('admin/user/{id}/remove/editor', name: 'app_user_remove_editor_role')]
    public function removeEditorRole(EntityManagerInterface $entityManager,User $user): Response{
        $user->setRoles(['']);
        $entityManager->flush();
        $this->addFlash('danger','Editor role removed successfully');
        return $this->redirectToRoute('app_user');
    }
      #[Route('admin/user/{id}/remove', name: 'app_user_remove')]
    public function userRemove(EntityManagerInterface $entityManager,UserRepository $userRepository,$id): Response{
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('danger', 'User not found');
            return $this->redirectToRoute('app_user');
        }
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('danger', 'User removed successfully');
        return $this->redirectToRoute('app_user');
    }
}
