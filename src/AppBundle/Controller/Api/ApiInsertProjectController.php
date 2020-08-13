<?php


namespace AppBundle\Controller\Api;


use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/api", name="api")
 */
class ApiInsertProjectController extends ApiController
{

    /**
     * @Route("/user/{id}/project", name="_insert_project", methods={"POST"})
     */
    public function insertProjectAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $data = $this->getRequest($request);

        if (empty($data['name']) || empty($data['description'])) {
            $data = null;
        }

        // TODO los usuarios posibles dependen del permiso de usuario

        $logged_user = $this->getUser();
        if ($logged_user->getId() != $id) {
            throw new BadRequestHttpException ('No tienes permisos para agregar proyectos a este usuario', null, 422);
        }

        if ($id != null && $data != null) {
            $project = new Project();
            $project->setName($data['name']);
            $project->setDescription($data['description']);

            // tengo que darle el objeto User
            $repository_user = $this->getDoctrine()->getRepository(User::class);
            $user = $repository_user->findOneById($id);
            $project->setUser($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($project);
            $entityManager->flush();

            $baseurl = $this->getBaseURL($request);
            $create_task_url = $baseurl . "/user/" . $user->getId() . "/project/" . $project->getId() . "/task";

            $project_array = [
                "id" => $project->getId(),
                "name" => $project->getName(),
                "description" => $project->getDescription(),
                "owner" => $project->getUser()->getRealname(),
                "create_task_url" => $create_task_url
            ];

            $response = $this->setResponse($project_array);
            return $response;
        }

        throw new BadRequestHttpException ('Falta Id usuario o Datos malformados', null, 422);
    }
}