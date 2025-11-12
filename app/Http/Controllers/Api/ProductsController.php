<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;

use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil data produk beserta relasi kategori
        $products = Product::with('category')->orderBy('id', 'asc')->get();
    
        // Ubah struktur output
        $data = $products->map(function ($item) {
            return [
                'id' => $item->id,
                'category_id' => $item->category_id,
                'name' => $item->name,
                'description' => $item->description,
                'image' => $item->image,
                'price' => $item->price,
                'status' => $item->status,
                'category' => $item->category ? $item->category->name : null,
            ];
        });
    
        return response()->json([
            'status' => true,
            'message' => "Data ditemukan",
            'data' => $data,
        ], 200);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
