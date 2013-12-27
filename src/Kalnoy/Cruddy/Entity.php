<?php namespace Kalnoy\Cruddy;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Fields\Collection as FieldCollection;
use Illuminate\Translation\TranslatorInterface;

class Entity implements FormInterface, ComponentInterface {

    /**
     * The entity factory.
     *
     * @var FactoryInterface
     */
    protected $factory;

    protected $permissions;

    /**
     * The id.
     *
     * @var string
     */
    protected $id;

    /**
     * The processing form.
     *
     * @var EntityFormInterface
     */
    protected $form;

    /**
     * The list of model's fields.
     *
     * @var Fields\Collection
     */
    protected $fields;

    /**
     * The list of model's columns.
     *
     * @var Columns\Collection
     */
    protected $columns;

    protected $related;

    /**
     * The id of a column that is used as model's title.
     *
     * @var string
     */
    public $primary_column;

    protected $primaryColumn;

    /**
     * The id of the column by which model collection will be sorted.
     *
     * @var string
     */
    public $order_by;

    /**
     * Initialize a model.
     *
     * @param string             $id
     * @param EntityFormInterface $form
     * @param array              $fields
     */
    public function __construct(FactoryInterface $factory, PermissionsInterface $permissions, $id)
    {
        $this->factory = $factory;
        $this->id = $id;
        $this->permissions = $permissions;

        $this->primary_column = $factory->config("{$id}.primary_column");
        $this->order_by = $factory->config("{$id}.order_by");
    }

    /**
     * Get an eloquent model instance.
     *
     * @return Eloquent
     */
    public function instance()
    {
        return $this->form()->instance();
    }

    /**
     * Find an item for an update operation.
     *
     * @param  int|array $id
     *
     * @return Entity
     */
    public function find($id)
    {
        if (is_array($id)) return $this->findMany($id);

        $builder = $this->newFindQuery();

        $builder->where($builder->getModel()->getQualifiedKeyName(), $id);

        return $builder->first();
    }

    /**
     * Find a list of items for an update operation.
     *
     * @param  array  $ids
     *
     * @return array
     */
    public function findMany(array $ids)
    {
        $builder = $this->newFindQuery();

        $builder->whereIn($builder->getModel()->getQualifiedKeyName(), $ids);

        return $builder->get();
    }

    /**
     * Begin new find query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newFindQuery()
    {
        $instance = $this->form()->instance();
        $builder = $instance->newQuery();

        // Modify query with all fields
        $this->fields()->modifyQuery($builder);

        return $builder;
    }

    /**
     * List all items.
     *
     * @param  array  $filters
     * @param  array  $order
     * @param  int    $page
     *
     * @return array
     */
    public function search(array $filters = array(), array $order = array())
    {
        $builder = $this->form()->instance()->newQuery();

        $this->columns()
            ->modifyQuery($builder)
            ->applyOrder($builder, $order)
            ->applyConstraints($builder, $filters);

        return $builder->paginate();
    }

    /**
     * Create a new item using data.
     *
     * @param  array  $data
     *
     * @return false|Eloquent
     */
    public function create(array $data)
    {
        $data = $this->fields()->process($this->form()->instance(), $data);

        return $this->form()->create($data);
    }

    /**
     * Update an item.
     *
     * @param  array  $data
     *
     * @return false|Eloquent
     */
    public function update(Eloquent $instance, array $data)
    {
        $data = $this->fields()->process($this->form()->instance(), $data);

        return $this->form()->update($instance, $data);
    }

    /**
     * Delete a model or a set of models.
     *
     * @param  int|array $ids
     *
     * @return void
     */
    public function delete($ids)
    {
        $this->form()->delete($ids);
    }

    /**
     * Get an error list since last operation.
     *
     * @return \Illuminate\Support\MessageBag
     */
    public function errors()
    {
        return $this->form()->errors();
    }

    /**
     * Get runtime data.
     *
     * @param  Eloquent $model
     *
     * @return array
     */
    public function runtime(Eloquent $model)
    {
        return array(
            'id' => $this->id,
            'can_view' => $this->canView(),
            'can_create' => $this->canCreate(),
            'can_update' => $this->canUpdate(),
            'can_delete' => $this->canDelete(),
        );
    }

    /**
     * Get a model id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the form that processes data.
     *
     * @return ModelFormInterface
     */
    public function form()
    {
        if ($this->form === null)
        {
            return $this->form = $this->factory->createForm($this->id);
        }

        return $this->form;
    }

    /**
     * Get a list of model's fields.
     *
     * @return array
     */
    public function fields()
    {
        if ($this->fields === null)
        {
            return $this->fields = $this->factory->createFields($this);
        }

        return $this->fields;
    }

    /**
     * Get a list of model's columns.
     *
     * @return array
     */
    public function columns()
    {
        if ($this->columns === null)
        {
            return $this->columns = $this->factory->createColumns($this);
        }

        return $this->columns;
    }

    public function related()
    {
        if ($this->related === null)
        {
            return $this->related = $this->factory->createRelated($this);
        }

        return $this->related;
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function getTranslator()
    {
        return $this->factory->getTranslator();
    }

    public function translate($key)
    {
        $translator = $this->getTranslator();

        $key = "{$this->id}.{$key}";

        if (($line = $translator->trans($key)) !== $key) return $line;
    }

    public function getTitle()
    {
        return $this->translate("title") ?: ucfirst(humanize($this->id));
    }

    public function getSingular()
    {
        return $this->translate("singular") ?: ucfirst(str_singular(humanize($this->id)));
    }

    public function canView()
    {
        return $this->permissions->canView($this);
    }

    public function canCreate()
    {
        return $this->permissions->canCreate($this);
    }

    public function canUpdate()
    {
        return $this->permissions->canUpdate($this);
    }

    public function canDelete()
    {
        return $this->permissions->canDelete($this);
    }

    /**
     * Get primary column instance.
     *
     * @return false|Columns\ColumnInterface
     */
    public function getPrimaryColumn()
    {
        if ($this->primaryColumn === null)
        {
            if ($this->primary_column === null)
            {
                return $this->primaryColumn = false;
            }

            $this->primaryColumn = $this->columns()->get($this->primary_column, false);
        }

        return $this->primaryColumn;
    }

    /**
     * Get a title of a model's instance.
     *
     * @param  Eloquent $model
     *
     * @return string
     */
    public function title(Eloquent $model)
    {
        $column = $this->getPrimaryColumn();

        if ($column === false) return $model->__toString();

        return $column->value($model);
    }

    /**
     * Convert an entity to a configuration array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            array(
                'soft_deleting' => $this->form()->instance()->isSoftDeleting(),
                'primary_column' => $this->primary_column,
                'title' => $this->getTitle(),
                'singular' => $this->getSingular(),
                'order_by' => $this->order_by,
                'fields' => array_values($this->fields()->toArray()),
                'columns' => array_values($this->columns()->toArray()),
                'related' => array_values($this->related()->toArray()),
                'defaults' => $this->fields()->data($this->form()->instance()),
            ),

            $this->runtime($this->form()->instance())
        );
    }

    /**
     * Convert an entity to a json string.
     *
     * @param  int    $options
     *
     * @return string
     */
    public function toJSON($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}