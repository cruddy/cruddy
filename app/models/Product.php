<?php

class Product extends Eloquent {

    protected $fillable = [
        'title', 'description',
    ];

    public function categories()
    {
        return $this->belongsToMany('Category', 'product_categories');
    }
}