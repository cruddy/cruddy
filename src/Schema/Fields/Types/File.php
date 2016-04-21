<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Schema\Fields\BaseField;
use Kalnoy\Cruddy\Service\Files\FileStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File input field.
 *
 * @property string $storeTo
 * @method $this storeTo(string $value)
 *
 * @property bool $many
 * @method $this many(bool $value = true)
 *
 * @since 1.0.0
 */
class File extends BaseField
{
    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function getModelClass()
    {
        return 'Cruddy.Fields.File';
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]|string
     */
    public function getModelValue($model)
    {
        $value = parent::getModelValue($model);

        if ($this->isMultiple()) {
            return is_array($value) ? $value : [ ];
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'multiple' => $this->isMultiple(),
            'storage' => $this->getStorageName(),

        ] + parent::toArray();
    }

    /**
     * @return bool
     */
    public function isMultiple()
    {
        return $this->get('many', false);
    }

    /**
     * @return FileStorage
     */
    protected function getStorage()
    {
        return app('cruddy.files')->storage($this->getStorageName());
    }

    /**
     * @return string
     */
    public function getStorageName()
    {
        return $this->get('storeTo', 'files');
    }

    /**
     * @param string $path
     * @param array $params
     *
     * @return string
     */
    public function urlToFile($path, array $params = [])
    {
        $path = $this->getStorageName().'/'.ltrim($path, '\\/');
        $info = pathinfo($path);

        $params['storage_path'] = $info['dirname'];
        $params['storage_file'] = $info['basename'];

        return url()->route('cruddy.files.show', $params);
    }

    /**
     * @inheritDoc
     */
    public function getModelValueForColumn($model)
    {
        if ( ! $src = $this->getModelValue($model)) {
            return null;
        }

        return
            '<a href="'.$this->urlToFile($src).'" target="_blank">'.$src.'</a>';
    }
}