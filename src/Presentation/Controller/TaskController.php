<?php

namespace App\Presentation\Controller;

use App\Domain\Dto\TaskCollectionDto;
use App\Domain\Dto\TaskDto;
use App\Domain\Entity\TaskCollection;
use App\Domain\Service\TaskService;
use App\Presentation\Transformer\TaskTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

/**
 * @Route("/api/task")
 * @SWG\Tag(name="tasks")
 */
class TaskController extends AbstractController
{
    /**
     * @var TaskService
     */
    private $service;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * @var TaskTransformer
     */
    private $taskTransformer;

    public function __construct(TaskService $service, Manager $fractal, TaskTransformer $taskTransformer)
    {
        $this->service = $service;
        $this->fractal = $fractal;
        $this->taskTransformer = $taskTransformer;
    }

    /**
     * @Route(
     *     "",
     *     name="task_add",
     *     methods={"POST"},
     *     format="application/json",
     *     requirements={
     *          "_format" : "application/json"
     *      }
     * )
     * @SWG\Post(
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          format="application/json",
     *          @SWG\Schema(ref="#/definitions/TaskRequest")
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="Returns added task",
     *          @SWG\Schema(ref="#/definitions/TaskResponse")
     *      ),
     *     @SWG\Response(
     *          response=400,
     *          description="Returns the validation errors"
     *      )
     * )
     * @ParamConverter("task", class=TaskDto::class)
     */
    public function addTask(TaskDto $task)
    {
        $taskDto = $this->service->addTask($task);
        $resource = new Item($taskDto, $this->taskTransformer);

        return $this->json(
            $this->fractal->createData($resource)->toArray()
        );
    }

    /**
     * @Route(
     *     "",
     *     name="tasks_fet",
     *     methods={"GET"},
     *     format="application/json",
     *     requirements={
     *          "_format" : "application/json"
     *      }
     * )
     * @SWG\Parameter(
     *      name="areDone",
     *      in="query",
     *      type="boolean"
     * )
     * @SWG\Parameter(
     *      name="when",
     *      in="query",
     *      type="string"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the tasks",
     *     @SWG\Schema(ref="#/definitions/MultipleTaskResponse")
     * )
     */
    public function getTasks(Request $request)
    {
        $areDone = null;
        $when = null;
        if ($request->query->has('areDone')) {
            $areDone = $request->query->getBoolean('areDone');
        }
        if ($request->query->has('when')) {
            $whenString = $request->query->get('when', "");
            $when = \DateTime::createFromFormat('Y-m-d', $whenString);

            if (!$when) {
                throw new \InvalidArgumentException("When is not valid!", 400);
            }
        }
        $tasksCollectionDto = $this->service->getAllTasks($areDone, $when);
        $resource = new Collection($tasksCollectionDto, $this->taskTransformer);

        return $this->json(
            $this->fractal->createData($resource)->toArray()
        );
    }

    /**
     * @Route(
     *     "/{taskId}/complete",
     *     name="task_complete",
     *     methods={"PUT"},
     *     format="application/json",
     *     requirements={
     *          "_format" : "application/json"
     *      }
     * )
     * @SWG\Put(
     *      @SWG\Parameter(parameter="taskId", name="taskId", type="integer", in="path"),
     *      @SWG\Response(
     *          response=200,
     *          description="Returns the completed task",
     *          @SWG\Schema(ref="#/definitions/TaskResponse")
     *      ),
     *      @SWG\Response(
     *          response=404,
     *          description="Task not found",
     *      )
     * )
     */
    public function completeTask(int $taskId)
    {
        $taskDto = $this->service->completeTask($taskId);
        $resource = new Item($taskDto, $this->taskTransformer);

        return $this->json(
            $this->fractal->createData($resource)->toArray()
        );
    }

    /**
     * @Route(
     *     "",
     *     name="task_update",
     *     methods={"PUT"},
     *     format="application/json",
     *     requirements={
     *          "_format" : "application/json"
     *      }
     * )
     * @SWG\Put(
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          format="application/json",
     *          @SWG\Schema(ref="#/definitions/TaskUpdateRequest")
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="Returns added task",
     *          @SWG\Schema(ref="#/definitions/TaskResponse")
     *      ),
     *      @SWG\Response(
     *          response=404,
     *          description="Task not found",
     *      )
     * )
     * @ParamConverter("task", class=TaskDto::class)
     */
    public function updateTask(TaskDto $task)
    {
        $taskDto = $this->service->updateTask($task);
        $resource = new Item($taskDto, $this->taskTransformer);

        return $this->json(
            $this->fractal->createData($resource)->toArray()
        );
    }

    /**
     * @Route(
     *     "/{taskId}",
     *     name="get_task",
     *     methods={"GET"},
     *     format="application/json",
     *     requirements={
     *          "_format" : "application/json"
     *      }
     * )
     * @SWG\Get(
     *      @SWG\Parameter(parameter="taskId", name="taskId", type="integer", in="path"),
     *      @SWG\Response(
     *          response=200,
     *          description="Returns the task with id",
     *          @SWG\Schema(ref="#/definitions/TaskResponse")
     *      ),
     *      @SWG\Response(
     *          response=404,
     *          description="Task not found",
     *      )
     * )
     */
    public function getTask(int $taskId)
    {
        $taskDto = $this->service->getTask($taskId);
        $resource = new Item($taskDto, $this->taskTransformer);

        return $this->json(
            $this->fractal->createData($resource)->toArray()
        );
    }

    /**
     * @Route(
     *     "/{taskId}",
     *     name="delete_task",
     *     methods={"DELETE"},
     *     format="application/json",
     *     requirements={
     *          "_format" : "application/json"
     *      }
     * )
     * @SWG\Delete(
     *      @SWG\Parameter(parameter="taskId", name="taskId", type="integer", in="path"),
     *      @SWG\Response(
     *          response=200,
     *          description="Returns the deleted task",
     *          @SWG\Schema(ref="#/definitions/TaskResponse")
     *      ),
     *      @SWG\Response(
     *          response=404,
     *          description="Task not found",
     *      )
     * )
     */
    public function deleteTask(int $taskId)
    {
        $taskDto = $this->service->deleteTask($taskId);
        $resource = new Item($taskDto, $this->taskTransformer);

        return $this->json(
            $this->fractal->createData($resource)->toArray()
        );
    }
}
