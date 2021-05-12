<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\TasksExportResponseDTO;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class BaseController extends AbstractFOSRestController
{
    /**
     * @param FormInterface $form
     * @return Response
     */
    protected function badRequestResponse(FormInterface $form): Response
    {
        return $this->handleView($this->view($form));
    }

    /**
     * @param string $url
     * @return Response
     */
    protected function createdResponse(string $url): Response
    {
        return $this->handleView($this->view(null, Response::HTTP_CREATED, ['Location' => $url]));
    }

    /**
     * @param object $object
     * @return Response
     */
    protected function showResponse(object $object): Response
    {
        return $this->handleView($this->view($object, Response::HTTP_OK));
    }

    /**
     * @param string $formType
     * @param Request $request
     * @param object|null $object
     * @return FormInterface
     */
    protected function createSubmittedForm(string $formType, Request $request, ?object $object = null): FormInterface
    {
        $form = $this->createForm($formType, $object);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        return $form;
    }

    /**
     * @param TasksExportResponseDTO $responseDTO
     * @return BinaryFileResponse
     */
    protected function exportFileDownloadResponse(TasksExportResponseDTO $responseDTO): BinaryFileResponse
    {
        return $this->file(
            $responseDTO->getTempFile(),
            $responseDTO->getFilename()
        );
    }
}
