@extends('layouts.admin')

@section('title', 'Sửa sản phẩm')
@section('page-title', 'Sửa: ' . $product->name)
@section('breadcrumb', 'Admin › Sản phẩm › Sửa')

@section('content')
<form method="POST" action="{{ route('admin.products.update', $product) }}">
    @csrf
    @method('PUT')
    @include('admin.products._form')
</form>
@endsection