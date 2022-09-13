@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="{{ route('search_product') }}" method="post" class="card-header">
            @csrf
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <!-- <select name="variant" id="" class="form-control js-example-basic-single">

                    </select> -->
                    <select class="form-control js-example-basic-single" name="variant"></select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From" class="form-control">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @php $i=1; @endphp
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $i }}</td>
                        <td>{{ $product->title }} <br> Created at : {{ date_format($product->created_at,"d-M-Y") }}</td>
                        <td>{{ Str::limit($product->description, 20) }}</td>
                        <td>
                            @php $j=1; @endphp
                            @foreach($product->product_variant_price as $product_variant_price_data)
                            @php if($j==4){break;} @endphp
                            <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant">

                                <dt class="col-sm-3 pb-0">
                                    @php
                                    $product_variant_one_value = "";
                                    $product_variant_two_value = "";
                                    $product_variant_three_value = "";
                                    @endphp
                                    @foreach($product->product_variant as $product_variant_data)
                                        @if($product_variant_price_data->product_variant_one == $product_variant_data->id)
                                        @php
                                            $product_variant_one_value = $product_variant_data->variant;
                                        @endphp
                                        @endif
                                        
                                        @if($product_variant_price_data->product_variant_two == $product_variant_data->id)
                                        @php
                                            $product_variant_two_value = $product_variant_data->variant;
                                        @endphp
                                        @endif

                                        @if($product_variant_price_data->product_variant_three == $product_variant_data->id)
                                        @php
                                            $product_variant_three_value = $product_variant_data->variant;
                                        @endphp
                                        @endif
                                    @endforeach

                                    {{ $product_variant_one_value }}/ {{ $product_variant_two_value }}/ {{ $product_variant_three_value }}
                                </dt>
                                <dd class="col-sm-9">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4 pb-0">Price : {{ number_format($product_variant_price_data->price,2) }}</dt>
                                        <dd class="col-sm-8 pb-0">InStock : {{ number_format($product_variant_price_data->stock,2) }}</dd>
                                    </dl>
                                </dd>
                            </dl>
                            @php $j++; @endphp
                            @endforeach
                            <button onclick="$('#variant').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('product.edit', $product->id) }}" class="btn btn-success">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @php $i++; @endphp
                    @endforeach

                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing 1 to 10 out of 100</p>
                </div>
                <div class="col-md-2">

                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">

    // // var APP_URL = $('meta[name="_base_url"]').attr('content');
    // var APP_URL = {!! json_encode(url('/')) !!}

    // $('.js-example-basic-single').select2({
    //     placeholder: 'Select an item',
    //     ajax: {
    //     url: APP_URL+'/select2-autocomplete-ajax',
    //     dataType: 'json',
    //     delay: 250,
    //     processResults: function (data) {
    //         return {
    //         results:  $.map(data, function (item) {
    //                 return {
    //                     text: item.variant,
    //                     id: item.id
    //                 }
    //             })
    //         };
    //     },
    //     cache: true
    //     }
    // });


    </script>


@endsection
