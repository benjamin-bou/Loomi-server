<?php

namespace App\Http\Controllers;


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
#[OA\Tag(
    name: "Authentication",
    description: "User authentication endpoints"
)]
#[OA\Tag(
    name: "Boxes",
    description: "Subscription boxes management"
)]
#[OA\Tag(
    name: "Subscriptions",
    description: "User subscriptions management"
)]
#[OA\Tag(
    name: "Orders",
    description: "Order management"
)]
#[OA\Tag(
    name: "Gift Cards",
    description: "Gift cards management"
)]
#[OA\Tag(
    name: "Reviews",
    description: "Product reviews and ratings"
)]
#[OA\Tag(
    name: "Deliveries",
    description: "Delivery management"
)]
class ApiDocumentationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     description="Create a new user account",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"firstName", "lastName", "email", "password"},
     *             @OA\Property(property="firstName", type="string", example="John"),
     *             @OA\Property(property="lastName", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="address", type="string", example="123 Main St, Paris, France")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(
     *                 property="user", 
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="role", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register() {}

    /**
     * @OA\Post(
     *     path="/login",
     *     tags={"Authentication"},
     *     summary="Login user",
     *     description="Authenticate user and return JWT token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(
     *                 property="user", 
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="role", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login() {}

    /**
     * @OA\Get(
     *     path="/profile",
     *     tags={"Authentication"},
     *     summary="Get user profile",
     *     description="Get current authenticated user profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     )
     * )
     */
    public function getProfile() {}

    /**
     * @OA\Post(
     *     path="/profile",
     *     tags={"Authentication"},
     *     summary="Update user profile",
     *     description="Update current user profile information",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="address", type="string", example="123 Main St, Paris, France"),
     *             @OA\Property(property="city", type="string", example="Paris"),
     *             @OA\Property(property="zipcode", type="string", example="75001")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     )
     * )
     */
    public function updateProfile() {}

    /**
     * @OA\Post(
     *     path="/refresh",
     *     tags={"Authentication"},
     *     summary="Refresh JWT token",
     *     description="Refresh the JWT token",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     )
     * )
     */
    public function refresh() {}

    /**
     * @OA\Get(
     *     path="/boxes",
     *     tags={"Boxes"},
     *     summary="Get all active boxes",
     *     description="Retrieve all active subscription boxes",
     *     @OA\Response(
     *         response=200,
     *         description="List of active boxes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="base_price", type="number", format="float"),
     *                 @OA\Property(property="active", type="boolean"),
     *                 @OA\Property(
     *                     property="category",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getBoxes() {}

    /**
     * @OA\Get(
     *     path="/boxes/{id}",
     *     tags={"Boxes"},
     *     summary="Get box details",
     *     description="Retrieve detailed information about a specific box",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Box ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Box details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="base_price", type="number", format="float"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="brand", type="string"),
     *                     @OA\Property(property="quantity", type="integer")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getBoxDetails() {}

    /**
     * @OA\Get(
     *     path="/subscriptions",
     *     tags={"Subscriptions"},
     *     summary="Get all subscription types",
     *     description="Retrieve all available subscription types",
     *     @OA\Response(
     *         response=200,
     *         description="List of subscription types",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number", format="float"),
     *                 @OA\Property(property="duration_months", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function getSubscriptions() {}

    /**
     * @OA\Get(
     *     path="/my-subscription",
     *     tags={"Subscriptions"},
     *     summary="Get current user subscription",
     *     description="Retrieve current user's active subscription",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Current subscription details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="subscription", type="object"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="gift_card_extensions", type="object")
     *         )
     *     )
     * )
     */
    public function getCurrentSubscription() {}

    /**
     * @OA\Get(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Get user orders",
     *     description="Retrieve all orders for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User orders retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="orders", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getOrders() {}

    /**
     * @OA\Post(
     *     path="/order",
     *     tags={"Orders"},
     *     summary="Create new order",
     *     description="Create a new order with subscription or gift cards",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"items", "payment_method"},
     *             @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="payment_method", type="string", example="cb"),
     *             @OA\Property(property="gift_card_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="order", type="object")
     *         )
     *     )
     * )
     */
    public function createOrder() {}

    /**
     * @OA\Get(
     *     path="/gift-cards",
     *     tags={"Gift Cards"},
     *     summary="Get all gift card types",
     *     description="Retrieve all active gift card types",
     *     @OA\Response(
     *         response=200,
     *         description="List of gift card types",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function getGiftCards() {}

    /**
     * @OA\Post(
     *     path="/gift-cards/activate",
     *     tags={"Gift Cards"},
     *     summary="Activate gift card",
     *     description="Activate a gift card using its code",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="GIFT123ABC")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gift card activated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="gift_card", type="object")
     *         )
     *     )
     * )
     */
    public function activateGiftCard() {}

    /**
     * @OA\Get(
     *     path="/my-gift-cards",
     *     tags={"Gift Cards"},
     *     summary="Get user gift cards",
     *     description="Retrieve all gift cards belonging to the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User gift cards retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     */
    public function getUserGiftCards() {}

    /**
     * @OA\Get(
     *     path="/boxes/{id}/reviews",
     *     tags={"Reviews"},
     *     summary="Get reviews for a box",
     *     description="Get all reviews for a specific box",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Box ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Box reviews retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="reviews", type="object"),
     *             @OA\Property(property="average_rating", type="number", format="float"),
     *             @OA\Property(property="total_reviews", type="integer")
     *         )
     *     )
     * )
     */
    public function getBoxReviews() {}

    /**
     * @OA\Post(
     *     path="/reviews",
     *     tags={"Reviews"},
     *     summary="Create a new review",
     *     description="Create a new review for a box",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"box_id", "rating"},
     *             @OA\Property(property="box_id", type="integer", example=1),
     *             @OA\Property(property="rating", type="number", format="float", minimum=0.5, maximum=5, example=4.5),
     *             @OA\Property(property="comment", type="string", example="Great box!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="review", type="object")
     *         )
     *     )
     * )
     */
    public function createReview() {}

    /**
     * @OA\Get(
     *     path="/profile/deliveries",
     *     tags={"Deliveries"},
     *     summary="Get user deliveries",
     *     description="Retrieve all deliveries for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User deliveries retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="delivery_type", type="string"),
     *                 @OA\Property(property="box_name", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="delivery_date", type="string", format="date-time"),
     *                 @OA\Property(property="can_review", type="boolean")
     *             )
     *         )
     *     )
     * )
     */
    public function getUserDeliveries() {}

    /**
     * @OA\Get(
     *     path="/payment-methods",
     *     tags={"Orders"},
     *     summary="Get available payment methods",
     *     description="Retrieve all available payment methods",
     *     @OA\Response(
     *         response=200,
     *         description="Payment methods retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="key", type="string", example="cb"),
     *                 @OA\Property(property="label", type="string", example="Carte bancaire")
     *             )
     *         )
     *     )
     * )
     */
    public function getPaymentMethods() {}
}
