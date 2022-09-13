<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\ProductImage;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $products = Product::all();
        // foreach($products as $product){
        //     dd($product->product_variant);
        // }
        // dd($products);
        return view('products.index', compact('products'));
    }
    
    public function dataAjax(Request $request)
    {
        $data = [];

        $searchquery = $request->q;
        $data = ProductVariant::select('variant', DB::raw('MAX(id) as max_id'))->where('variant','like','%'.$searchquery.'%')->groupBy(['variant'])->get();
        return response()->json($data);
    }

    public function search(Request $request)
    {
        $title = $request->title;
        $variant = $request->variant;
        $price_from = $request->price_from;
        $price_to = $request->price_to;
        $date = $request->date;
        $variant = ProductVariant::select('variant')->where('id', $variant)->first()->variant ?? "";
        // dd($title,$variant,$price_from,$price_to,$date);
        $products = Product::with([
                    'product_variant' => function ($query) use ($variant) {
                        $query->where('variant', 'like', "%{$variant}%");
                    }
                    ])
                    ->with([
                    'product_variant_price' => function ($query) use ($price_from, $price_to) {
                        $query->whereBetween('price', [$price_from, $price_to]);
                    }
                    ])
                    ->where('title', 'like', "%{$title}%")
                    ->where('created_at', 'like', "%{$date}%")
                    ->get();
        // foreach($products as $product){
        //     dd($product->product_variant);
        // }
        // foreach($products as $product){
        //     dd($product->product_variant_price);
        // }
        // dd($products);
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // dd($request->product_image);
        $product = new Product;
        $product->title = $request->title;
        $product->sku = $request->sku;
        $product->description = $request->description;
        $product->save();
        $combinations = array();
        foreach($request->product_variant as $product_variant_data){
            $product_variant_data = (object) $product_variant_data;
            foreach($product_variant_data->tags as $tag){
                $product_variant = new ProductVariant;
                $product_variant->variant = $tag;
                $product_variant->variant_id = $product_variant_data->option;
                $product_variant->product_id = $product->id;
                $product_variant->save();
                $combinations[$tag] = $product_variant->id;
            }
        }
        foreach($request->product_variant_prices as $product_variant_prices_data){
            $product_variant_prices_data = (object) $product_variant_prices_data;
            $product_variant_price = new ProductVariantPrice;
            $title = explode("/",$product_variant_prices_data->title);
            $product_variant_one = null;
            $product_variant_two = null;
            $product_variant_three = null;
            foreach($combinations as $key=>$value){
                if(isset($title[0]) && $key == $title[0]){
                    $product_variant_one = $value;
                }
                if(isset($title[1]) && $key == $title[1]){
                    $product_variant_two = $value;
                }
                if(isset($title[2]) && $key == $title[2]){
                    $product_variant_three = $value;
                }
                // dd($combinations, $key, $value);
            }
            $product_variant_price->product_variant_one = $product_variant_one;
            $product_variant_price->product_variant_two = $product_variant_two;
            $product_variant_price->product_variant_three = $product_variant_three;
            $product_variant_price->price = (int) $product_variant_prices_data->price;
            $product_variant_price->stock = (int) $product_variant_prices_data->stock;
            $product_variant_price->product_id = $product->id;
            // dd($product_variant_price);

            $product_variant_price->save();
        }
        foreach($request->product_image as $product_image_data){
            // $product_image_data = (object) $product_image_data;
            $product_image = new ProductImage;
            $product_image->product_id = $product->id;
            $product_image->file_path = $product_image_data;
            // $product_image->thumbnail = $product_image_data;
            $product_image->save();
        }

        return response()->json([
            'message' => 'New product created',
            'success' => 'ok'
        ]);

    }
    
    public function fileUpload(Request $request)  
    {  
        $imageName = time().'.'.$request->file->getClientOriginalExtension();  
        $request->file->move(public_path('images'), $imageName);  
           
        return response()->json(['file' => $imageName]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        // dd($product->product_variant);
        // dd($product->product_image);
        return view('products.edit', compact('variants','product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        // $product = new Product;
        $product->product_variant()->delete();
        $product->product_variant_price()->delete();
        // dd($product->product_variant());
        $product->title = $request->title;
        $product->sku = $request->sku;
        $product->description = $request->description;
        $product->save();
        $combinations = array();
        foreach($request->product_variant as $product_variant_data){
            $product_variant_data = (object) $product_variant_data;
            foreach($product_variant_data->tags as $tag){
                $product_variant = new ProductVariant;
                $product_variant->variant = $tag;
                $product_variant->variant_id = $product_variant_data->option;
                $product_variant->product_id = $product->id;
                $product_variant->save();
                $combinations[$tag] = $product_variant->id;
            }
        }
        foreach($request->product_variant_prices as $product_variant_prices_data){
            $product_variant_prices_data = (object) $product_variant_prices_data;
            $product_variant_price = new ProductVariantPrice;
            $title = explode("/",$product_variant_prices_data->title);
            $product_variant_one = null;
            $product_variant_two = null;
            $product_variant_three = null;
            foreach($combinations as $key=>$value){
                if(isset($title[0]) && $key == $title[0]){
                    $product_variant_one = $value;
                }
                if(isset($title[1]) && $key == $title[1]){
                    $product_variant_two = $value;
                }
                if(isset($title[2]) && $key == $title[2]){
                    $product_variant_three = $value;
                }
                // dd($combinations, $key, $value);
            }
            $product_variant_price->product_variant_one = $product_variant_one;
            $product_variant_price->product_variant_two = $product_variant_two;
            $product_variant_price->product_variant_three = $product_variant_three;
            $product_variant_price->price = (int) $product_variant_prices_data->price;
            $product_variant_price->stock = (int) $product_variant_prices_data->stock;
            $product_variant_price->product_id = $product->id;
            // dd($product_variant_price);

            $product_variant_price->save();
        }
        foreach($request->product_image as $product_image_data){
            // $product_image_data = (object) $product_image_data;
            $product_image = new ProductImage;
            $product_image->product_id = $product->id;
            $product_image->file_path = $product_image_data;
            // $product_image->thumbnail = $product_image_data;
            $product_image->save();
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'success' => 'ok'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
