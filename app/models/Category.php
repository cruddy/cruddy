<?php

class Category extends Eloquent {

    protected $fillable = array('title');

    public function parent()
    {
        return $this->belongsTo('Category');
    }

    public function children()
    {
        return $this->hasMany('Category', 'parent_id');
    }

    public function products()
    {
        return $this->belongsToMany('Product', 'product_categories');
    }
}