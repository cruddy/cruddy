<?php namespace Kalnoy\Cruddy\Entity;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Kalnoy\Cruddy\ComponentInterface;
use Kalnoy\Cruddy\PermissionsInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Entity implements FormInterface, ComponentInterface {

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var PermissionsInterface
     */
    protected $permissions;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * The id.
     *
     * @var string
     */
    protected $id;

    /**
     * The form processor.
     *
     * @var FormInterface
     */
    protected $form;

    /**
     * The list of entity's fields.
     *
     * @var \Kalnoy\Cruddy\Entity\Fields\Collection
     */
    protected $fields;

    /**
     * The list of entity's columns.
     *
     * @var Columns\Collection
     */
    protected $columns;

    /**
     * The list of entity's related entities.
     *
     * @var Related\Collection
     */
    protected $related;

    /**
     * The id of a column that is used as model's title.
     *
     * @var string
     */
    public $primary_column;

    /**
     * The primary column instance.
     *
     * @var \Kalnoy\Cruddy\Entity\Columns\AbstractColumn
     */
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
     * @param Factory               $factory
     * @param PermissionsInterface  $permissions
     * @param TranslatorInterface   $translator
     * @param string                $id
     */
    public function __construct(Factory $factory, PermissionsInterface $permissions, TranslatorInterface $translator, $id)
    {
        $this->factory = $factory;
        $this->id = $id;
        $this->permissions = $permissions;
        $this->translator = $translator;
    }

    /**
     * Apply configuration options.
     *
     * @param array $config
     *
     * @return $this
     */
    public function configure(array $config)
    {
        $this->primary_column = array_get($config, 'primary_column');
        $this->order_by = array_get($config, 'order_by');

        return $this;
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
     * Find a model or throw ModelNotFoundException if none is found.
     *
     * @param $id
     *
     * @return Entity
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id)
    {
        $instance = $this->find($id);

        if ($instance === null) throw new ModelNotFoundException();

        return $instance;
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
     * @param  array $filters
     * @param  array $order
     *
     * @return array
     */
    public function all($search = null, array $filters = array(), array $order = array(), array $columns = array('*'))
    {
        $builder = $this->form()->instance()->newQuery();
        $query = $builder->getQuery();

        $allColumns = $this->columns()
            ->modifyQuery($builder)
            ->applyOrder($query, $order)
            ->applyConstraints($query, $filters);

        if ($search)
        {
            $query->whereNested(function ($nested) use ($allColumns, $search) {
                $allColumns->search($nested, $search);
            });
        }

        return $this->paginate($builder, $columns);
    }

    /**
     * Search items using "search anything" feature.
     *
     * @param       $query
     * @param array $columns
     * @return array
     */
    public function search($query, array $columns = array('*'))
    {
        return $this->all($query, array(), $this->getDefaultOrder(), $columns);
    }

    protected function paginate(Builder $builder, $columns = array('*'))
    {
        $columns = $this->columns($columns);

        $paginated = $builder->paginate();
        $paginated->setItems($columns->collectionData($paginated->getItems()));

        return $paginated;
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
     * @return int
     */
    public function delete($ids)
    {
        return $this->form()->delete($ids);
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
            'can_view' => $this->permissions->canView($this),
            'can_create' => $this->permissions->canCreate($this),
            'can_update' => $this->permissions->canUpdate($this),
            'can_delete' => $this->permissions->canDelete($this),
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
     * @return FormInterface
     */
    public function form()
    {
        if ($this->form === null)
        {
            return $this->form = $this->factory->createForm($this);
        }

        return $this->form;
    }

    /**
     * Get a collection of entity's fields.
     *
     * @return Fields\Collection
     */
    public function fields(array $items = array('*'))
    {
        if ($this->fields === null)
        {
            $this->fields = $this->factory->createFields($this);
        }

        return $this->fields->only($items);
    }

    /**
     * Get a list of entity's columns.
     *
     * @param array $items
     *
     * @return Columns\Collection
     */
    public function columns(array $items = array('*'))
    {
        if ($this->columns === null)
        {
            $this->columns = $this->factory->createColumns($this);
        }

        return $this->columns->only($items);
    }

    /**
     * Get a collection of entity's related entities.
     *
     * @param array $items
     *
     * @return Related\Collection
     */
    public function related(array $items = array('*'))
    {
        if ($this->related === null)
        {
            $this->related = $this->factory->createRelated($this);
        }

        return $this->related->only($items);
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function translate($key)
    {
        $key = "{$this->id}.{$key}";

        if (($line = $this->translator->trans($key)) !== $key) return $line;
    }

    public function getTitle()
    {
        return $this->translate("title") ?: ucfirst(humanize($this->id));
    }

    public function getSingular()
    {
        return $this->translate("singular") ?: ucfirst(str_singular(humanize($this->id)));
    }

    public function getDefaultOrder()
    {
        if (!$this->order_by) return array();

        return [ $this->order_by => $this->columns()->get($this->order_by)->order_dir ];
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