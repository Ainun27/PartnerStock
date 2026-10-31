<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Tampilkan data produk Keripik Pisang Original Pak Aceng
     */
    public function index()
    {
        $products = Product::all();
        $singleProduct = Product::first();

        return view('admin.produk.index', compact('products', 'singleProduct'));
    }

    /**
     * Perbarui data produk Pak Aceng (harga titip, harga jual, catatan)
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'product_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'agreed_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
        ]);

        // Aturan bisnis: Nama produk Pak Aceng tetap Keripik Pisang Original
        if (!str_contains(strtolower($validated['name']), 'keripik pisang original')) {
            return back()->with('error', 'Sesuai aturan bisnis konsinyasi Pak Aceng, produk tunggal harus tetap "Keripik Pisang Original".');
        }

        $product->update($validated);

        return redirect()->route('admin.produk.index')->with('success', 'Data produk Keripik Pisang Original berhasil diperbarui.');
    }
}
