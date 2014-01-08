<?php  namespace Kalnoy\Cruddy\Entity\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Entity\Fields\AbstractField;

class Image extends File {

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
        return parent::toArray() +
        [
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    function getJavaScriptClass()
    {
        return 'Image';
    }
}