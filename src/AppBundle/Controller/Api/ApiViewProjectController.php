<?php


namespace AppBundle\Controller\Api;


use AppBundle\Entity\Project;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/api", name="api")
 */
class ApiViewProjectController extends ApiController
{

    /**
     * @Route("/user/{id}/project/{id2}", name="_view_project", methods={"GET"})
     */
    public function viewProjectAction($id, $id2, Request $request)
    {

        if ($id != null && $id2 != null) {
            $repository = $this->getDoctrine()->getRepository(Project::class);
            $project = $repository->findOneById($id2);

            $baseurl = $this->getBaseURL($request);

            if ($project && $project->getUser()->getId() == $id) {
                $project_array = [
                    "id" => $project->getId(),
                    "name" => $project->getName(),
                    "description" => $project->getDescription(),
                    "owner" => $project->getUser()->getRealname(),
                    "tasks" => array_map(function ($task) use ($baseurl, $project){
                        return [
                            "task_name" => $task->getName(),
                            "task_url" => $baseurl . "/user/" . $task->getProject()->getUser()->getId() . "/project/" . $task->getProject()->getId() . "/task/" . $task->getId()
                        ];
                    }, $project->getTasks()->toArray())
                ];

                $response = $this->setResponse($project_array);
                return $response;
            }
        }
        throw new BadRequestHttpException ('Wrong Id for User or Project', null, 422);
    }
}