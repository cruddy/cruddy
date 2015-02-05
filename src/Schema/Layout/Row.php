<?php

namespace Kalnoy\Cruddy\Schema\Layout;

class Row extends Container {

    /**
     *  The maximum span for the elements.
     */
    const MAX_SPAN = 12;

    /**
     * {@inheritdoc}
     */
    protected $class = 'Row';

    /**
     * Init a row.
     *
     * @param array|\Closure $items
     */
    public function __construct($items)
    {
        if ($items instanceof \Closure)
        {
            $items($this);
        }
        else
        {
            $this->field($items);
        }
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

           $this->col($span, $item);
        }

        return $this;
    }

    /**
     * Define a column.
     *
     * @param int $span
     * @param string|array|\Closure $items
     *
     * @return $this
     */
    public function col($span, $items)
    {
        return $this->add(new Col($span, $items));
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
     * Make sure that total span doesn't exceeds max columns.
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
    public function compile()
    {
        $this->fillSpan();

        return parent::compile();
    }

}