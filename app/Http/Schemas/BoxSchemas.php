<?php

namespace App\Http\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Box",
    title: "Box",
    description: "Subscription box model",
    type: "object"
)]
class BoxSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "name", type: "string", example: "Beauty Box Premium")]
    public string $name;

    #[OA\Property(property: "description", type: "string", example: "A premium beauty box with the best cosmetic products")]
    public string $description;

    #[OA\Property(property: "base_price", type: "number", format: "float", example: 29.99)]
    public float $base_price;

    #[OA\Property(property: "active", type: "boolean", example: true)]
    public bool $active;

    #[OA\Property(property: "box_category_id", type: "integer", example: 1)]
    public int $box_category_id;
    #[OA\Property(property: "category", type: "object", nullable: true)]
    public ?BoxCategorySchema $category;

    #[OA\Property(property: "items", type: "array", items: new OA\Items(type: "object"), nullable: true)]
    public ?array $items;

    #[OA\Property(property: "average_rating", type: "number", format: "float", example: 4.5)]
    public ?float $average_rating;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}

#[OA\Schema(
    schema: "BoxCategory",
    title: "Box Category",
    description: "Box category model",
    type: "object"
)]
class BoxCategorySchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "name", type: "string", example: "Beauty")]
    public string $name;

    #[OA\Property(property: "description", type: "string", example: "Beauty and cosmetics products")]
    public ?string $description;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}

#[OA\Schema(
    schema: "Item",
    title: "Item",
    description: "Item model",
    type: "object"
)]
class ItemSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "name", type: "string", example: "Premium Lipstick")]
    public string $name;

    #[OA\Property(property: "description", type: "string", example: "High-quality matte lipstick")]
    public ?string $description;

    #[OA\Property(property: "brand", type: "string", example: "Chanel")]
    public ?string $brand;

    #[OA\Property(property: "value", type: "number", format: "float", example: 45.00)]
    public ?float $value;

    #[OA\Property(property: "quantity", type: "integer", example: 2, description: "Quantity in the box (pivot table)")]
    public ?int $quantity;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}
