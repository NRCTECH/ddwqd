<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategorySortOption extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function sortOption(): BelongsToMany
    {
        return $this->belongsToMany(
            SortOption::class,
            'sort_options',
            'sort_option_id'
        );
    }
}
