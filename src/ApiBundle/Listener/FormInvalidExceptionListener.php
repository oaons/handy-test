<?php

namespace ApiBundle\Listener;

use ApiBundle\Exception\FormInvalidException;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @DI\Service("api.listener.form_invalid_exception")
 */
class FormInvalidExceptionListener
{
    /**
     * @var ViewHandlerInterface
     */
    private $viewHandler;

    /**
     * @DI\InjectParams({
     *     "viewHandler" = @DI\Inject("fos_rest.view_handler"),
     * })
     * @param ViewHandlerInterface $viewHandler
     */
    public function __construct(ViewHandlerInterface $viewHandler)
    {
        $this->viewHandler = $viewHandler;
    }

    /**
     * @DI\Observe("kernel.exception")
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof FormInvalidException) {
            $view = new View($exception->getForm(), Response::HTTP_BAD_REQUEST);
            $response = $this->viewHandler->handle($view, $event->getRequest());
            $event->setResponse($response);
        }
    }
}
