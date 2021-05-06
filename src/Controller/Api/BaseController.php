<?php

declare(strict_types=1);

namespace App\Controller\Api;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractFOSRestController
{
    protected function badRequestResponse(FormInterface $form): Response
    {
        return $this->handleView($this->view($form, Response::HTTP_BAD_REQUEST));
    }

    protected function createdResponse(object $object): Response
    {
        return $this->handleView($this->view($object, Response::HTTP_CREATED));
    }
}
