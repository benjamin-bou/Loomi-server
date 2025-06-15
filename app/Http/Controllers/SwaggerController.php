<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Loomi API",
    description: "API for Loomi subscription box platform"
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Local development server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
class SwaggerController extends Controller
{
    // This controller is just for OpenAPI configuration
}
