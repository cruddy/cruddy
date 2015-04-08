<?php

namespace Kalnoy\Cruddy\Schema\Layout;

class Row extends Container {

    /**
     *  The maximum span for the elements.
     */
    const MAX_SPAN = 12;

    /**
     * Init a row.
     *
     * @param array|\Closure $items
     */
    public function __construct($items = null)
    {
        if ($items instanceof \Closure)
        {
            $items($this);
        }
        elseif ($items)
        {
            $this->field($items);
        }
    }

    /**
     * @return string
     */
    public function modelClass()
    {
        return 'Cruddy.Layout.Row';
    }

    /**
     * @param Element $item
     *
     * @return bool
     */
    protected function canBeAdded(Element $item)
    {
        return $item instanceof Col;
    }

    /**
     * Add fields.
     *
     * @param array $items
     *
     * @return $this
     */
    public function field($items)
    {
        $items = is_array($items) ? $items : func_get_args();

        foreach ($items as $item)
        {
           $span = null;

           if (is_array($item) && is_numeric(reset($item)))
           {
                $span = array_shift($item);
           }

           $this->col($item, $span);
        }

        return $this;
    }

    /**
     * Define a column.
     *
     * @param string|array|\Closure $items
     * @param int $span
     *
     * @return $this
     */
    public function col($items, $span = null)
    {
        return $this->add(new Col($items, $span));
    }

    /**
     * Set span on columns that do not have one.
     *
     * @return $this
     */
    protected function fillSpan()
    {
        $free = self::MAX_SPAN;
        $noSpan = [];

        foreach ($this->items as $col)
        {
            if ($col->span)
            {
                $free -= $col->span;
            }
            else
            {
                $noSpan[] = $col;
            }
        }

        if (count($noSpan))
        {
            $spot = max(1, floor($free / count($noSpan)));

            foreach ($noSpan as $col)
            {
                $col->span = $spot;
            }
        }

        return $this->normalizeSpan();
    }

    /**
     * Make sure that total span does'nt exceeds max columns.
     *
     * @return $this
     */
    protected function normalizeSpan()
    {
        $total = 0;

        foreach ($this->items as $col)
        {
            $total += $col->span;
        }

        if ($total <= self::MAX_SPAN) return $this;

        foreach ($this->items as $col)
        {
            $col->span = max(1, floor(($total / $col->span) * self::MAX_SPAN));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $this->fillSpan();

        return parent::toArray();
    }

}