<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

class BoxSwaggerController extends Controller
{
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
     *                 @OA\Property(property="box_category_id", type="integer"),
     *                 @OA\Property(
     *                     property="category",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="description", type="string")
     *                 ),
     *                 @OA\Property(property="average_rating", type="number", format="float"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function index() {}

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
     *             @OA\Property(property="active", type="boolean"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="brand", type="string"),
     *                     @OA\Property(property="value", type="number", format="float"),
     *                     @OA\Property(property="quantity", type="integer")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="category",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Box not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function show() {}

    /**
     * @OA\Get(
     *     path="/admin/boxes",
     *     tags={"Boxes"},
     *     summary="Get all boxes (admin)",
     *     description="Retrieve all boxes including inactive ones (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all boxes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="base_price", type="number", format="float"),
     *                 @OA\Property(property="active", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Admin access required",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function adminIndex() {}

    /**
     * @OA\Put(
     *     path="/admin/boxes/{id}",
     *     tags={"Boxes"},
     *     summary="Update box (admin)",
     *     description="Update box information (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Box ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "base_price"},
     *             @OA\Property(property="name", type="string", example="Updated Box Name"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="base_price", type="number", format="float", example=39.99)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Box updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Box updated successfully"),
     *             @OA\Property(property="box", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Admin access required",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function update() {}
}
