@extends('layouts.frontend.app')

@section('title')
	{{$title}}
@endsection

@section('meta')
@php
	$website = App\Website::get()->last();
@endphp
   <meta name='subject' content='ecommerce's subject'>
   <meta name='title' content='{{$title}} || {{ config('app.name') }}'>
   <meta name='description' content='@if (isset($website->meta_tag)) {{$website->meta_tag}} @endif'>
   <meta name='keywords' content='@if (isset($website->meta_keyword)) {{$website->meta_keyword}} @endif'>
   <meta name='author' content='yousuf1648|projanmoit.com'>
   <meta name='copyright' content='yousuf1648|projanmoit.com'>
@endsection

@push('css')
    <style>
        .panel_bg {
            background-color: #0D7E40;
            color: white;
        }
    </style>
@endpush

@section('content')
    <div class="breadcrumbs_area commun_bread py-3 grey-section">
        <div class="container">   
            <div class="row">
                <div class="col-12">
                    <div class="breadcrumb_content">
                        <ul class="text-capitalize">
                            <li><a href="{{ route('home') }}">home</a></li>
                            <li><i class="fa fa-angle-right"></i></li>
                            <li>Cart</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>         
    </div>
    <section class="prd bg-white pt-10 cart_style">
        <div class="container">
            <div class="row">
                <div class="col-md-8 cart-items table-responsive">
                    <div class="alert panel_bg">
                        <i class="fa fa-shopping-bag" aria-hidden="true"></i> My Cart
                        <strong>
                            (@if (session('cart'))
                            {{count(session('cart'))}}
                            @else
                                0
                            @endif Items)
                        </strong>
                    </div>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th width="2%">#</th>
                                <th width="10%">Image</th>
                                <th width="20%">Product Name</th>
                                <th width="10%">Price</th>
                                <th width="12.5%">Qty</th>
                                <th width="12.5%">Subtotal</th>
                                <th width="15%" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total = 0;
                                $i = 1;
                            @endphp
                            @if(session('cart'))
                                @foreach(session('cart') as $id => $details)

                                    <?php $total += $details['sell_price'] * $details['quantity'] ?>

                                    <tr>
                                        <td>{{$i++}}</td>
                                        <td>
                                            <img src="{{ URL::to($details['image']) }}" style="height: 50px;">
                                        </td>
                                        <td>
                                            <strong>{{ $details['title'] }}</strong><br />
                                                @if ($details['size_id'])
                                            @php
                                                $size = App\Size::find($details['size_id']);
                                            @endphp
                                                    Size : {{$size->name}}<br>
                                                @endif

                                                @if ($details['colour_id'])
                                            @php
                                                $colour = App\Colour::find($details['colour_id']);
                                            @endphp
                                                    Colour : {{$colour->name}}
                                                @endif
                                        </td>
                                        <td>&#x9f3; {{ $details['sell_price'] }}</td>
                                        <td>
                                            <input type="number" value="{{ $details['quantity'] }}" class="form-control quantity" style="min-width: 59px;" />
                                        </td>
                                        <td><strong>&#x9f3; {{ $details['sell_price'] * $details['quantity'] }}</strong></td>
                                        <td class="actions text-center">
                                            <a class="btn btn-warning btn-sm update-cart text-white" data-id="{{ $id }}" title="Update"><i class="fa fa-pencil-square-o"></i></a>
                                            <a title="Delete" class="btn btn-danger btn-sm remove-from-cart" data-id="{{ $id }}"><i class="fa fa-trash"></i></a>
                                        </td>

                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    <a href="{{ route('home') }}" class="btn btn-success">Continue Shopping <i class="fa fa-arrow-right" aria-hidden="true"></i></a>

                </div>

                <div class="col-md-4">
                    <div class="alert panel_bg">
                        <div class="panel-heading">Checkout Summary</div>
                    </div>
                    <div class="panel check-sum">
                        <div class="panel-body">

                            <h5>Sub Total <span class="pull-right"> ৳ {{ $total }}</h5>
                            <hr style="margin: 20px; padding: 0px;" />
                            <h5>VAT <span class="pull-right"> ৳ 0</h5>
                                <hr style="margin: 20px; padding: 0px;" />
                            <h5 class="text-success">Payable Total <span class="pull-right"> ৳ {{ $total }}</h5>
                                <hr style="margin: 20px; padding: 0px;" />
                            @auth
                                <a href="{{ route('customer.checkout.index') }}" class="btn btn-success pull-right">Checkout</a>
                            @else
                                <a href="{{ url('/cart-login') }}" class="btn btn-info pull-right">Checkout</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
	<script>
  		$(".update-cart").click(function (e) {
           e.preventDefault();

           var ele = $(this);

            $.ajax({
               url: '{{ url('update-cart') }}',
               method: "patch",
               data: {_token: '{{ csrf_token() }}', id: ele.attr("data-id"), quantity: $(".quantity").val()},
               success: function (data) {
                   window.location.reload();
               }
            });
        });

	</script>

@endpush
