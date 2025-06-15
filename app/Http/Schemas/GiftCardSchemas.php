<?php

namespace App\Http\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "GiftCard",
    title: "Gift Card",
    description: "Gift card model",
    type: "object"
)]
class GiftCardSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "code", type: "string", example: "GIFT123ABC")]
    public string $code;

    #[OA\Property(property: "value", type: "number", format: "float", example: 50.00)]
    public float $value;

    #[OA\Property(property: "remaining_value", type: "number", format: "float", example: 25.00)]
    public float $remaining_value;

    #[OA\Property(property: "gift_card_type_id", type: "integer", example: 1)]
    public int $gift_card_type_id;

    #[OA\Property(property: "user_id", type: "integer", example: 1)]
    public ?int $user_id;

    #[OA\Property(property: "status", type: "string", enum: ["active", "used", "expired"], example: "active")]
    public string $status;

    #[OA\Property(property: "expiry_date", type: "string", format: "date", example: "2025-12-31")]
    public ?string $expiry_date;
    #[OA\Property(property: "type", type: "object", nullable: true)]
    public ?GiftCardTypeSchema $type;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}

#[OA\Schema(
    schema: "GiftCardType",
    title: "Gift Card Type",
    description: "Gift card type model",
    type: "object"
)]
class GiftCardTypeSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "name", type: "string", example: "Standard Gift Card")]
    public string $name;

    #[OA\Property(property: "description", type: "string", example: "Standard gift card for any subscription")]
    public ?string $description;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}

#[OA\Schema(
    schema: "GiftCardActivation",
    title: "Gift Card Activation",
    description: "Gift card activation data",
    type: "object",
    required: ["code"]
)]
class GiftCardActivationSchema
{
    #[OA\Property(property: "code", type: "string", example: "GIFT123ABC")]
    public string $code;
}
