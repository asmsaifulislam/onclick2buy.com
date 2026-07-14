@extends('layouts.app')
@section('title', $product->meta_title ?: $product->name)
@section('meta')
    <meta name="description" content="{{ $product->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 160) }}">
    @if($product->meta_keywords)<meta name="keywords" content="{{ $product->meta_keywords }}">@endif
@endsection
@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 animate-fade-in-up">
        <div>
            <div id="mainImageWrap" class="relative rounded-2xl shadow-lg overflow-hidden bg-white">
                @if($product->images && count($product->images) > 0)
                    <img id="mainImage" src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="image-zoom w-full h-64 sm:h-96 md:h-[500px] object-cover cursor-zoom-in" onclick="openZoom()">
                @else
                    <div class="h-64 sm:h-96 md:h-[500px] bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400">
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                @endif
                @if($product->images && count($product->images) > 1)
                    <button type="button" id="btn360" onclick="toggle360()" class="absolute top-3 right-3 z-10 flex items-center gap-1 bg-gray-900/80 text-white text-xs font-bold px-3 py-1.5 rounded-full hover:bg-indigo-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v8l4 2M12 4a8 8 0 11-8 8m0 0a8 8 0 018-8"/></svg>
                        360&deg; View
                    </button>
                @endif
                <div id="viewer360" class="hidden absolute inset-0 bg-white flex items-center justify-center cursor-ew-resize select-none touch-none">
                    <img id="spinImage" src="{{ $product->images[0] ?? '' }}" class="max-h-full max-w-full object-contain pointer-events-none">
                    <span class="absolute bottom-3 text-xs text-gray-500 bg-white/80 px-3 py-1 rounded-full shadow">Drag left / right to rotate</span>
                    <button type="button" onclick="toggle360()" class="absolute top-3 right-3 bg-gray-900 text-white text-xs px-3 py-1.5 rounded-full">Close</button>
                </div>
            </div>
            @if($product->images && count($product->images) > 1)
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 mt-3">
                    @foreach($product->images as $idx => $image)
                        <div class="image-zoom rounded-lg overflow-hidden cursor-pointer border-2 {{ $idx === 0 ? 'border-indigo-500' : 'border-transparent' }} hover:border-indigo-400 transition-all" onclick="setMainImage('{{ $image }}', this)">
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
            @if($product->variants)
                <div class="space-y-4">
                    @foreach(['size' => 'Size', 'color' => 'Color', 'material' => 'Material'] as $vKey => $vLabel)
                        @if(!empty($product->variants[$vKey]))
                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-2">{{ $vLabel }}</p>
                                <div class="flex flex-wrap gap-2" data-variant-group="{{ $vKey }}">
                                    @foreach($product->variants[$vKey] as $vVal)
                                        <button type="button" class="variant-chip px-4 py-2 rounded-lg border-2 border-gray-200 text-sm font-medium text-gray-700 hover:border-indigo-400 transition-all" data-value="{{ $vVal }}">{{ $vVal }}</button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
            @if($product->stock > 0)
                <form id="buyForm" action="{{ route('cart.add', $product) }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 p-4 bg-gray-50 rounded-xl">
                    @csrf
                    <input type="hidden" name="buy_now" id="buyNowFlag" value="0">
                    <input type="hidden" name="variant_size" id="variant_size" value="">
                    <input type="hidden" name="variant_color" id="variant_color" value="">
                    <input type="hidden" name="variant_material" id="variant_material" value="">
                    <div class="flex items-center gap-3">
                        <label class="font-medium text-gray-700 whitespace-nowrap">Qty:</label>
                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-20 input-field text-center py-2">
                    </div>
                    <button type="submit" class="btn-primary flex-1 flex items-center justify-center gap-2 py-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        Add to Cart
                    </button>
                    <button type="submit" onclick="document.getElementById('buyNowFlag').value='1'" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2zm0 0a2 2 0 002 2h2a2 2 0 002-2m-6 0h.01M9 16h.01"/></svg>
                        Buy Now
                    </button>
                </form>
            @else
                <button disabled class="w-full bg-gray-200 text-gray-500 py-3.5 rounded-xl font-bold cursor-not-allowed text-lg">Out of Stock</button>
            @endif
            <div class="mt-3">
                @auth
                    @if($product->isWishlisted())
                        <form action="{{ route('wishlist.destroy', $product) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border-2 border-red-200 text-red-600 font-medium hover:bg-red-50 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                                Saved to Wishlist
                            </button>
                        </form>
                    @else
                        <form action="{{ route('wishlist.store', $product) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border-2 border-gray-200 text-gray-600 font-medium hover:border-red-300 hover:text-red-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                Add to Wishlist
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border-2 border-gray-200 text-gray-600 font-medium hover:border-red-300 hover:text-red-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        Add to Wishlist
                    </a>
                @endauth
            </div>
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

    <div id="zoomModal" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4 cursor-zoom-out" onclick="closeZoom()">
        <img id="zoomImage" src="" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
        <button type="button" class="absolute top-4 right-6 text-white text-4xl leading-none" onclick="closeZoom()">&times;</button>
    </div>

    <script>
        const spinImages = @json($product->images ?? []);
        let spinIndex = 0;

        function setMainImage(src, el) {
            const main = document.getElementById('mainImage');
            if (main) main.src = src;
            document.querySelectorAll('[onclick^="setMainImage"]').forEach(d => d.classList.remove('border-indigo-500'));
            if (el) el.classList.add('border-indigo-500');
        }
        function openZoom() {
            const main = document.getElementById('mainImage');
            const zm = document.getElementById('zoomImage');
            if (main && zm) { zm.src = main.src; document.getElementById('zoomModal').classList.remove('hidden'); }
        }
        function closeZoom() { document.getElementById('zoomModal').classList.add('hidden'); }
        function toggle360() {
            const v = document.getElementById('viewer360');
            if (!v) return;
            if (v.classList.contains('hidden')) {
                v.classList.remove('hidden');
                spinIndex = 0;
                document.getElementById('spinImage').src = spinImages[0];
            } else {
                v.classList.add('hidden');
            }
        }
        (function () {
            const v = document.getElementById('viewer360');
            if (!v) return;
            let dragging = false, startX = 0, startIdx = 0;
            v.addEventListener('pointerdown', e => { dragging = true; startX = e.clientX; startIdx = spinIndex; v.setPointerCapture(e.pointerId); });
            v.addEventListener('pointermove', e => {
                if (!dragging || spinImages.length < 2) return;
                const delta = Math.round((e.clientX - startX) / 40);
                let idx = (startIdx - delta) % spinImages.length;
                if (idx < 0) idx += spinImages.length;
                if (idx !== spinIndex) { spinIndex = idx; document.getElementById('spinImage').src = spinImages[idx]; }
            });
            v.addEventListener('pointerup', () => dragging = false);
            v.addEventListener('pointercancel', () => dragging = false);
        })();

        document.querySelectorAll('.variant-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const group = chip.closest('[data-variant-group]');
                group.querySelectorAll('.variant-chip').forEach(c => c.classList.remove('selected'));
                chip.classList.add('selected');
                const key = group.dataset.variantGroup;
                const input = document.getElementById('variant_' + key);
                if (input) input.value = chip.dataset.value;
            });
        });

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
