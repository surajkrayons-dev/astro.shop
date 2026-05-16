<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'code',
        'name',
        'slug',
        'stone_name',

        'ratti_options',

        'description',
        'benefits',
        'how_to_use',
        'purity',

        'specifications',
        'faq',

        'rating_avg',
        'rating_count',

        'before_price',
        'after_price',

        'hsn_code',
        'gst_rate',

        'shipping_info',
        'origin',
        'lab_certificates',
        'planet',

        'meta_title',
        'meta_description',
        'meta_keywords',

        'weight',
        'length',
        'breadth',
        'height',

        'stock_qty',
        'stock_status',

        'image',
        'status'
    ];

    protected $casts = [
        'ratti_options' => 'array',
        'specifications' => 'array',
        'faq' => 'array',
        'lab_certificates' => 'array',
        'meta_keywords' => 'array',
        'before_price' => 'float',
        'after_price' => 'float',
        'gst_rate' => 'float',
        'weight' => 'float',
        'length' => 'float',
        'breadth' => 'float',
        'height' => 'float',
        'rating_avg' => 'float',
        'rating_count' => 'integer',
        'stock_qty' => 'integer',
        'status' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function storeReviews()
    {
        return $this->hasMany(StoreReview::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public static function updateRating($productId)
    {
        $stats = StoreReview::where('product_id', $productId)
            ->selectRaw('COUNT(*) as total, AVG(rating) as avg')
            ->first();

        self::where('id', $productId)->update([
            'rating_count' => $stats->total ?? 0,
            'rating_avg'   => round($stats->avg ?? 0, 2),
        ]);
    }

    public static function resolveStockStatus(int $qty): string
    {
        if ($qty <= 0) {
            return 'out_of_stock';
        }

        if ($qty <= 10) {
            return 'few_left';
        }

        return 'in_stock';
    }
}