<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Notifications\ProductAdded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $product = Product::find($id);
        if($product){
            return response()->json(['message' => 'Product find successfully', 'product' => $product], 200);
        }else {
            return response()->json(['error' => 'Product not find.'], 404);

        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        $admin = Auth::user();

        if (! $admin->isAdmin()) {
            return response()->json(['error' => 'Only admins can add product!'], 403);
        }

        $product = Product::create($data);

        $admin->notify(new ProductAdded($product));

        return response()->json(['message' => 'Product added successfully', 'product' => $product], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id,Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        $admin = Auth::user();
        if (! $admin->isAdmin()) {
            return response()->json(['error' => 'Only admins can update product!'], 403);
        }

        if ($id && $data){
            $product = Product::find($id);
            if($product) {
                $product->update([
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'quantity' => $data['quantity']
                ]);
                $product->save();
                return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
            } else {
                return response()->json(['error' => 'Product not found!'], 404);
            }

        } else {
            return response()->json(['error' => 'Not valid data.'], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $admin = Auth::user();

        if (! $admin->isAdmin()) {
            return response()->json(['error' => 'Only admins can delete product!'], 403);
        }


        $product = Product::find($id);
        if($product) {
            $product->delete();
            return response()->json(['message' => 'Product deleted successfully'], 200);
        } else {
            return response()->json(['error' => 'Product not found'], 404);
        }

    }
}
