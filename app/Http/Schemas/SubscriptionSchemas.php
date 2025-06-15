<?php

namespace App\Http\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Subscription",
    title: "Subscription",
    description: "User subscription model",
    type: "object"
)]
class SubscriptionSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "subscription_type_id", type: "integer", example: 1)]
    public int $subscription_type_id;

    #[OA\Property(property: "start_date", type: "string", format: "date", example: "2024-01-01")]
    public string $start_date;

    #[OA\Property(property: "end_date", type: "string", format: "date", example: "2024-12-31")]
    public string $end_date;

    #[OA\Property(property: "status", type: "string", enum: ["active", "cancelled", "expired"], example: "active")]
    public string $status;
    #[OA\Property(property: "auto_renew", type: "boolean", example: true)]
    public bool $auto_renew;
    #[OA\Property(property: "type", type: "object", nullable: true)]
    public ?SubscriptionTypeSchema $type;

    #[OA\Property(property: "deliveries", type: "array", items: new OA\Items(type: "object"), nullable: true)]
    public ?array $deliveries;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}

#[OA\Schema(
    schema: "SubscriptionType",
    title: "Subscription Type",
    description: "Subscription type model",
    type: "object"
)]
class SubscriptionTypeSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "name", type: "string", example: "Monthly Beauty Box")]
    public string $name;

    #[OA\Property(property: "description", type: "string", example: "Monthly subscription for beauty products")]
    public ?string $description;

    #[OA\Property(property: "price", type: "number", format: "float", example: 29.99)]
    public float $price;

    #[OA\Property(property: "duration_months", type: "integer", example: 1)]
    public int $duration_months;

    #[OA\Property(property: "box_id", type: "integer", example: 1)]
    public ?int $box_id;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}

#[OA\Schema(
    schema: "SubscriptionDelivery",
    title: "Subscription Delivery",
    description: "Subscription delivery model",
    type: "object"
)]
class SubscriptionDeliverySchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "subscription_id", type: "integer", example: 1)]
    public int $subscription_id;

    #[OA\Property(property: "box_id", type: "integer", example: 1)]
    public int $box_id;

    #[OA\Property(property: "delivery_date", type: "string", format: "date", example: "2024-02-01")]
    public string $delivery_date;

    #[OA\Property(property: "status", type: "string", enum: ["pending", "shipped", "delivered"], example: "delivered")]
    public string $status;

    #[OA\Property(property: "tracking_number", type: "string", example: "TR123456789")]
    public ?string $tracking_number;
    #[OA\Property(property: "box", type: "object", nullable: true)]
    public ?BoxSchema $box;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}
