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
     * Display a listing of the gift cards.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(GiftCardType::where('active', 1)->get());
    }

    /**
     * Activer une carte cadeau avec son code
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function activate(Request $request)
    {
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
            $giftCard->save();

            Log::info('GiftCard activated successfully', [
                'gift_card_id' => $giftCard->id,
                'code' => $code,
                'gift_card_type' => $giftCard->giftCardType->name ?? 'Unknown',
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Carte cadeau activée avec succès',
                'giftCard' => [
                    'id' => $giftCard->id,
                    'code' => $giftCard->code,
                    'type' => $giftCard->giftCardType->name ?? 'Carte cadeau',
                    'expiration_date' => $giftCard->expiration_date,
                    'giftCardType' => $giftCard->giftCardType
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
     * Récupérer les cartes cadeaux activées par l'utilisateur connecté
     *
     * @return \Illuminate\Http\Response
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
                ->whereNull('used_at') // Cartes non utilisées
                ->get()
                ->map(function ($giftCard) {
                    return [
                        'id' => $giftCard->id,
                        'code' => $giftCard->code,
                        'expiration_date' => $giftCard->expiration_date,
                        'used_at' => $giftCard->used_at,
                        'giftCardType' => $giftCard->giftCardType ? [
                            'id' => $giftCard->giftCardType->id,
                            'name' => $giftCard->giftCardType->name,
                            'description' => $giftCard->giftCardType->description,
                            'base_price' => $giftCard->giftCardType->base_price
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'giftCards' => $giftCards
            ]);
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
