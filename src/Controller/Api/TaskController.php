<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\TasksExportDTO;
use App\Entity\Task;
use App\Entity\User;
use App\Exception\UnsupportedExportFormatException;
use App\Form\PaginatedType;
use App\Form\Task\CreateTaskType;
use App\Form\Task\ExportType;
use App\Manager\GeneralDoctrineManager;
use App\Security\Voter\TaskVoter;
use App\Service\Export\TasksExportServiceFacade;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/tasks", name="api_tasks_")
 */
class TaskController extends BaseController
{
    private TaskService $taskService;
    private GeneralDoctrineManager $doctrineManager;

    public function __construct(TaskService $taskService, GeneralDoctrineManager $doctrineManager)
    {
        $this->taskService = $taskService;
        $this->doctrineManager = $doctrineManager;
    }

    /**
     * @Route("/", name="list", methods={"GET"})
     * @OA\Get(
     *     tags={"Tasks"},
     *     summary="List all user tasks",
     *     description="List all tasks created by logged in user",
     *     @OA\Parameter(
     *         required=false,
     *         in="query",
     *         name="page",
     *         description="Page number to get, must be positive number",
     *         example=1
     *     ),
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
     *                             property="page",
     *                             type="array",
     *                             @OA\Items(example="This value should be positive.")
     *                         ),
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function indexAction(Request $request, PaginatorInterface $paginator): Response
    {
        /* create form with request query data */
        $form = $this->createGetForm(PaginatedType::class, $request);

        /* return response with messages if validation failed */
        if (!$form->isValid()) {
            return $this->badRequestResponse($form);
        }

        /* get logged in user and it tasks */
        /** @var User $user */
        $user = $this->getUser();
        $tasks = $this->taskService->getUserTasksQuery($user);
        $paginatedRequestDTO = $form->getData();

        /* paginate user tasks  */
        $paginatedTasks = $paginator->paginate(
            $tasks,
            $paginatedRequestDTO->getPage() ?? 1
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
     * @Security(name="Bearer")
     */
    public function showAction(?Task $task = null): Response
    {
        /*
         * throw NotFoundException (which will be converted to 404 Not Found response)
         * if no task with provided id was found
         */
        if (!$task) {
            throw $this->createNotFoundException();
        }

        /* deny access to found task if logged in user is not creator of it */
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
     *         description="Task successfully created",
     *         @OA\Header(
     *             header="Location",
     *             schema=@OA\Schema(
     *                 type="string",
     *                 example="http://localhost/api/tasks/1"
     *             ),
     *             description="URL to created task"
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
     * @Security(name="Bearer")
     *
     * @throws Exception
     */
    public function createAction(Request $request): Response
    {
        $task = new Task();
        /* deny access to task creation if logged in user can not perform this action */
        $this->denyAccessUnlessGranted(TaskVoter::ACTION_CREATE, $task);

        /** @var User $user */
        $user = $this->getUser();
        /* fill empty task with request data */
        $form = $this->createSubmittedForm(CreateTaskType::class, $request, $task);

        /* return response with messages if validation failed */
        if (!$form->isValid()) {
            return $this->badRequestResponse($form);
        }

        /* attach user to the task and save it in database */
        $task->setUser($user);
        $this->doctrineManager->save($task);

        /* return response with absolute url to created task in Location header */
        return $this->createdResponse(
            $this->generateUrl(
                'api_tasks_show',
                ['id' => $task->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @Route("/export", name="export", methods={"POST"})
     * @OA\Post(
     *     tags={"Tasks"},
     *     summary="Export tasks",
     *     description="Export tasks for logged in user in provided date rage and one of formats [pdf, csv, xlsx]",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref=@Model(type=ExportType::class))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Export file generated",
     *         @OA\Header(
     *             header="Content-Type",
     *             schema=@OA\Schema(
     *                 type="string",
     *                 example="application/pdf"
     *             ),
     *             description="Response MIME type, changing due to several export formats supported"
     *         ),
     *         @OA\Header(
     *             header="Content-Disposition",
     *             schema=@OA\Schema(
     *                 type="string",
     *                 example="attachment; filename=tasks_export.pdf",
     *             ),
     *             description="Describe that a client should initiate 'Save as' dialog"
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary"
     *             ),
     *         ),
     *         @OA\MediaType(
     *             mediaType="text/plain;charset=UTF-8",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary"
     *             )
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary"
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
     *                             property="type",
     *                             type="array",
     *                             @OA\Items(example="This value is required")
     *                         ),
     *                         @OA\Property(
     *                             property="start_date",
     *                             type="array",
     *                             @OA\Items(example="This value is required")
     *                         ),
     *                         @OA\Property(
     *                             property="end_date",
     *                             type="array",
     *                             @OA\Items(example="This value should be equal or greater than zero")
     *                         ),
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @throws UnsupportedExportFormatException
     */
    public function exportAction(Request $request, TasksExportServiceFacade $tasksExportServiceFacade): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /* created data transfer object for export and fill it with request data */
        $exportDTO = new TasksExportDTO();
        $form = $this->createSubmittedForm(ExportType::class, $request, $exportDTO);

        /* return response with messages if validation failed */
        if (!$form->isValid()) {
            return $this->badRequestResponse($form);
        }

        $startDate = $form->get('start_date')->getData();
        $endDate = $form->get('end_date')->getData();
        /* get user tasks that have createdDate in provided date range */
        $filteredTasks = $this->taskService->getUserTasksByDateRange($user, $startDate, $endDate);
        /* put filtered by date tasks in DTO and process export */
        $exportDTO->setTasks($filteredTasks);
        $responseDTO = $tasksExportServiceFacade->export($exportDTO);

        /* return download file response with generated one */
        return $this->exportFileDownloadResponse($responseDTO);
    }
}
