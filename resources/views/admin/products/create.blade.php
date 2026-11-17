@extends('layouts.admin')

@section('title', 'Tambah Menu')
@section('eyebrow', 'Manajemen Menu')
@section('heading', 'Tambah Menu Baru')

@section('content')
    @include('admin.products._form', [
        'product' => null,
        'action' => route('admin.products.store'),
        'method' => 'POST',
        'submitLabel' => 'Simpan Menu',
    ])
@endsection
