<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

class ExceptionFlashSubscriber implements EventSubscriberInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return; // No hay request 
        }

        $session = $request->getSession();
        if (!$session) {
            return; // El request no tiene sesión
        }

        $exception = $event->getThrowable();

        // Agregar mensaje flash según la excepción
        if ($session instanceof FlashBagAwareSessionInterface) {
            if ($exception instanceof NotFoundHttpException) {
                $session->getFlashBag()->add('warning', 'No encontrado.');
            } elseif ($exception instanceof AccessDeniedHttpException) {
                $session->getFlashBag()->add('danger', 'Acceso denegado.');
            } else {
                $session->getFlashBag()->add('danger', 'Error inesperado.');
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }
}