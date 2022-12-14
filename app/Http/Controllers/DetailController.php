<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Product;
use App\Cart;

class DetailController extends Controller
{
    public function index(Request $request, $id)
    {
        // dd($product);
        $product = Product::with(['galleries','user'])->where('slug', $id)->firstOrFail();
        return view('pages.detail', compact('product'));
    }

    public function add(Request $request, $id)
    {
        $data = [
            'products_id' => $id,
            'users_id' => Auth::user()->id
        ];

        Cart::create($data);

        return redirect()->route('cart');
    }
}
