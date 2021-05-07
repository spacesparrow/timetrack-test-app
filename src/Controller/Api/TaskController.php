<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Task;
use App\Entity\User;
use App\Form\Task\CreateTaskType;
use App\Security\Voter\TaskVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/tasks", name="api_tasks_")
 */
class TaskController extends BaseController
{

    /**
     * @Route("/", name="list", methods={"GET"})
     * @OA\Get(
     *     tags={"Tasks"},
     *     summary="List all user tasks",
     *     description="List all tasks created by logged in user"
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {

    }

    /**
     * @Route("/{id}", name="show", methods={"GET"}, requirements={"id"="\d+"})
     * @OA\Get(
     *     tags={"Tasks"},
     *     summary="Display task",
     *     description="Display task created by logged in user"
     * )
     *
     * @param Task $task
     * @return Response
     */
    public function showAction(Task $task): Response
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(TaskVoter::ACTION_VIEW, $task);

        return $this->showResponse($task);
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     * @OA\Post(
     *     tags={"Tasks"},
     *     summary="Create new task",
     *     description="Create new task for logged in user"
     * )
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function createAction(Request $request, EntityManagerInterface $manager): Response
    {
        $task = new Task();
        $this->denyAccessUnlessGranted(TaskVoter::ACTION_CREATE, $task);

        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(CreateTaskType::class, $task);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->badRequestResponse($form);
        }

        $task->setUser($user);
        $manager->persist($task);
        $manager->flush();

        return $this->createdResponse(
            $this->generateUrl(
                'api_tasks_show',
                ['id' => $task->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"}, requirements={"id"="\d+"})
     * @OA\Put(
     *     tags={"Tasks"},
     *     summary="Update existing task",
     *     description="Update existing task with time spent"
     * )
     *
     * @param Request $request
     * @param Task $task
     * @return Response
     */
    public function updateAction(Request $request, Task $task): Response
    {

    }
}
