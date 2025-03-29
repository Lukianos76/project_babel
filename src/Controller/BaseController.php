<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends AbstractController
{
    /**
     * Returns a JSON response with the given data and status code
     */
    protected function jsonResponse(
        mixed $data,
        int $status = Response::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        return $this->json($data, $status, $headers);
    }

    /**
     * Returns a success response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $status = Response::HTTP_OK
    ): JsonResponse {
        return $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Returns an error response
     */
    protected function errorResponse(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        mixed $errors = null
    ): JsonResponse {
        return $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
} 