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

@endpush

@section('content')
	<!-- breadcrumb start -->
	@include('layouts.frontend.partial.search_breadcrumb')
	<section class="cat-wise pad-tb-25 pt-15">
	    <div class="container">
	        <div class="row">
	            {{-- <h4 class="p-title">Search</h4> --}}
	            <div class="col-md-12">
	            	@include('layouts.frontend.partial.search_product')
	            	{{$products->onEachSide(2)->links()}}

	            </div>
	        </div>
	    </div>
	</section>
@endsection

@push('js')
	<script>
		function wishlist_form_submit(id) {
		    document.getElementById('wishlist_form_'+id).submit();
		}

	</script>
@endpush
