<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name_bn', 'name_en', 'icon', 'color', 'parent_id', 'sort_order', 'status'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Helper: current language name
    public function getNameAttribute()
    {
        return app()->getLocale() === 'bn' ? $this->name_bn : $this->name_en;
    }
}