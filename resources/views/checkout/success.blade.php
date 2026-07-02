@extends('layouts.app')
@section('title', 'Order Successful')
@section('content')
    <div class="text-center py-16 animate-fade-in-up">
        <div class="w-24 h-24 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg animate-float">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">Order Placed!</h1>
        <p class="text-lg text-gray-500 mb-4 max-w-md mx-auto">Thank you for your purchase. You'll receive a confirmation email shortly with your order details.</p>
        <div class="flex justify-center gap-4 mt-8">
            <a href="{{ route('products.index') }}" class="btn-primary inline-flex items-center gap-2">Continue Shopping</a>
            <a href="{{ route('home') }}" class="btn-outline inline-flex items-center gap-2">Go Home</a>
        </div>
    </div>
@endsection
