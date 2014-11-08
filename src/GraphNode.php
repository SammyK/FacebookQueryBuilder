<?php namespace SammyK\FacebookQueryBuilder;

class GraphNode
{
    /**
     * The name of the node.
     *
     * @var string
     */
    protected $name;

    /**
     * The modifiers that will be appended to the node.
     *
     * @var array
     */
    protected $modifiers = [];

    /**
     * The fields & GraphEdge's that we want to request.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Compiled values that are ready to be concatenated.
     *
     * @var array
     */
    protected $compiled_values = [];

    /**
     * Create a new GraphNode value object.
     *
     * @param string $name
     * @param array $fields
     * @param int $limit
     */
    public function __construct($name, $fields = [], $limit = 0)
    {
        $this->name = $name;
        $this->fields($fields);
        if ($limit)
        {
            $this->limit($limit);
        }
    }

    /**
     * Modifier data to be sent with this node.
     *
     * @param array $data
     *
     * @return \SammyK\FacebookQueryBuilder\GraphNode
     */
    public function modifiers(array $data)
    {
        $this->modifiers = array_merge($this->modifiers, $data);

        return $this;
    }

    /**
     * Gets the modifiers for this node.
     *
     * @return array
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * Gets a modifier if it is set.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getModifier($key)
    {
        return isset($this->modifiers[$key]) ? $this->modifiers[$key] : null;
    }

    /**
     * Set the limit for this node.
     *
     * @param int $limit
     *
     * @return \SammyK\FacebookQueryBuilder\GraphNode$this
     */
    public function limit($limit)
    {
        return $this->modifiers(['limit' => $limit]);
    }

    /**
     * Gets the limit for this node.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->getModifier('limit');
    }

    /**
     * Set the fields for this node.
     *
     * @param mixed $fields
     *
     * @return \SammyK\FacebookQueryBuilder\GraphNode
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
     * Gets the fields for this node.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Clear the compiled values.
     */
    public function resetCompiledValues()
    {
        $this->compiled_values = [];
    }

    /**
     * Compile the modifier values.
     */
    public function compileModifiers()
    {
        if (count($this->modifiers) === 0) return;

        $this->compiled_values[] = http_build_query($this->modifiers, '', '&');
    }

    /**
     * Compile the field values.
     */
    public function compileFields()
    {
        if (count($this->fields) === 0) return;

        $this->compiled_values[] = 'fields=' . implode(',', $this->fields);
    }

    /**
     * Compile the the full URL.
     *
     * @return string
     */
    public function compileUrl()
    {
        $append = '';
        if (count($this->compiled_values) > 0)
        {
            $append = '?' . implode('&', $this->compiled_values);
        }
        return '/' . $this->name . $append;
    }

    /**
     * Compile the final URL as a string.
     *
     * @return string
     */
    public function asUrl()
    {
        $this->resetCompiledValues();

        $this->compileModifiers();
        $this->compileFields();
        return $this->compileUrl();
    }

    /**
     * Compile the final URL as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->asUrl();
    }
}
