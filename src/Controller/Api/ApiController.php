<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
abstract class ApiController extends BaseController
{
    /**
     * Returns a JSON response with the given data and status code
     * Override parent method to ensure consistent API response format
     */
    protected function jsonResponse(
        mixed $data,
        int $status = Response::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        $headers = array_merge($headers, [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization'
        ]);

        return parent::jsonResponse($data, $status, $headers);
    }

    /**
     * Returns a success response with API-specific format
     */
    protected function apiSuccessResponse(
        mixed $data = null,
        string $message = 'Success',
        int $status = Response::HTTP_OK
    ): JsonResponse {
        return $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ], $status);
    }

    /**
     * Returns an error response with API-specific format
     */
    protected function apiErrorResponse(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        mixed $errors = null
    ): JsonResponse {
        return $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => time()
        ], $status);
    }
} 