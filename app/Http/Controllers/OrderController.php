<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\PaymentMethodType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $orders = $user->orders()->with([
            'paymentMethods.paymentMethodType',
            'boxOrders.box',
        ])->orderBy('created_at', 'desc')->get();
        return response()->json([
            'user' => $user,
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Logic to show form for creating a new order
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        $items = [];
        $paymentMethod = null;
        if (!empty($data)) {
            // $body = json_decode($data['body'], true);
            $items = $data['items'] ?? [];
            $paymentMethod = $data['payment_method'] ?? null;
        }

        Log::info('OrderController@store', [
            'user_id' => $user ? $user->id : null,
            'items' => $items,
            'payment_method' => $paymentMethod,
            'data' => $data,
        ]);

        if (!$user || empty($items)) {
            return response()->json(['error' => 'Utilisateur non authentifié ou panier vide'], 400);
        }

        // Calcul du total
        $total = 0;
        foreach ($items as $item) {
            $total += ($item['price'] ?? $item['base_price'] ?? 0) * ($item['quantity'] ?? 1);
        }

        // Création de la commande
        $order = new Order();
        $order->user_id = $user->id;
        $order->order_number = uniqid('ORD-');
        $order->total_amount = $total;
        $order->status = 'pending';
        $order->active = true;
        $order->save();

        // Ajout des boxes à la commande (table pivot box_orders)
        foreach ($items as $item) {
            if (($item['type'] ?? null) === 'box') {
                $order->boxes()->attach($item['id'], ['quantity' => $item['quantity'] ?? 1]);
            }
            // Ici on peut gérer d'autres types (giftcard, subscription, etc.)
        }

        // (Optionnel) Enregistrer le moyen de paiement choisi
        if ($paymentMethod) {
            // On suppose que PaymentMethodType contient les types (visa, cb, etc.)
            $paymentType = PaymentMethodType::where('name', $paymentMethod)->first();
            if ($paymentType) {
                $order->paymentMethods()->create([
                    'payment_method_type_id' => $paymentType->id,
                    'amount' => $total,
                ]);
            }
        }

        return response()->json(['success' => true, 'order_id' => $order->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Logic to display a specific order by ID
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Logic to show form for editing an existing order
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Logic to update an existing order
    }
}
