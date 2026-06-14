@extends('layouts.admin')

@section('title', 'Thêm sản phẩm')
@section('page-title', 'Thêm sản phẩm mới')
@section('breadcrumb', 'Admin › Sản phẩm › Thêm mới')

@section('content')
<form method="POST" action="{{ route('admin.products.store') }}">
    @csrf
    @include('admin.products._form')
</form>
@endsection