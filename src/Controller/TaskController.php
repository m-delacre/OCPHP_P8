<?php

namespace App\Controller;

use DateTime;
use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task_list')]
    public function listTask(TaskRepository $taskRepository): Response
    {
        $tasksList = $taskRepository->findBy(['isDone' => false]);
        return $this->render('task/list.html.twig', [
            'tasks' => $tasksList
        ]);
    }

    #[Route('/tasksDone', name: 'task_list_done')]
    public function listTaskNotDone(TaskRepository $taskRepository): Response
    {
        $tasksList = $taskRepository->findBy(['isDone' => true]);
        return $this->render('task/list.html.twig', [
            'tasks' => $tasksList
        ]);
    }

    #[Route('/tasks/create', name: 'task_create')]
    public function createTask(Request $request, EntityManagerInterface $em): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newTask = $form->getData();
            $newTask->setCreatedAt(new DateTime());
            $newTask->setIsDone(false);

            $currentUser = $this->getUser();
            $newTask->setUser($currentUser);

            $em->persist($newTask);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    #[IsGranted('', subject: 'task')]
    public function editTask(Task $task, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'La tâche à bien été modifiée.');
            return $this->redirectToRoute('task_list');
        }

        return $this->render(
            'task/edit.html.twig',
            [
                'form' => $form->createView(),
                'task' => $task
            ]
        );
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle')]
    public function toggleTask(Task $task, EntityManagerInterface $em): Response
    {
        $task->toggle();
        $em->flush();

        $this->addFlash('success', sprintf(
            'La tâche %s a bien été marquée comme faite.',
            $task->getTitle()
        ));
        return $this->redirectToRoute('task_list');
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete')]
    public function deleteTask(Task $task, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($task->getUser() === $user) {
            $em->remove($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a bien été supprimée.');
            return $this->redirectToRoute('task_list');
        }

        if ( $task->getUser()->getUsername() === "anonyme" && in_array('ROLE_ADMIN', $user->getRoles())) {
            $em->remove($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a bien été supprimée.');
            return $this->redirectToRoute('task_list');
        }

        $this->addFlash('error', "vous n'êtes pas authorisé à supprimer cette tâche.");
        return $this->redirectToRoute('task_list');
    }
}
