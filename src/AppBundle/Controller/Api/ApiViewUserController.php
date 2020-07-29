<?php


namespace AppBundle\Controller\Api;


use AppBundle\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/api", name="api")
 */
class ApiViewUserController extends ApiController
{

    /**
     * @Route("/user/{id}", name="_view_user", methods={"GET"})
     */
    public function viewUserAction($id, Request $request)
    {
        if ($id != null) {
            $repository = $this->getDoctrine()->getRepository(User::class);
            $user = $repository->findOneById($id);

            $baseurl = $this->getBaseURL($request);

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

                $response = $this->setResponse($user_array);
                return $response;
            }
        }
        throw new BadRequestHttpException ('Wrong Id for User', null, 422);
    }
}