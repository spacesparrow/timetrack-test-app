<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\TasksExportResponseDTO;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractFOSRestController
{
    protected function badRequestResponse(FormInterface $form): Response
    {
        return $this->handleView($this->view($form));
    }

    protected function createdResponse(string $url): Response
    {
        return $this->handleView($this->view(null, Response::HTTP_CREATED, ['Location' => $url]));
    }

    protected function showResponse(object $object): Response
    {
        return $this->handleView($this->view($object, Response::HTTP_OK));
    }

    protected function createSubmittedForm(string $formType, Request $request, ?object $object = null): FormInterface
    {
        $form = $this->createForm($formType, $object);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        return $form;
    }

    protected function exportFileDownloadResponse(TasksExportResponseDTO $responseDTO): BinaryFileResponse
    {
        return $this->file(
            $responseDTO->getTempFile(),
            $responseDTO->getFilename()
        );
    }
}
