@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Product</h1>
    </div>
    <div id="app">
        <edit-product :variants="{{ $variants }}" :data="{{ $product }}" :product_variant_data="{{ $product->product_variant }}" :product_variant_price_data="{{ $product->product_variant_price }}" :product_image_data="{{ $product->product_image }}">Loading</edit-product>
    </div>
@endsection
