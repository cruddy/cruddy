<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\AbstractField;

/**
 * The field for uploading and displaying images.
 *
 * @method $this width(int $value)
 * @method $this height(int $value)
 * @property int $width
 * @property int $height
 */
class Image extends File
{
    /**
     * @return string
     */
    protected function getModelClass()
    {
        return 'Cruddy.Fields.Image';
    }

    /**
     * @return string
     */
    protected function getAccepts()
    {
        return 'image/*,image/jpeg';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),

        ] + parent::toArray();
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->get('width', null);
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->get('height', 80);
    }

    /**
     * Set thumbnail's max size.
     *
     * @param int $width
     * @param int $height
     *
     * @return $this
     */
    public function thumbnailSize($width, $height)
    {
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Set thumbnail's max width.
     *
     * @param int $value
     *
     * @return $this
     */
    public function thumbnailWidth($value)
    {
        $this->width = $value;
        $this->height = null;

        return $this;
    }

    /**
     * Set thumbnail's max height.
     *
     * @param int $value
     *
     * @return $this
     */
    public function thumbnailHeight($value)
    {
        $this->height = $value;
        $this->width = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStorageName()
    {
        return $this->get('storeTo', 'images');
    }

    /**
     * @inheritDoc
     */
    public function getModelValueForColumn($model)
    {
        if ( ! $src = $this->getModelValue($model)) {
            return null;
        }

        $url = $this->urlToFile($src);
        $thumb = $this->urlToFile($src, [ 'width' => $this->getWidth(),
                                          'height' => $this->getHeight() ]);

        return 
            '<a href="'.$url.'" title="'.$src.'" target="_blank" data-trigger="fancybox">'.
                '<img src="'.$thumb.'" alt="'.$src.'">'.
            '</a>';
    }

}