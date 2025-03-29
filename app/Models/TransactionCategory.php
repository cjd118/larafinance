<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(TransactionCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(TransactionCategory::class, 'parent_id');
    }

    public function getPath()
    {
        $path = [$this];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent);
            $parent = $parent->parent;
        }

        return $path;
    }

    public function getPathFormatted($separator = ' > ')
    {
        $pathArray = $this->getPath();

        $pathNames = array_map(function ($category) {
            return $category->name;
        }, $pathArray);


        return implode($separator, $pathNames);
    }
}
