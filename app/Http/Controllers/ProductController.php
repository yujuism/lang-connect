<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = collect();
        for ($i = 1; $i <= 20; $i++) {
            $products->push((object) [
                'id' => $i,
                'name' => 'Product ' . $i,
                'description' => 'This is the description for product ' . $i,
                'price' => rand(10000, 500000)
            ]);
        }

        return view('products.list', compact('products'));
    }

    public function create()
    {
        return view('products.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0'
        ]);

        return redirect()->route('products')
            ->with('success', 'Product created successfully!');
    }

    public function show($id)
    {
        $product = (object) [
            'id' => $id,
            'name' => 'Product ' . $id,
            'description' => 'This is the detailed description for product ' . $id,
            'price' => rand(10000, 500000)
        ];

        return view('products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = (object) [
            'id' => $id,
            'name' => 'Product ' . $id,
            'description' => 'This is the description for product ' . $id,
            'price' => rand(10000, 500000)
        ];

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0'
        ]);

        return redirect()->route('products')
            ->with('success', 'Product updated successfully!');
    }
}
