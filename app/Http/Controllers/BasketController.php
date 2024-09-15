<?php

namespace App\Http\Controllers;

use App\Models\Basket;
use App\Models\BasketItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BasketController extends Controller
{
    public function newBasket(){
        $user = Auth::user();
        $basket = Basket::create([
            'user_id' => $user->id,
            'expires_at' => now()->addDay(),
            'token' => md5(uniqid())
        ]);

        return response()->json(['message' => 'Basket created successfully', 'basket' => $basket], 200);

    }

    public function itemAdd(Request $request){


        $data = $request->validate([
            'basket_token' => 'required|string',
            'product_id' => 'required|numeric',
        ]);
        $basket = Basket::where('token',$data['basket_token'])->first();
        if(!$basket){
            return response()->json(['error' => 'Basket not found.'], 404);
        }
        if ($basket->user_id != \auth()->user()->id){
            return response()->json(['error' => 'Basket is not yours.'], 404);
        }
        $product = Product::find($data['product_id']);
        if (!($product && $product->quantity > 1)){
            return response()->json(['error' => 'product not found or not enough quantity.'], 404);
        }
        $basketItemsId = $basket->items->pluck('product_id')->toArray();
        if ($basketItemsId && in_array($product->id,$basketItemsId)){
            return response()->json(['error' => 'product already exist in basket.'], 201);
        }
        BasketItem::create([
            'basket_id' => $basket->id,
            'product_id' => $product->id
        ]);

        return response()->json(['message' => 'item added to basket successfully'], 200);

    }

    public function itemDelete(Request $request){


        $data = $request->validate([
            'basket_token' => 'required|string',
            'product_id' => 'required|numeric',
        ]);
        $basket = Basket::where('token',$data['basket_token'])->first();
        if(!$basket){
            return response()->json(['error' => 'Basket not found.'], 404);
        }
        if ($basket->user_id != \auth()->user()->id){
            return response()->json(['error' => 'Basket is not yours.'], 404);
        }
        $basketItemsId = $basket->items->pluck('product_id')->toArray();
        if ($basketItemsId && in_array($data['product_id'],$basketItemsId)){
            BasketItem::where([
                'basket_id' => $basket->id,
                'product_id' => $data['product_id']
            ])->first()->delete();
            return response()->json(['message' => 'item delete from basket successfully'], 200);

        } else {
            return response()->json(['error' => 'product already not exist in basket.'], 201);
        }
    }

    public function items(Request $request){


        $data = $request->validate([
            'basket_token' => 'required|string'
        ]);
        $basket = Basket::where('token',$data['basket_token'])->with('items')->first();
        if(!$basket){
            return response()->json(['error' => 'Basket not found.'], 404);
        }
        if ($basket->user_id != \auth()->user()->id){
            return response()->json(['error' => 'Basket is not yours.'], 404);
        }
        return response()->json(['message' => 'Basket find successfully', 'basket' => $basket], 200);


    }

}
