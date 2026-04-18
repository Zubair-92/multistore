<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    protected function activeCoupons(int $limit = 3)
    {
        return Coupon::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhereDate('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', now()))
            ->orderByDesc('value')
            ->take($limit)
            ->get();
    }

    protected function featuredDeals(int $limit = 4)
    {
        return $this->catalogQuery()
            ->whereNotNull('offer_price')
            ->whereColumn('offer_price', '<', 'price')
            ->orderByRaw('(price - offer_price) desc')
            ->take($limit)
            ->get();
    }

    protected function recentlyViewedProducts(Request $request, ?int $exceptProductId = null, int $limit = 4)
    {
        $recentlyViewedIds = collect($request->session()->get('recently_viewed_products', []))
            ->when($exceptProductId, fn ($collection) => $collection->reject(fn ($id) => (int) $id === $exceptProductId))
            ->take($limit)
            ->values();

        if ($recentlyViewedIds->isEmpty()) {
            return collect();
        }

        $products = $this->catalogQuery()
            ->whereIn('id', $recentlyViewedIds)
            ->get()
            ->keyBy('id');

        return $recentlyViewedIds
            ->map(fn ($id) => $products->get((int) $id))
            ->filter()
            ->values();
    }

    protected function rememberViewedProduct(Request $request, Product $product): void
    {
        $recentlyViewed = collect($request->session()->get('recently_viewed_products', []))
            ->reject(fn ($id) => (int) $id === (int) $product->id)
            ->prepend($product->id)
            ->take(8)
            ->values()
            ->all();

        $request->session()->put('recently_viewed_products', $recentlyViewed);
    }

    protected function catalogQuery()
    {
        return Product::query()
            ->with([
                'translations',
                'translation',
                'store.translations',
                'store.translation',
                'store.storeCategory.translation',
                'category.translations',
                'category.translation',
                'reviews',
            ])
            ->active()
            ->whereHas('store', fn ($query) => $query->where('approved', true));
    }

    public function index(Request $request)
    {
        return $this->catalogPage($request, 'frontend.home.index', [
            'pageType' => 'home',
            'heroBadge' => 'Multi Store Marketplace',
            'heroTitle' => 'Discover products from trusted local stores.',
            'heroDescription' => 'Browse active inventory, compare offers, and shop approved vendors from one place.',
            'catalogLabel' => 'Live Catalog',
            'catalogHelp' => 'products matching your current filters',
            'catalogTitle' => 'Catalog',
            'catalogDescription' => 'Only active products from approved stores are shown here.',
            'filterAction' => route('frontend.home'),
        ]);
    }

    public function products(Request $request)
    {
        return $this->catalogPage($request, 'frontend.home.index', [
            'pageType' => 'products',
            'heroBadge' => 'Marketplace Products',
            'heroTitle' => 'Browse all live products in one dedicated catalog.',
            'heroDescription' => 'Find approved store products faster with direct search, sorting, stock filters, and featured deals.',
            'catalogLabel' => 'Available Products',
            'catalogHelp' => 'approved products currently live in the marketplace',
            'catalogTitle' => 'Product Catalog',
            'catalogDescription' => 'This page is the direct marketplace product listing.',
            'filterAction' => route('frontend.products.index'),
        ]);
    }

    protected function catalogPage(Request $request, string $view, array $pageMeta = [])
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'store' => ['nullable', 'integer', 'exists:stores,id'],
            'stock' => ['nullable', 'in:in_stock'],
            'sort' => ['nullable', 'in:latest,price_low,price_high,name'],
        ]);

        $products = $this->catalogQuery()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('translations', function ($translationQuery) use ($search) {
                    $translationQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->when($filters['category'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['store'] ?? null, fn ($query, $storeId) => $query->where('store_id', $storeId))
            ->when(($filters['stock'] ?? null) === 'in_stock', fn ($query) => $query->where('stock', '>', 0));

        match ($filters['sort'] ?? 'latest') {
            'price_low' => $products->orderByRaw('COALESCE(offer_price, price) asc'),
            'price_high' => $products->orderByRaw('COALESCE(offer_price, price) desc'),
            'name' => $products->orderBy('id'),
            default => $products->latest('id'),
        };

        $products = $products->paginate(12)->withQueryString();

        if (($filters['sort'] ?? 'latest') === 'name') {
            $products->setCollection(
                $products->getCollection()->sortBy(fn (Product $product) => mb_strtolower($product->name ?? ''))->values()
            );
        }

        $categories = Category::with(['translations', 'translation'])->get();
        $stores = Store::query()
            ->with(['translations', 'translation'])
            ->where('approved', true)
            ->orderBy('id')
            ->get();

        return view($view, [
            'products' => $products,
            'categories' => $categories,
            'stores' => $stores,
            'filters' => $filters,
            'featuredDeals' => $this->featuredDeals(),
            'activeCoupons' => $this->activeCoupons(),
            'recentlyViewedProducts' => $this->recentlyViewedProducts($request),
            'pageMeta' => $pageMeta,
        ]);
    }

    public function stores(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'integer', 'exists:store_categories,id'],
        ]);

        $stores = Store::query()
            ->with(['translations', 'translation', 'storeCategory.translation'])
            ->where('approved', true)
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('translations', function ($translationQuery) use ($search) {
                    $translationQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orWhere('address', 'like', '%' . $search . '%');
                });
            })
            ->when($filters['category'] ?? null, fn ($query, $categoryId) => $query->where('store_category_id', $categoryId))
            ->withCount(['products as active_products_count' => fn ($query) => $query->where('status', true)])
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $storeCategories = \App\Models\StoreCategory::with(['translations', 'translation'])->get();

        return view('frontend.store-public.index', [
            'stores' => $stores,
            'filters' => $filters,
            'storeCategories' => $storeCategories,
        ]);
    }

    public function showProduct(Request $request, Product $product)
    {
        abort_unless(
            $product->status && $product->store?->approved,
            404
        );

        $product->load([
            'translations',
            'translation',
            'store.translations',
            'store.translation',
            'store.storeCategory.translation',
            'category.translations',
            'category.translation',
            'reviews.user.translation',
        ]);

        $this->rememberViewedProduct($request, $product);

        $userReview = auth('web')->check()
            ? $product->reviews->firstWhere('user_id', auth('web')->id())
            : null;

        $canReview = auth('web')->check()
            && auth('web')->user()->orders()
                ->where('status', '!=', 'cancelled')
                ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
                ->exists();

        $relatedProducts = $this->catalogQuery()
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                $query->where('store_id', $product->store_id)
                    ->orWhere('category_id', $product->category_id);
            })
            ->latest('id')
            ->take(4)
            ->get();

        $recentlyViewedProducts = $this->recentlyViewedProducts($request, $product->id);
        $activeCoupons = $this->activeCoupons(2);

        return view('frontend.product.show', compact(
            'product',
            'relatedProducts',
            'userReview',
            'canReview',
            'recentlyViewedProducts',
            'activeCoupons'
        ));
    }

    public function showStore(Store $store)
    {
        abort_unless($store->approved, 404);

        $store->load([
            'translations',
            'translation',
            'storeCategory.translation',
        ]);

        $products = $this->catalogQuery()
            ->where('store_id', $store->id)
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $featuredProducts = $this->catalogQuery()
            ->where('store_id', $store->id)
            ->where('stock', '>', 0)
            ->orderByRaw('COALESCE(offer_price, price) asc')
            ->take(4)
            ->get();

        return view('frontend.store-public.show', compact('store', 'products', 'featuredProducts'));
    }
}
