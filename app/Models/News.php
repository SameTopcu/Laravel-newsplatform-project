<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class News extends Model
{
    protected $fillable = [
        'author_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'source_url',
        'source_url_hash',
        'thumbnail',
        'thumbnail_caption',
        'is_published',
        'is_breaking',
        'view_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_published' => 'boolean',
            'is_breaking' => 'boolean',
            'view_count' => 'integer',
        ];
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getReadTimeAttribute(): int
    {
        $text = trim(strip_tags((string) $this->content));
        if ($text === '') {
            return 1;
        }
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return max(1, (int) ceil(count($words) / 200));
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
