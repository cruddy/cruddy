<?php namespace Kalnoy\Cruddy\Form\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Form\AbstractField;

/**
 * The field for uploading and displaying images.
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class Image extends File
{
    /**
     * @var int
     */
    public $width;

    /**
     * @var int
     */
    public $height;

    /**
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.Image';
    }

    /**
     * @return string
     */
    public function getAccept()
    {
        return 'image/*,image/jpeg';
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),

        ] + parent::getConfig();
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height ?: 80;
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
     * @inheritdoc
     */
    public function getStorageName()
    {
        return $this->storageName ?: 'images';
    }

}