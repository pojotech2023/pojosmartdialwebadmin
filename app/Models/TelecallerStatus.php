<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TelecallerStatus extends Model
{
    use HasFactory;

    protected $table = 'telecaller_statuses';

    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
        'requires_appointment',
        'requires_location',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requires_appointment' => 'boolean',
        'requires_location'    => 'boolean',
        'is_active'            => 'boolean',
        'sort_order'           => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function ($status) {
            if (empty($status->slug) && !empty($status->name)) {
                $status->slug = Str::slug($status->name);
            }
            if (empty($status->color)) {
                $status->color = '#6366f1';
            }
        });
    }

    /**
     * Scope for active statuses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
}
