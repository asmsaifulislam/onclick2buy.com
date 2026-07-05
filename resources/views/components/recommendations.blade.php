@props(['productId' => null, 'limit' => 4])

<div id="recommendations-widget" class="mt-8">
    <h2 class="text-xl font-bold text-gray-900 mb-4">You Might Also Like</h2>
    <div id="recommendations-loading" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @for($i = 0; $i < $limit; $i++)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 animate-pulse">
                <div class="w-full h-40 bg-gray-200 rounded-lg mb-3"></div>
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            </div>
        @endfor
    </div>
    <div id="recommendations-content" class="grid grid-cols-2 md:grid-cols-4 gap-4 hidden"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadRecommendations();
});

async function loadRecommendations() {
    const productId = '{{ $productId }}';
    const limit = {{ $limit }};

    try {
        let url = '/api/recommendations?limit=' + limit;
        if (productId) {
            url = '/api/recommendations/product/' + productId + '?limit=' + limit;
        }

        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.recommendations && data.recommendations.length > 0) {
            renderRecommendations(data.recommendations);
        } else {
            document.getElementById('recommendations-widget').classList.add('hidden');
        }
    } catch (error) {
        console.error('Failed to load recommendations');
        document.getElementById('recommendations-widget').classList.add('hidden');
    }
}

function renderRecommendations(products) {
    const container = document.getElementById('recommendations-content');
    const loading = document.getElementById('recommendations-loading');

    container.innerHTML = products.map(product => `
        <a href="/products/${product.slug || product.product_id}" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
            <div class="relative">
                ${product.image
                    ? `<img src="/storage/${product.image}" alt="${product.product_name}" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300">`
                    : `<div class="w-full h-40 bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                        <svg class="w-12 h-12 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>`
                }
                ${product.predicted_rating ? `
                    <div class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-full text-xs font-medium text-indigo-600">
                        ★ ${product.predicted_rating.toFixed(1)}
                    </div>
                ` : ''}
            </div>
            <div class="p-3">
                <h3 class="font-medium text-gray-900 text-sm line-clamp-2 mb-2">${product.product_name}</h3>
                <p class="text-indigo-600 font-bold">$${product.price ? product.price.toFixed(2) : '0.00'}</p>
            </div>
        </a>
    `).join('');

    loading.classList.add('hidden');
    container.classList.remove('hidden');
}
</script>
