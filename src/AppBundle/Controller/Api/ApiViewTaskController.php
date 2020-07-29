<?php


namespace AppBundle\Controller\Api;


use AppBundle\Entity\Task;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/api", name="api")
 */
class ApiViewTaskController extends ApiController
{

    /**
     * @Route("/user/{id}/project/{id2}/task/{id3}", name="_view_task", methods={"GET"})
     */
    public function viewTaskAction($id, $id2, $id3, Request $request)
    {

        if ($id != null && $id2 != null && $id3 != null) {
            $repository = $this->getDoctrine()->getRepository(Task::class);
            $task = $repository->findOneById($id3);

            if ($task &&
                $task->getProject()->getId() == $id2 &&
                $task->getProject()->getUser()->getId() == $id
            ) {
                $task_array = [
                    "id" => $task->getId(),
                    "name" => $task->getName(),
                    "description" => $task->getDescription(),
                    "owner" => $task->getProject()->getUser()->getRealname(),
                    "project" => $task->getProject()->getName()
                ];

                $response = $this->setResponse($task_array);
                return $response;
            }
        }
        throw new BadRequestHttpException ('Wrong Id for User or Project or Task', null, 422);
    }
}