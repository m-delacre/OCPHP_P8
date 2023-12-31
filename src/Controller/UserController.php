<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ModifUserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list')]
    #[IsGranted('ROLE_ADMIN', message: "vous n'êtes pas authorisé à accéder à cette page.")]
    public function usersList(UserRepository $userRepository): Response
    {
        $usersList = $userRepository->findAll();
        return $this->render('user/list.html.twig', ['users' => $usersList]);
    }

    #[Route('/users/{id}/edit', name: 'user_edit')]
    public function userEdit(User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $userRoles = $user->getRoles();
        $isAdmin = false;

        if (in_array('ROLE_ADMIN', $userRoles)) {
            $isAdmin = true;
        }

        $form = $this->createForm(ModifUserFormType::class, $user, ['is_admin' => $isAdmin]);

        $userPassword = $user->getPassword();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('isAdmin')->getData()) {
                $user->setRolesAdminUser();
            } else {
                $user->setRolesSimpleUser();
            }

            if (empty($form->get('password')->getData())) {
                $user->setPassword($userPassword);
            } else {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
            }

            $em->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
