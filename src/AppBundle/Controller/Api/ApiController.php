<?php


namespace AppBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
//use Symfony\Component\Security\Core\Exception\BadCredentialsException;


/**
 * @Route("/api", name="api")
 */
abstract class ApiController extends Controller
{
    /*protected $data;

    protected $response;*/

    /*public function __construct(Request $request)
    {
        $this->data = json_decode($request->getContent(), true);
    }*/

    protected function getRequest(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        return $data;
    }

    protected function setResponse($array)
    {
        $response = new Response();
        $data = json_encode($array, JSON_UNESCAPED_UNICODE);
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    protected function getBaseURL(Request $request)
    {
        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . "/api";
        return $baseurl;
    }
}