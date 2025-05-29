<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Box;
use App\Models\Order;
use App\Models\SubscriptionDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Créer un nouvel avis
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'box_id' => 'required|exists:boxes,id',
            'rating' => 'required|numeric|min:0.5|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $boxId = $validatedData['box_id'];

        // Vérifier que l'utilisateur a bien reçu cette boîte
        if (!$this->hasUserReceivedBox($user->id, $boxId)) {
            return response()->json([
                'error' => 'Vous ne pouvez laisser un avis que pour des boîtes que vous avez reçues'
            ], 403);
        }

        // Vérifier si l'utilisateur a déjà laissé un avis pour cette boîte
        $existingReview = Review::where('user_id', $user->id)
            ->where('box_id', $boxId)
            ->first();

        if ($existingReview) {
            return response()->json([
                'error' => 'Vous avez déjà laissé un avis pour cette boîte'
            ], 409);
        }

        try {
            $review = Review::create([
                'user_id' => $user->id,
                'box_id' => $boxId,
                'rating' => $validatedData['rating'],
                'comment' => $validatedData['comment'],
            ]);

            $review->load(['user', 'box']);

            return response()->json([
                'message' => 'Avis créé avec succès',
                'review' => $review
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating review: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la création de l\'avis'], 500);
        }
    }

    /**
     * Récupérer l'avis d'un utilisateur pour une boîte
     */
    public function getUserReview(Request $request, $boxId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $review = Review::where('user_id', $user->id)
            ->where('box_id', $boxId)
            ->with(['user', 'box'])
            ->first();

        if (!$review) {
            return response()->json(['review' => null], 200);
        }

        return response()->json(['review' => $review], 200);
    }

    /**
     * Récupérer tous les avis d'une boîte
     */
    public function getBoxReviews($boxId)
    {
        $box = Box::find($boxId);

        if (!$box) {
            return response()->json(['error' => 'Boîte non trouvée'], 404);
        }

        $reviews = Review::where('box_id', $boxId)
            ->with(['user' => function ($query) {
                $query->select('id', 'first_name', 'last_name');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $averageRating = $reviews->avg('rating');
        $totalReviews = $reviews->count();

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews,
            'rating_distribution' => $this->getRatingDistribution($reviews)
        ], 200);
    }

    /**
     * Mettre à jour un avis existant
     */
    public function update(Request $request, $reviewId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $review = Review::find($reviewId);

        if (!$review) {
            return response()->json(['error' => 'Avis non trouvé'], 404);
        }

        if ($review->user_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé à modifier cet avis'], 403);
        }

        $validatedData = $request->validate([
            'rating' => 'required|numeric|min:0.5|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        try {
            $review->update($validatedData);
            $review->load(['user', 'box']);

            return response()->json([
                'message' => 'Avis mis à jour avec succès',
                'review' => $review
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating review: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour de l\'avis'], 500);
        }
    }

    /**
     * Supprimer un avis
     */
    public function destroy($reviewId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $review = Review::find($reviewId);

        if (!$review) {
            return response()->json(['error' => 'Avis non trouvé'], 404);
        }

        if ($review->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Non autorisé à supprimer cet avis'], 403);
        }

        try {
            $review->delete();
            return response()->json(['message' => 'Avis supprimé avec succès'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting review: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la suppression de l\'avis'], 500);
        }
    }

    /**
     * Vérifier si un utilisateur a reçu une boîte spécifique
     */
    private function hasUserReceivedBox($userId, $boxId)
    {
        // Vérifier dans les commandes directes avec statut delivered
        $hasOrderedBox = Order::where('user_id', $userId)
            ->whereIn('status', ['delivered', 'completed'])
            ->whereHas('boxOrders', function ($query) use ($boxId) {
                $query->where('box_id', $boxId);
            })
            ->exists();

        if ($hasOrderedBox) {
            return true;
        }

        // Vérifier dans les livraisons d'abonnement
        $hasSubscriptionDelivery = SubscriptionDelivery::where('box_id', $boxId)
            ->whereHas('subscription.order', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereNotNull('delivered_at')
            ->exists();

        return $hasSubscriptionDelivery;
    }

    /**
     * Calculer la distribution des notes
     */
    private function getRatingDistribution($reviews)
    {
        $distribution = [
            '5' => 0,
            '4.5' => 0,
            '4' => 0,
            '3.5' => 0,
            '3' => 0,
            '2.5' => 0,
            '2' => 0,
            '1.5' => 0,
            '1' => 0,
            '0.5' => 0,
        ];

        foreach ($reviews as $review) {
            $ratingKey = (string)$review->rating;
            if (isset($distribution[$ratingKey])) {
                $distribution[$ratingKey]++;
            }
        }

        return $distribution;
    }
}
