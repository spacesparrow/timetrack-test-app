<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Task;
use App\Entity\User;
use App\Form\Task\CreateTaskType;
use App\Security\Voter\TaskVoter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
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
     *     description="List all tasks created by logged in user",
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of Task entities created by logged in user, ordered by created date desc",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(type="integer", property="currentPageNumber", example=1),
     *                 @OA\Property(type="integer", property="numItemsPerPage", example=10),
     *                 @OA\Property(type="integer", property="totalCount", example=5),
     *                 @OA\Property(type="integer", property="pageRange", example=10),
     *                 @OA\Property(
     *                     type="array",
     *                     property="items",
     *                     @OA\Items(ref=@Model(type=Task::class))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unathorized request",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(property="code", type="integer", example=401),
     *                     @OA\Property(property="message", type="string", example="Expired JWT Token"),
     *                 )
     *         )
     *     ),
     * )
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function indexAction(Request $request, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $tasks = $user->getTasks();

        $paginatedTasks = $paginator->paginate(
            $tasks,
            $request->request->getInt('page', 1)
        );

        return $this->showResponse($paginatedTasks);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"}, requirements={"id"="\d+"})
     * @OA\Get(
     *     tags={"Tasks"},
     *     summary="Display task",
     *     description="Display task created by logged in user",
     *     @OA\Response(
     *         response=200,
     *         description="Task entity returned",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref=@Model(type=Task::class))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unathorized request",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(property="code", type="integer", example=401),
     *                     @OA\Property(property="message", type="string", example="Expired JWT Token"),
     *                 )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied request",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="code", type="integer", example=403),
     *                 @OA\Property(property="message", type="string", example="Access Denied."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task was not found",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="code", type="integer", example=404),
     *                 @OA\Property(property="message", type="string", example="Not Found")
     *             )
     *         )
     *     )
     * )
     *
     * @param Task|null $task
     * @return Response
     */
    public function showAction(?Task $task = null): Response
    {
        if (!$task) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(TaskVoter::ACTION_VIEW, $task);

        return $this->showResponse($task);
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     * @OA\Post(
     *     tags={"Tasks"},
     *     summary="Create new task",
     *     description="Create new task for logged in user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref=@Model(type=CreateTaskType::class))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task successfully created"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unathorized request",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(property="code", type="integer", example=401),
     *                     @OA\Property(property="message", type="string", example="Expired JWT Token"),
     *                 )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="code", type="integer", example=422),
     *                 @OA\Property(property="message", type="string", example="Validation Failed"),
     *                 @OA\Property(
     *                     property="errors",
     *                     type="object",
     *                     @OA\Property(
     *                         property="children",
     *                         type="object",
     *                         @OA\Property(
     *                             property="title",
     *                             type="array",
     *                             @OA\Items(example="This value is required")
     *                         ),
     *                         @OA\Property(
     *                             property="comment",
     *                             type="array",
     *                             @OA\Items(example="This value is required")
     *                         ),
     *                         @OA\Property(
     *                             property="timeSpent",
     *                             type="array",
     *                             @OA\Items(example="This value should be equal or greater than zero")
     *                         ),
     *                         @OA\Property(
     *                             property="createdDate",
     *                             type="array",
     *                             @OA\Items(example="Created date can not be greater that today")
     *                         ),
     *                     )
     *                 )
     *             )
     *         )
     *     )
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
        $form = $this->createSubmittedForm(CreateTaskType::class, $request, $task);

        if (!$form->isValid()) {
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
}
