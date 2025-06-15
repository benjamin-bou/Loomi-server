<?php

namespace App\Http\Controllers;

use App\Models\GiftCard;
use Illuminate\Http\Request;
use App\Models\GiftCardType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GiftCardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/gift-cards",
     *     tags={"Gift Cards"},
     *     summary="Get all gift card types",
     *     description="Retrieve all active gift card types available for purchase",
     *     @OA\Response(
     *         response=200,
     *         description="List of active gift card types",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/GiftCardType")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json(GiftCardType::where('active', 1)->get());
    }

    /**
     * @OA\Post(
     *     path="/gift-cards/activate",
     *     tags={"Gift Cards"},
     *     summary="Activate gift card",
     *     description="Activate a gift card using its code and assign it to the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/GiftCardActivation")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gift card activated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Carte cadeau activée avec succès!"),
     *             @OA\Property(property="gift_card", ref="#/components/schemas/GiftCard")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Gift card already activated or invalid",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cette carte cadeau est déjà utilisée")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Gift card not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Code de carte cadeau invalide")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function activate(Request $request)
    {
        // Vérifier que l'utilisateur est connecté
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour activer une carte cadeau'
            ], 401);
        }

        $request->validate([
            'code' => 'required|string|max:20'
        ]);

        $code = strtoupper(trim($request->code));

        try {
            // Rechercher la carte cadeau par son code
            $giftCard = GiftCard::with('giftCardType')
                ->where('code', $code)
                ->first();

            if (!$giftCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code de carte cadeau invalide'
                ], 404);
            }

            // Vérifier si la carte cadeau n'est pas déjà utilisée
            if ($giftCard->used_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette carte cadeau a déjà été utilisée'
                ], 400);
            }

            // Vérifier si la carte cadeau n'a pas déjà été activée par quelqu'un d'autre
            if ($giftCard->activated_by && $giftCard->activated_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette carte cadeau a déjà été activée par un autre utilisateur'
                ], 400);
            }

            // Vérifier si la carte cadeau n'est pas expirée
            if ($giftCard->expiration_date && $giftCard->expiration_date < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette carte cadeau a expiré'
                ], 400);
            }

            // Marquer la carte comme activée par l'utilisateur connecté
            $giftCard->activated_by = Auth::id();
            $giftCard->used_at = now();
            $giftCard->save();

            return response()->json([
                'success' => true,
                'message' => 'Carte cadeau activée avec succès',
                'gift_card' => [
                    'id' => $giftCard->id,
                    'code' => $giftCard->code,
                    'gift_card_type' => $giftCard->giftCardType ? [
                        'name' => $giftCard->giftCardType->name,
                        'base_price' => $giftCard->giftCardType->base_price
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error activating gift card', [
                'error' => $e->getMessage(),
                'code' => $code,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation de la carte cadeau'
            ], 500);
        }
    }

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
     *             @OA\Items(ref="#/components/schemas/GiftCard")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     )
     * )
     */
    public function getUserGiftCards()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // Récupérer les cartes cadeaux activées par l'utilisateur connecté
            $giftCards = GiftCard::with(['giftCardType', 'order'])
                ->where('activated_by', $user->id)
                ->whereNotNull('code') // Cartes qui ont un code (créées)
                ->get()
                ->map(function ($giftCard) {
                    return [
                        'id' => $giftCard->id,
                        'code' => $giftCard->code,
                        'expiration_date' => $giftCard->expiration_date,
                        'used_at' => $giftCard->used_at,
                        'gift_card_type' => $giftCard->giftCardType ? [
                            'id' => $giftCard->giftCardType->id,
                            'name' => $giftCard->giftCardType->name,
                            'description' => $giftCard->giftCardType->description,
                            'base_price' => $giftCard->giftCardType->base_price
                        ] : null
                    ];
                });

            return response()->json($giftCards->toArray());
        } catch (\Exception $e) {
            Log::error('Error fetching user gift cards', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des cartes cadeaux'
            ], 500);
        }
    }
}
