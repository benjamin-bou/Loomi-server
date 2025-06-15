<?php

namespace App\Http\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    title: "User",
    description: "User model",
    type: "object"
)]
class UserSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "first_name", type: "string", example: "John")]
    public string $first_name;

    #[OA\Property(property: "last_name", type: "string", example: "Doe")]
    public string $last_name;

    #[OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com")]
    public string $email;

    #[OA\Property(property: "role", type: "string", enum: ["user", "admin"], example: "user")]
    public string $role;

    #[OA\Property(property: "address", type: "string", example: "123 Main St, Paris, France")]
    public ?string $address;

    #[OA\Property(property: "email_verified_at", type: "string", format: "date-time", nullable: true)]
    public ?string $email_verified_at;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}

#[OA\Schema(
    schema: "UserRegistration",
    title: "User Registration",
    description: "User registration data",
    type: "object",
    required: ["first_name", "last_name", "email", "password"]
)]
class UserRegistrationSchema
{
    #[OA\Property(property: "first_name", type: "string", example: "John")]
    public string $first_name;

    #[OA\Property(property: "last_name", type: "string", example: "Doe")]
    public string $last_name;

    #[OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com")]
    public string $email;

    #[OA\Property(property: "password", type: "string", format: "password", example: "password123")]
    public string $password;

    #[OA\Property(property: "address", type: "string", example: "123 Main St, Paris, France")]
    public ?string $address;
}

#[OA\Schema(
    schema: "UserLogin",
    title: "User Login",
    description: "User login credentials",
    type: "object",
    required: ["email", "password"]
)]
class UserLoginSchema
{
    #[OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com")]
    public string $email;

    #[OA\Property(property: "password", type: "string", format: "password", example: "password123")]
    public string $password;
}

#[OA\Schema(
    schema: "AuthToken",
    title: "Authentication Token",
    description: "JWT authentication token response",
    type: "object"
)]
class AuthTokenSchema
{
    #[OA\Property(property: "access_token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")]
    public string $access_token;

    #[OA\Property(property: "token_type", type: "string", example: "bearer")]
    public string $token_type;

    #[OA\Property(property: "expires_in", type: "integer", example: 3600)]
    public int $expires_in;
    #[OA\Property(property: "user", type: "object")]
    public UserSchema $user;
}
