<?php namespace SammyK\FacebookQueryBuilder;

class GraphNode extends GraphEdge
{
    /**
     * Compiled values that are ready to be concatenated.
     *
     * @var array
     */
    protected $compiled_values = [];

    /**
     * Clear the compiled values.
     */
    public function clearCompiledValues()
    {
        $this->compiled_values = [];
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
     * Compile the limit value.
     */
    public function compileLimit()
    {
        if ($this->limit === 0) return;

        $this->compiled_values[] = 'limit=' . $this->limit;
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
     * Compile the final URL as a string.
     *
     * @return string
     */
    public function asUrl()
    {
        $this->clearCompiledValues();

        $this->compileLimit();
        $this->compileFields();
        $this->compileModifiers();

        $append = '';
        if (count($this->compiled_values) > 0)
        {
            $append = '?' . implode('&', $this->compiled_values);
        }

        //$append = '?' . http_build_query($values);
        return '/' . $this->name . $append;
    }
}
