<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\BaseForm;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Helpers;
use Kalnoy\Cruddy\Schema\Fields\BaseInput;

/**
 * The field for displaying select box.
 *
 * @property string $prompt
 * @method $this prompt(StringField $value)
 *
 * @property bool $multiple
 * @method $this multiple(bool $value = true)
 *
 * @since 1.0.0
 */
class Enum extends BaseInput implements Filter
{
    /**
     * @var array
     */
    private $items;

    /**
     * @param BaseForm $form
     * @param string $id
     * @param $items
     */
    public function __construct(BaseForm $form, $id, $items)
    {
        parent::__construct($form, $id);

        $this->items = $items;
    }

    /**
     * @return mixed
     */
    public function isMultiple()
    {
        return $this->get('multiple', false);
    }

    /**
     * @return string
     */
    public function getPrompt()
    {
        return Helpers::tryTranslate($this->get('prompt'));
    }

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function getModelClass()
    {
        return 'Cruddy.Fields.Enum';
    }

    /**
     * {@inheritdoc}
     */
    public function parseInputValue($value)
    {
        $value = $this->parse($value);

        return $this->isMultiple() ? $value : reset($value);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterConstraint(Builder $query, $data)
    {
        if ($this->isMultiple() || ! ($data = $this->parse($data))) return;

        $query->whereNested(function ($inner) use ($data) {
            foreach ($data as $key) {
                $inner->orWhere($this->id, '=', $key);
            }
        });
    }

    /**
     * Translate items if possible.
     *
     * @param array $items
     *
     * @return array
     */
    protected function translateItems($items)
    {
        foreach ($items as $key => $value) {
            $items[$key] = Helpers::tryTranslate($value);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'prompt' => $this->getPrompt(),
            'items' => $this->translateItems($this->getItems()),
            'multiple' => $this->isMultiple(),

        ] + parent::toArray();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        if ($this->items instanceof \Closure) {
            $this->items = value($this->items);
        }

        return $this->items;
    }

    /**
     * @param $value
     *
     * @return array
     */
    protected function parse($value)
    {
        if (empty($value)) return [];

        if (is_string($value)) $value = explode(',', $value);

        return $value;
    }

}