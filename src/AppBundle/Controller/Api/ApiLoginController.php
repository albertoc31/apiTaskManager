<?php


namespace AppBundle\Controller\Api;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * @Route("/api", name="api")
 */
class ApiLoginController extends ApiController
{
    /**
     * @Route("/login", methods={"POST"})
     */
    public function apiLoginAction(Request $request)
    {
        $userdata = $this->getRequest($request);

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['username' => $userdata['username']]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $isValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $userdata['password']);
        if (!$isValid) {
            throw new BadCredentialsException();
        }
        $expiracy = $this->getParameter('api_token_expiracy');
        $token = $this->get('lexik_jwt_authentication.encoder')
            ->encode([
                'username' => $user->getUsername(),
                'exp' => time() + $expiracy // in parameters.yml
            ]);

        $baseurl = $this->getBaseURL($request);
        $create_project_url = $baseurl . "/user/" . $user->getId() . "/project";

        $login_array = [
            "create_projects_url" => $create_project_url,
            "token" => $token
        ];

        $response = $this->setResponse($login_array);
        return $response;
    }

}