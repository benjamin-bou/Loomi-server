<?php

namespace App\Http\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ApiResponse",
    title: "API Response",
    description: "Standard API response structure",
    type: "object"
)]
class ApiResponseSchema
{
    #[OA\Property(property: "success", type: "boolean", example: true)]
    public bool $success;

    #[OA\Property(property: "message", type: "string", example: "Operation completed successfully")]
    public string $message;

    #[OA\Property(property: "data", type: "object", nullable: true)]
    public mixed $data;
}

#[OA\Schema(
    schema: "ApiError",
    title: "API Error",
    description: "API error response structure",
    type: "object"
)]
class ApiErrorSchema
{
    #[OA\Property(property: "success", type: "boolean", example: false)]
    public bool $success;

    #[OA\Property(property: "message", type: "string", example: "An error occurred")]
    public string $message;

    #[OA\Property(property: "error", type: "string", example: "Detailed error message")]
    public ?string $error;
    #[OA\Property(
        property: "errors",
        type: "object"
    )]
    public ?object $errors;
}

#[OA\Schema(
    schema: "ValidationError",
    title: "Validation Error",
    description: "Validation error response",
    type: "object"
)]
class ValidationErrorSchema
{
    #[OA\Property(property: "message", type: "string", example: "The given data was invalid.")]
    public string $message;
    #[OA\Property(
        property: "errors",
        type: "object"
    )]
    public object $errors;
}

#[OA\Schema(
    schema: "PaginatedResponse",
    title: "Paginated Response",
    description: "Paginated API response structure",
    type: "object"
)]
class PaginatedResponseSchema
{
    #[OA\Property(
        property: "data",
        type: "array",
        items: new OA\Items(type: "object")
    )]
    public array $data;

    #[OA\Property(property: "current_page", type: "integer", example: 1)]
    public int $current_page;

    #[OA\Property(property: "last_page", type: "integer", example: 5)]
    public int $last_page;

    #[OA\Property(property: "per_page", type: "integer", example: 15)]
    public int $per_page;

    #[OA\Property(property: "total", type: "integer", example: 67)]
    public int $total;

    #[OA\Property(property: "from", type: "integer", example: 1)]
    public int $from;

    #[OA\Property(property: "to", type: "integer", example: 15)]
    public int $to;

    #[OA\Property(property: "first_page_url", type: "string", example: "http://localhost:8000/api/boxes?page=1")]
    public string $first_page_url;

    #[OA\Property(property: "last_page_url", type: "string", example: "http://localhost:8000/api/boxes?page=5")]
    public string $last_page_url;

    #[OA\Property(property: "next_page_url", type: "string", nullable: true, example: "http://localhost:8000/api/boxes?page=2")]
    public ?string $next_page_url;

    #[OA\Property(property: "prev_page_url", type: "string", nullable: true, example: null)]
    public ?string $prev_page_url;

    #[OA\Property(property: "path", type: "string", example: "http://localhost:8000/api/boxes")]
    public string $path;
}
