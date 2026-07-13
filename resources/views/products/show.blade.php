@extends('layouts.app')
@section('title', $product->name)
@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 animate-fade-in-up">
        <div>
            <div class="image-zoom rounded-2xl shadow-lg overflow-hidden bg-white">
                @if($product->images && count($product->images) > 0)
                    <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="w-full h-64 sm:h-96 md:h-[500px] object-cover">
                @else
                    <div class="h-64 sm:h-96 md:h-[500px] bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400">
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                @endif
            </div>
            @if($product->images && count($product->images) > 1)
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 mt-3">
                    @foreach($product->images as $image)
                        <div class="image-zoom rounded-lg overflow-hidden cursor-pointer border-2 border-transparent hover:border-indigo-400 transition-all">
                            <img src="{{ $image }}" class="h-16 w-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="space-y-5">
            <div>
                <span class="text-sm font-medium text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">{{ $product->category->name ?? 'Uncategorized' }}</span>
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-3">{{ $product->name }}</h1>
                <div class="flex items-center gap-2 mt-2">
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= $avgRating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <span class="text-sm font-medium text-gray-600">{{ $avgRating > 0 ? $avgRating : 'No' }} ({{ $product->reviews->count() }} {{ $product->reviews->count() === 1 ? 'review' : 'reviews' }})</span>
                </div>
            </div>
            <div class="flex items-baseline gap-3">
                @if($product->sale_price)
                    <span class="text-4xl font-extrabold text-red-600">৳{{ number_format($product->sale_price, 2) }}</span>
                    <span class="text-xl text-gray-400 line-through">৳{{ number_format($product->price, 2) }}</span>
                    <span class="bg-red-100 text-red-700 text-sm font-bold px-3 py-1 rounded-full">Save ৳{{ number_format($product->price - $product->sale_price, 2) }}</span>
                @else
                    <span class="text-4xl font-extrabold text-gray-900">৳{{ number_format($product->price, 2) }}</span>
                @endif
            </div>
            <div class="prose prose-gray max-w-none">
                <p class="text-gray-600 leading-relaxed">{{ $product->description }}</p>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-lg">
                    <svg class="w-5 h-5 {{ $product->stock > 0 ? 'text-green-500' : 'text-red-500' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="font-medium {{ $product->stock > 0 ? 'text-green-700' : 'text-red-700' }}">{{ $product->stock > 0 ? 'In Stock (' . $product->stock . ' available)' : 'Out of Stock' }}</span>
                </div>
                @if($product->sku)
                    <div class="text-gray-400">SKU: <span class="font-mono">{{ $product->sku }}</span></div>
                @endif
            </div>
            @if($product->stock > 0)
                <form action="{{ route('cart.add', $product) }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 p-4 bg-gray-50 rounded-xl">
                    @csrf
                    <div class="flex items-center gap-3">
                        <label class="font-medium text-gray-700 whitespace-nowrap">Qty:</label>
                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-20 input-field text-center py-2">
                    </div>
                    <button type="submit" class="btn-primary flex-1 flex items-center justify-center gap-2 py-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        Add to Cart
                    </button>
                </form>
            @else
                <button disabled class="w-full bg-gray-200 text-gray-500 py-3.5 rounded-xl font-bold cursor-not-allowed text-lg">Out of Stock</button>
            @endif
            <div class="border-t pt-5 flex items-center gap-4 text-sm text-gray-500">
                <div class="flex items-center gap-1"><svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Secure checkout</div>
                <div class="flex items-center gap-1"><svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/><path d="M3 4h1.5l2.5 7h8.5l2-6H7.5L6 3H3v1z"/></svg> Free shipping over ৳500</div>
                <div class="flex items-center gap-1"><svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> 30-day returns</div>
            </div>
        </div>
    </div>

    <section class="mt-16">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Customer Reviews</h2>
            @auth
                <button onclick="document.getElementById('reviewForm').classList.toggle('hidden')" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                    {{ $userReview ? 'Edit Your Review' : 'Write a Review' }}
                </button>
            @else
                <a href="{{ route('login') }}" class="px-5 py-2.5 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">Login to Review</a>
            @endauth
        </div>

        @auth
            <form id="reviewForm" method="POST" action="{{ route('products.reviews.store', $product) }}" class="bg-white rounded-xl shadow-md p-6 mb-8 {{ $userReview && !$errors->any() ? 'hidden' : '' }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Your Rating</label>
                    <div class="flex items-center gap-1" id="starPicker">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" data-star="{{ $i }}" class="star-btn text-3xl {{ $userReview && $userReview->rating >= $i ? 'text-amber-400' : 'text-gray-200' }} hover:text-amber-400 transition-colors">&#9733;</button>
                        @endfor
                        <input type="hidden" name="rating" id="ratingInput" value="{{ $userReview->rating ?? 0 }}">
                    </div>
                    @error('rating') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Comment (optional)</label>
                    <textarea name="comment" rows="3" class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Share your thoughts about this product...">{{ old('comment', $userReview->comment ?? '') }}</textarea>
                    @error('comment') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">{{ $userReview ? 'Update Review' : 'Submit Review' }}</button>
            </form>
        @endauth

        @if($product->reviews->isEmpty())
            <div class="text-center py-12 bg-white rounded-xl shadow-sm">
                <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                <p class="text-gray-400 text-lg">No reviews yet. Be the first to review this product!</p>
            </div>
        @else
            <div class="space-y-4 mb-8">
                @foreach($product->reviews->sortByDesc('created_at') as $review)
                    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold">{{ substr($review->user->name ?? 'A', 0, 1) }}</div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $review->user->name ?? 'Deleted User' }}</p>
                                    <p class="text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                        </div>
                        @if($review->comment)
                            <p class="text-gray-600 text-sm leading-relaxed mt-2">{{ $review->comment }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <script>
        document.getElementById('starPicker')?.addEventListener('click', function(e) {
            const btn = e.target.closest('.star-btn');
            if (!btn) return;
            const val = parseInt(btn.dataset.star);
            document.getElementById('ratingInput').value = val;
            this.querySelectorAll('.star-btn').forEach((el, i) => {
                el.classList.toggle('text-amber-400', i < val);
                el.classList.toggle('text-gray-200', i >= val);
            });
        });
    </script>

    @if($related->isNotEmpty())
        <section class="mt-16">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8">Related Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($related as $i => $rel)
                    <div class="product-card bg-white rounded-xl shadow-md overflow-hidden group animate-fade-in-up" style="animation-delay: {{ $i * 0.1 }}s">
                        <a href="{{ route('products.show', $rel) }}">
                            @if($rel->images && count($rel->images) > 0)
                                <img src="{{ $rel->images[0] }}" alt="{{ $rel->name }}" class="product-img h-40 w-full object-cover">
                            @else
                                <div class="h-40 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </a>
                        <div class="p-3">
                            <a href="{{ route('products.show', $rel) }}" class="font-semibold text-gray-800 group-hover:text-indigo-600 transition-colors">{{ $rel->name }}</a>
                            <p class="text-lg font-bold mt-1">৳{{ number_format($rel->sale_price ?: $rel->price, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
