<?php  namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\AbstractField;

class Image extends File {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Image';

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'image';

    /**
     * @inheritdoc
     *
     * @var string
     */
    public $accepts = 'image/*';

    /**
     * The max width of the thumbnail.
     *
     * @var int
     */
    public $width;

    /**
     * The max height of the thumbnail.
     *
     * @var int
     */
    public $height = 80;

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return
        [
            'width' => $this->width,
            'height' => $this->height,

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