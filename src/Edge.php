<?php namespace SammyK\FacebookQueryBuilder;

class Edge
{
    /**
     * The name of the edge.
     *
     * @var string
     */
    public $name;

    /**
     * The fields & edges that we want to pull from the edge.
     *
     * @var array
     */
    public $fields = [];

    /**
     * The modifiers that will be appended to the edge.
     *
     * @var array
     */
    public $modifiers = [];

    /**
     * The maximum number of records to return for this edge.
     *
     * @var int
     */
    public $limit;

    /**
     * Sets this as the root edge
     *
     * @var boolean
     */
    protected $is_root = false;

    /**
     * Create a new edge value object.
     *
     * @param string $name
     * @param array $fields
     * @param int $limit
     */
    public function __construct($name, $fields = [], $limit = 0)
    {
        $this->name = $name;
        $this->fields = $fields;
        $this->limit = $limit;
    }

    /**
     * Set the limit for this edge.
     *
     * @param int $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Set the fields for this edge.
     *
     * @param mixed $fields
     * @return $this
     */
    public function fields($fields)
    {
        if ( ! is_array($fields))
        {
            $fields = func_get_args();
        }

        $this->fields = array_merge($this->fields, $fields);

        return $this;
    }

    /**
     * Modifier data to be sent with this edge.
     *
     * @param array $data
     * @return \SammyK\FacebookQueryBuilder\Edge
     */
    public function with(array $data)
    {
        $this->modifiers = array_merge($this->modifiers, $data);

        return $this;
    }

    /**
     * Compile the modifier values.
     *
     * @return string
     */
    public function compileModifiers()
    {
        if (count($this->modifiers) === 0) return '';

        $processed_modifiers = [];

        foreach ($this->modifiers as $k => $v)
        {
            $processed_modifiers[] = $k . '(' . $v . ')';
        }

        $modifiers = implode('.', $processed_modifiers);

        return $modifiers ? '.' . $modifiers : '';
    }

    /**
     * Compile the field values.
     *
     * @return string
     */
    public function compileFields()
    {
        if (count($this->fields) === 0) return '';

        $processed_fields = [];

        foreach ($this->fields as $v)
        {
            $processed_fields[] = $v instanceof Edge ? (string) $v : urlencode($v);
        }

        return '{' . implode(',',$processed_fields) . '}';
    }

    /**
     * Compile the limit value.
     *
     * @return string
     */
    public function compileLimit()
    {
        return $this->limit > 0 ? '.limit(' . $this->limit . ')' : '';
    }

    /**
     * Compile the final edge.
     *
     * @return string
     */
    public function compileEdge()
    {
        return $this->name . $this->compileLimit() . $this->compileFields() . $this->compileModifiers();
    }

    /**
     * Convert the nested query into an array of endpoints.
     *
     * @return array
     */
    public function toEndpoints()
    {
        $endpoints = [];

        $children = $this->getChildEdges();
        foreach ($children as $child)
        {
            $endpoints[] = '/' . implode('/', $child);
        }

        return $endpoints;
    }

    /**
     * Arrange the child edge nodes into a multidimensional array.
     *
     * @return array
     */
    public function getChildEdges()
    {
        $edges = [];
        $has_children = false;

        foreach ($this->fields as $v)
        {
            if ($v instanceof Edge)
            {
                $has_children = true;

                $children = $v->getChildEdges();
                foreach ($children as $child_edges)
                {
                    $edges[] = array_merge([$this->name], $child_edges);
                }
            }
        }

        // Means this is the final node (no further sub edges)
        if ( ! $has_children)
        {
            $edges[] = [$this->name];
        }

        return $edges;
    }

    /**
     * Returns edge as nicely formatted string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->compileEdge();
    }
}
