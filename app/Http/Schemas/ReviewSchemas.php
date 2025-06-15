<?php

namespace App\Http\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Review",
    title: "Review",
    description: "Product review model",
    type: "object"
)]
class ReviewSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "user_id", type: "integer", example: 1)]
    public int $user_id;

    #[OA\Property(property: "box_id", type: "integer", example: 1)]
    public int $box_id;

    #[OA\Property(property: "rating", type: "integer", minimum: 1, maximum: 5, example: 5)]
    public int $rating;

    #[OA\Property(property: "comment", type: "string", example: "Amazing box with great products!")]
    public ?string $comment;
    #[OA\Property(property: "user", type: "object", nullable: true)]
    public ?UserSchema $user;

    #[OA\Property(property: "box", type: "object", nullable: true)]
    public ?BoxSchema $box;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}

#[OA\Schema(
    schema: "ReviewCreate",
    title: "Review Creation",
    description: "Review creation data",
    type: "object",
    required: ["box_id", "rating"]
)]
class ReviewCreateSchema
{
    #[OA\Property(property: "box_id", type: "integer", example: 1)]
    public int $box_id;

    #[OA\Property(property: "rating", type: "integer", minimum: 1, maximum: 5, example: 5)]
    public int $rating;

    #[OA\Property(property: "comment", type: "string", example: "Amazing box with great products!")]
    public ?string $comment;
}

#[OA\Schema(
    schema: "ReviewUpdate",
    title: "Review Update",
    description: "Review update data",
    type: "object"
)]
class ReviewUpdateSchema
{
    #[OA\Property(property: "rating", type: "integer", minimum: 1, maximum: 5, example: 4)]
    public ?int $rating;

    #[OA\Property(property: "comment", type: "string", example: "Updated: Good box with nice products!")]
    public ?string $comment;
}
