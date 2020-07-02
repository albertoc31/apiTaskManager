<?php


namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use AppBundle\Entity\User;
use AppBundle\Entity\Project;
use AppBundle\Entity\Task;


/**
 * @Route("/api", name="api")
 */
class ApiController extends Controller
{

    /**
     * @Route("/users", name="_list_users", methods={"GET"})
     */
    public function listUsersAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();

        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . "/api";

        $users_array = array_map( function($user) use ($baseurl){return [
            "id" => $user->getId(),
            "name" => $user->getRealname(),
            "url" => $baseurl . "/user/" . $user->getId(),
            "projects" => array_map( function ($project) use ($baseurl, $user){
                return [
                    "project_name" => $project->getName(),
                    "project_url" => $baseurl . "/user/" . $user->getId() . "/project/" . $project->getId()
                ];
                }, $user->getProjects()->toArray()),
        ];} , $users );

        //var_dump($users_array);die(' ==> end');

        /*$response = new JsonResponse();
        $response->setData($users_array);*/

        $response = new Response(json_encode($users_array, JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }

    /**
     * @Route("/user/{id}", name="_view_user", methods={"GET"})
     */
    public function viewUserAction($id, Request $request)
    {
        if ($id != null) {
            $repository = $this->getDoctrine()->getRepository(User::class);
            $user = $repository->findOneById($id);

            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . "/api";

            if ($user) {
                $user_array = [
                    "id" => $user->getId(),
                    "name" => $user->getRealname(),
                    "projects" => array_map( function ($project) use ($baseurl, $user){
                        return [
                            "project_name" => $project->getName(),
                            "project_url" => $baseurl . "/user/" . $user->getId() . "/project/" . $project->getId()
                        ];
                    }, $user->getProjects()->toArray()),
                ];

                /*$response = new JsonResponse();
                $response->setData($user_array);*/

                $response = new Response(json_encode($user_array, JSON_UNESCAPED_UNICODE));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        }
        throw new BadRequestHttpException ('Wrong Id for User', null, 400);
    }

    /**
     * @Route("/user/{id}/project/{id2}", name="_view_project", methods={"GET"})
     */
    public function viewProjectAction($id, $id2, Request $request)
    {

        if ($id != null && $id2 != null) {
            $repository = $this->getDoctrine()->getRepository(Project::class);
            $project = $repository->findOneById($id2);

            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . "/api";

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

                /*$response = new JsonResponse();
                $response->setData($project_array);*/

                $response = new Response(json_encode($project_array, JSON_UNESCAPED_UNICODE));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        }
        throw new BadRequestHttpException ('Wrong Id for User or Project', null, 400);
    }

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

                /*$response = new JsonResponse();
                $response->setData($task_array);*/

                $response = new Response(json_encode($task_array, JSON_UNESCAPED_UNICODE));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        }
        throw new BadRequestHttpException ('Wrong Id for User or Project or Task', null, 400);
    }

    // mantengo este metodo para ver proyectos

    /**
     * @Route("/projects", name="_list_projects", methods={"GET"})
     */
    public function listProjectsAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Project::class);
        $projects = $repository->findAll();

        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . "/api";

        $projects_array = array_map( function($project) use ($baseurl) {return [
            "id" => $project->getId(),
            "name" => $project->getName(),
            "owner" => $project->getUser()->getRealname(),
            "url" => $baseurl . "/user/" . $project->getUser()->getId() . "/project/" . $project->getId()
        ];} , $projects  );

        //var_dump($projects_array);die(' ==> end');

        /*$response = new JsonResponse();
        $response->setData($projects_array);*/

        $response = new Response(json_encode($projects_array, JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }

    /**
     * @Route("/user/{id}/project", name="_insert_project", methods={"POST"})
     */
    public function insertProjectAction($id, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // TODO los usuarios posibles dependen del permiso de usuario
        $repository_user = $this->getDoctrine()->getRepository(User::class);


        if (empty($data['name']) || empty($data['description'])) {
            $data = null;
        }

        if ($id != null && $data != null) {
            $project = new Project();
            $project->setName($data['name']);
            $project->setDescription($data['description']);

            // tengo que darle el objeto User
            $user = $repository_user->findOneById($id);
            $project->setUser($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($project);
            $entityManager->flush();

            $project_array = [
                "id" => $project->getId(),
                "name" => $project->getName(),
                "description" => $project->getDescription(),
                "owner" => $project->getUser()->getRealname()
            ];

            $response = new Response(json_encode($project_array, JSON_UNESCAPED_UNICODE));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        throw new BadRequestHttpException ('Falta Id usuario o Datos malformados', null, 400);
    }

    /**
     * @Route("/user/{id}/project/{id2}/task", name="_insert_task", methods={"POST"})
     */
    public function insertTaskAction($id, $id2, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // TODO los usuarios posibles dependen del permiso de usuario
        $repository_project = $this->getDoctrine()->getRepository(Project::class);

        // tengo que darle el objeto User
        $project = $repository_project->findOneById($id2);

        if ($project->getUser()->getId() != $id) {
            throw new BadRequestHttpException ('Este usuario no es el propietario de este proyecto', null, 400);
            die();
        }

        if (empty($data['name']) || empty($data['description']) ) {
            $data = null;
        }

        if ($id != null && $id2 != null && $data != null) {
            $task = new Task();
            $task->setName($data['name']);
            $task->setDescription($data['description']);
            $task->setProject($project);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            $task_array = [
                "id" => $task->getId(),
                "name" => $task->getName(),
                "description" => $task->getDescription(),
                "owner" => $task->getProject()->getUser()->getRealname(),
                "project" => $task->getProject()->getName()
            ];

            $response = new Response(json_encode($task_array, JSON_UNESCAPED_UNICODE));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        throw new BadRequestHttpException ('Falta Id usuario o Id proyecto o Datos malformados', null, 400);
    }
}