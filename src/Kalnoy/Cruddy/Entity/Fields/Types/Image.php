<?php  namespace Kalnoy\Cruddy\Entity\Fields\Types;

class Image extends File {

    /**
     * @inheritdoc
     *
     * @var string
     */
    public $accepts = 'image/*';

    /**
     * The width of the thumbnail.
     *
     * @var int
     */
    public $width = 40;

    /**
     * The height of the thumbnail.
     *
     * @var int
     */
    public $height = 40;

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