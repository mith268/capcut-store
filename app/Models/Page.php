<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'product_id',
        'theme_preset',
        'status',
        'is_homepage',
        'published_version_id',
        'draft_sections',
        'published_sections',
        'meta_title',
        'meta_description',
        'social_image',
        'custom_css_vars',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_homepage' => 'boolean',
            'draft_sections' => 'array',
            'published_sections' => 'array',
            'custom_css_vars' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->orderBy('version_number', 'desc');
    }

    public function publish(?int $userId = null, ?string $summary = 'Published via Page Editor'): PageRevision
    {
        $nextVersion = ($this->revisions()->max('version_number') ?? 0) + 1;

        $revision = $this->revisions()->create([
            'version_number' => $nextVersion,
            'sections' => $this->draft_sections,
            'theme_preset' => $this->theme_preset,
            'created_by' => $userId,
            'change_summary' => $summary,
        ]);

        $this->update([
            'published_sections' => $this->draft_sections,
            'published_version_id' => $revision->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $revision;
    }

    public function restoreRevision(PageRevision $revision): void
    {
        $this->update([
            'draft_sections' => $revision->sections,
            'theme_preset' => $revision->theme_preset,
        ]);
    }

    public function duplicate(string $newTitle, string $newSlug, ?int $newProductId = null): self
    {
        return self::create([
            'title' => $newTitle,
            'slug' => $newSlug,
            'product_id' => $newProductId ?? $this->product_id,
            'theme_preset' => $this->theme_preset,
            'status' => 'draft',
            'is_homepage' => false,
            'draft_sections' => $this->draft_sections,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'social_image' => $this->social_image,
        ]);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }
}
