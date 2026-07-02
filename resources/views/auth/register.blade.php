@extends('layouts.app')
@section('title', 'Register')
@section('content')
<div class="max-w-md mx-auto animate-fade-in-up mt-4 md:mt-10">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Create Account</h1>
            <p class="text-indigo-200 text-sm mt-1">Join us and start shopping</p>
        </div>
        <div class="p-6 md:p-8">
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 font-medium text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input-field" required autofocus placeholder="John Doe">
                    @error('name')<p class="text-red-500 text-sm mt-1 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $message }}</p>@enderror
                </div>
                <div class="mb-4">
                    <label class="block mb-2 font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input-field" required placeholder="you@example.com">
                    @error('email')<p class="text-red-500 text-sm mt-1 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $message }}</p>@enderror
                </div>
                <div class="mb-4">
                    <label class="block mb-2 font-medium text-gray-700">Password</label>
                    <input type="password" name="password" class="input-field" required placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;">
                    @error('password')<p class="text-red-500 text-sm mt-1 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $message }}</p>@enderror
                </div>
                <div class="mb-6">
                    <label class="block mb-2 font-medium text-gray-700">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="input-field" required placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;">
                </div>
                <button type="submit" class="btn-primary w-full py-3 text-base flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Create Account
                </button>
            </form>
            <p class="mt-6 text-center text-gray-500 text-sm">Already have an account? <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold hover:underline">Sign in</a></p>
        </div>
    </div>
</div>
@endsection
