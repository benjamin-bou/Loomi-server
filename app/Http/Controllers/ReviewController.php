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
     * @OA\Post(
     *     path="/reviews",
     *     tags={"Reviews"},
     *     summary="Create a new review",
     *     description="Create a new review for a box that the user has received",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ReviewCreate")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Avis créé avec succès"),
     *             @OA\Property(property="review", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cannot review box not received",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Vous ne pouvez laisser un avis que pour des boîtes que vous avez reçues")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Review already exists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Vous avez déjà laissé un avis pour cette boîte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
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
     * @OA\Get(
     *     path="/reviews/user/{boxId}",
     *     tags={"Reviews"},
     *     summary="Get user review for a box",
     *     description="Get the authenticated user's review for a specific box",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="boxId",
     *         in="path",
     *         required=true,
     *         description="Box ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User review retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Review")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Aucun avis trouvé")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/boxes/{id}/reviews",
     *     tags={"Reviews"},
     *     summary="Get reviews for a box",
     *     description="Get all reviews for a specific box with pagination",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Box ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Box reviews retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="reviews",
     *                 allOf={@OA\Schema(ref="#/components/schemas/PaginatedResponse")}
     *             ),
     *             @OA\Property(property="average_rating", type="number", format="float", example=4.2),
     *             @OA\Property(property="total_reviews", type="integer", example=25)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Box not found",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     )
     * )
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
     * @OA\Put(
     *     path="/reviews/{id}",
     *     tags={"Reviews"},
     *     summary="Update a review",
     *     description="Update an existing review (only by the review author)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Review ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ReviewUpdate")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Avis mis à jour avec succès"),
     *             @OA\Property(property="review", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cannot modify this review",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Vous ne pouvez modifier que vos propres avis")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
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
     * @OA\Delete(
     *     path="/reviews/{id}",
     *     tags={"Reviews"},
     *     summary="Delete a review",
     *     description="Delete an existing review (only by the review author)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Review ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Avis supprimé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cannot delete this review",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Vous ne pouvez supprimer que vos propres avis")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     )
     * )
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
