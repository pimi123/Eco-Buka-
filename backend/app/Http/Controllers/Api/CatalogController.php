<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogController extends Controller
{
    private const SERIES = ['DELTA', 'RIVER', 'STREAM', 'RAPID', 'PowerOcean'];

    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => 'nullable|string|max:100', 'category' => 'nullable|string|max:150',
            'collection' => 'nullable|string|max:150', 'categories' => 'nullable|array|max:30',
            'categories.*' => 'string|max:150', 'series' => 'nullable|array|max:5',
            'series.*' => 'in:'.implode(',', self::SERIES),
            'min' => 'nullable|numeric|min:0|max:10000000', 'max' => 'nullable|numeric|min:0|max:10000000',
            'sale' => 'nullable|boolean', 'stock' => 'nullable|boolean',
            'sort' => 'nullable|in:recommended,price_asc,price_desc,newest',
            'page' => 'nullable|integer|min:1|max:10000', 'facets' => 'nullable|boolean',
        ]);
        $query = Product::query()->where('products.active', true);
        $context = null;
        if (!empty($data['category'])) {
            $category = Category::where('active', true)->where('slug', $data['category'])->firstOrFail();
            $context = ['name' => $category->name, 'description' => $category->description];
            $query->where(fn ($q) => $q->where('products.category_id', $category->id)->orWhereHas('categories', fn ($c) => $c->where('categories.id', $category->id)->where('category_product.active', true)));
        }
        if (!empty($data['collection'])) {
            $collection = Collection::where('active', true)->where('slug', $data['collection'])->firstOrFail();
            $context = ['name' => $collection->name, 'description' => $collection->description];
            $query->whereHas('collections', fn ($c) => $c->where('collections.id', $collection->id)->where('collection_product.active', true));
        }
        // One cached, bounded metadata result per context; never one query per option.
        $facets = null;
        if ($request->boolean('facets')) {
            $facets = Cache::remember('catalog-facets-v2-'.sha1(($data['category'] ?? '').'|'.($data['collection'] ?? '')), 60, function () use ($query) {
                $base = clone $query;
                $counts = Product::query()->selectRaw('COUNT(*)')->whereIn('products.id', (clone $base)->select('products.id'))
                    ->where(fn ($p) => $p->whereColumn('products.category_id', 'categories.id')
                        ->orWhereExists(fn ($pivot) => $pivot->selectRaw('1')->from('category_product')
                            ->whereColumn('category_product.product_id', 'products.id')->whereColumn('category_product.category_id', 'categories.id')->where('category_product.active', true)));
                $categories = Category::where('active', true)->select(['id', 'name', 'slug'])->selectSub($counts, 'count')->orderBy('sort_order')->get();
                $aggregates = (clone $base)->selectRaw('MIN(price) as min_price, MAX(price) as max_price');
                foreach (self::SERIES as $i => $series) {
                    $aggregates->selectRaw('SUM(CASE WHEN UPPER(products.name) LIKE ? THEN 1 ELSE 0 END) as series_'.$i, ['%'.strtoupper($series).'%']);
                }
                $stats = $aggregates->first();
                return ['categories' => $categories->filter(fn ($c) => $c->count > 0)->values()->toArray(), 'min' => (float) $stats->min_price, 'max' => (float) $stats->max_price,
                    'series' => collect(self::SERIES)->map(fn ($name, $i) => ['name' => $name, 'count' => (int) $stats->{'series_'.$i}])->filter(fn ($s) => $s['count'] > 0)->values()->all()];
            });
        }
        if ($term = trim($data['q'] ?? '')) {
            $term = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
            $query->where(function ($q) use ($term) {
                $q->whereRaw("products.name LIKE ? ESCAPE '!'", ['%'.$term.'%'])
                    ->orWhereRaw("products.short_description LIKE ? ESCAPE '!'", ['%'.$term.'%'])
                    ->orWhereHas('category', fn ($c) => $c->whereRaw("name LIKE ? ESCAPE '!'", ['%'.$term.'%']));
            });
        }
        if (!empty($data['categories'])) {
            $ids = Category::where('active', true)->whereIn('slug', $data['categories'])->pluck('id');
            $query->where(fn ($q) => $q->whereIn('products.category_id', $ids)->orWhereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids)->where('category_product.active', true)));
        }
        if (!empty($data['series'])) {
            $query->where(function ($q) use ($data) {
                foreach ($data['series'] as $series) $q->orWhereRaw('UPPER(products.name) LIKE ?', ['%'.strtoupper($series).'%']);
            });
        }
        if (isset($data['min'])) $query->where('products.price', '>=', $data['min']);
        if (isset($data['max'])) $query->where('products.price', '<=', $data['max']);
        if ($request->boolean('sale')) $query->where('products.in_stock', true)->where('products.price', '>', 0)->whereColumn('products.old_price', '>', 'products.price');
        if ($request->boolean('stock')) $query->where('products.in_stock', true);
        if (($data['sort'] ?? 'recommended') !== 'recommended') $query->inStockFirst();
        switch ($data['sort'] ?? 'recommended') {
            case 'price_asc': $query->orderByRaw('CASE WHEN price IS NULL THEN 1 ELSE 0 END')->orderBy('price'); break;
            case 'price_desc': $query->orderByRaw('CASE WHEN price IS NULL THEN 1 ELSE 0 END')->orderByDesc('price'); break;
            case 'newest': $query->orderByDesc('created_at'); break;
            default:
                $query->discountFirst();
                if (isset($collection)) {
                    $query->orderBy(\Illuminate\Support\Facades\DB::table('collection_product')->select('sort_order')->whereColumn('product_id', 'products.id')->where('collection_id', $collection->id)->limit(1));
                } else $query->orderBy('products.sort_order');
        }
        $result = $query->orderBy('products.id')->with('category:id,name,slug')->paginate(24);
        return response()->json(['data' => $result->items(), 'total' => $result->total(), 'current_page' => $result->currentPage(), 'last_page' => $result->lastPage(), 'facets' => $facets, 'context' => $context]);
    }
}
