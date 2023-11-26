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

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list')]
    public function usersList(UserRepository $userRepository): Response
    {
        $usersList = $userRepository->findAll();
        return $this->render('user/list.html.twig', ['users' => $usersList]);
    }

    #[Route('/users/{id}/edit', name: 'user_edit')]
    public function userEdit(User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(ModifUserFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $plainPassword = $user->getPassword();
            dd($plainPassword);
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plainPassword
            );
            $user->setPassword($hashedPassword);
            $em->persist($user);
            $em->flush();
        }
        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
