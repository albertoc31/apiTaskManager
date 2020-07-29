<?php

/**
 * @author: albertosanchez
 * Nuevo Listener para gestionar excepciones
 */

// src/AppBundle/EventListener/ExceptionListener.php
namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();
        $message = sprintf(
            'My Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );

        $result = [
            "message" => $exception->getMessage(),
            "code" => $exception->getCode()
        ];

        $response = new Response(json_encode($result, JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json');

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
//            $response->headers->replace($exception->getHeaders());
        } else {
            /*
             * En vez de 500 por defecto, uso el codigo de la excepcion
             */
            if ($exception->getCode()) {
                $response->setStatusCode($exception->getCode());
            } else {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}
