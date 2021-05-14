<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\TasksExportResponseDTO;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Base controller where general controller methods stored
 */
class BaseController extends AbstractFOSRestController
{
    /*
     * Returns 422 Validation Failed response with all messages in body
     */
    protected function badRequestResponse(FormInterface $form): Response
    {
        return $this->handleView($this->view($form));
    }

    /*
     * Returns 201 Created response with url to created object in Location header
     */
    protected function createdResponse(string $url): Response
    {
        return $this->handleView($this->view(null, Response::HTTP_CREATED, ['Location' => $url]));
    }

    /*
     * Returns 200 OK response with provided object in body
     */
    protected function showResponse(object $object): Response
    {
        return $this->handleView($this->view($object, Response::HTTP_OK));
    }

    /*
     * Create submitted provided FormType with request data and object with it if passed
     */
    protected function createSubmittedForm(string $formType, Request $request, ?object $object = null): FormInterface
    {
        $form = $this->createForm($formType, $object);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        return $form;
    }

    /*
     * Returns 200 OK response for downloading file with special Content-Type and Content-Disposition headers
     */
    protected function exportFileDownloadResponse(TasksExportResponseDTO $responseDTO): BinaryFileResponse
    {
        return $this->file(
            $responseDTO->getTempFile(),
            $responseDTO->getFilename()
        );
    }

    /*
     * Create submitted provided FormType with request query data and object with if if passed
     */
    public function createGetForm(string $typeClass, Request $request, object $object = null): FormInterface
    {
        $form = $this->createForm($typeClass, $object);
        $form->submit($request->query->all());

        return $form;
    }
}
