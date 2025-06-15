<?php

namespace App\Http\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Order",
    title: "Order",
    description: "Order model",
    type: "object"
)]
class OrderSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "user_id", type: "integer", example: 1)]
    public int $user_id;

    #[OA\Property(property: "subscription_id", type: "integer", example: 1)]
    public ?int $subscription_id;

    #[OA\Property(property: "total_amount", type: "number", format: "float", example: 29.99)]
    public float $total_amount;

    #[OA\Property(property: "status", type: "string", enum: ["pending", "paid", "shipped", "delivered", "cancelled"], example: "paid")]
    public string $status;

    #[OA\Property(property: "payment_method_id", type: "integer", example: 1)]
    public ?int $payment_method_id;

    #[OA\Property(property: "shipping_address", type: "string", example: "123 Main St, Paris, France")]
    public ?string $shipping_address;

    #[OA\Property(property: "order_date", type: "string", format: "date-time")]
    public string $order_date;
    #[OA\Property(property: "user", type: "object", nullable: true)]
    public ?UserSchema $user;

    #[OA\Property(property: "subscription", type: "object", nullable: true)]
    public ?SubscriptionSchema $subscription;

    #[OA\Property(property: "payment_method", type: "object", nullable: true)]
    public ?PaymentMethodSchema $payment_method;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}

#[OA\Schema(
    schema: "OrderCreate",
    title: "Order Creation",
    description: "Order creation data",
    type: "object",
    required: ["subscription_type_id", "payment_method_id"]
)]
class OrderCreateSchema
{
    #[OA\Property(property: "subscription_type_id", type: "integer", example: 1)]
    public int $subscription_type_id;

    #[OA\Property(property: "payment_method_id", type: "integer", example: 1)]
    public int $payment_method_id;

    #[OA\Property(property: "shipping_address", type: "string", example: "123 Main St, Paris, France")]
    public ?string $shipping_address;

    #[OA\Property(property: "gift_card_code", type: "string", example: "GIFT123")]
    public ?string $gift_card_code;
}

#[OA\Schema(
    schema: "PaymentMethod",
    title: "Payment Method",
    description: "Payment method model",
    type: "object"
)]
class PaymentMethodSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "name", type: "string", example: "Credit Card")]
    public string $name;

    #[OA\Property(property: "description", type: "string", example: "Pay with credit or debit card")]
    public ?string $description;

    #[OA\Property(property: "type_id", type: "integer", example: 1)]
    public int $type_id;

    #[OA\Property(property: "active", type: "boolean", example: true)]
    public bool $active;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}
