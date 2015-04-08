<?php  namespace Kalnoy\Cruddy\Schema\Fields\Types;

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
class Image extends File {

    /**
     * @return string
     */
    protected function modelClass()
    {
        return 'Cruddy.Fields.Image';
    }

    /**
     * @return string
     */
    protected function defaultAccepts()
    {
        return 'image/*,image/jpeg';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'width' => $this->get('width', null),
            'height' => $this->get('height', 80),

        ] + parent::toArray();
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

}