<?php

namespace App\Http\Controllers;

use App\Models\GiftCard;
use Illuminate\Http\Request;
use App\Models\GiftCardType;

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
}
