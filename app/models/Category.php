<?php

class Category extends Eloquent {

    protected $fillable = array('title', 'slug', 'images');

    public function getImagesAttribute($value)
    {
        return empty($value) ? [] : json_decode($value);
    }

    public function setImagesAttribute($value)
    {
        $images = $this->images;

        $delete = array_diff($images, $value);

        if (!empty($delete))
        {
            $root = public_path();

            foreach ($delete as $i => $file)
            {
                $delete[$i] = $root.$file;
            }

            File::delete($delete);
        }

        $this->attributes['images'] = json_encode($value);
    }

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