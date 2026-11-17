@extends('layouts.admin')

@section('title', 'Edit Menu')
@section('eyebrow', 'Manajemen Menu')
@section('heading', 'Edit Menu')

@section('content')
    @include('admin.products._form', [
        'product' => $product,
        'action' => route('admin.products.update', $product),
        'method' => 'PUT',
        'submitLabel' => 'Simpan Perubahan',
    ])
@endsection
