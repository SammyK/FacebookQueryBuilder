<?php namespace SammyK\FacebookQueryBuilder;

class GraphNode
{
    /**
     * The name of the fields param
     *
     * @const string
     */
    const PARAM_FIELDS = 'fields';

    /**
     * The name of the limit param
     *
     * @const string
     */
    const PARAM_LIMIT = 'limit';

    /**
     * The name of the access token param
     *
     * @const string
     */
    const PARAM_ACCESS_TOKEN = 'access_token';

    /**
     * The name of the app secret proof param
     *
     * @const string
     */
    const PARAM_APP_SECRET_PROOF = 'appsecret_proof';

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
    protected $compiledValues = [];

    /**
     * Create a new GraphNode value object.
     *
     * @param string $name
     * @param array  $fields
     * @param int    $limit
     */
    public function __construct($name, $fields = [], $limit = 0)
    {
        $this->name = $name;
        $this->fields($fields);
        if ($limit) {
            $this->limit($limit);
        }
    }

    /**
     * Modifier data to be sent with this node.
     *
     * @param array $data
     *
     * @return GraphNode
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
     * @return GraphNode
     */
    public function limit($limit)
    {
        return $this->modifiers([
          static::PARAM_LIMIT => $limit,
        ]);
    }

    /**
     * Gets the limit for this node.
     *
     * @return int|null
     */
    public function getLimit()
    {
        return $this->getModifier(static::PARAM_LIMIT);
    }

    /**
     * Set the fields for this node.
     *
     * @param mixed $fields
     *
     * @return GraphNode
     */
    public function fields($fields)
    {
        if (!is_array($fields)) {
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
        $this->compiledValues = [];
    }

    /**
     * Compile the modifier values.
     */
    public function compileModifiers()
    {
        if (count($this->modifiers) === 0) {
            return;
        }

        $this->compiledValues[] = http_build_query($this->modifiers, '', '&');
    }

    /**
     * Compile the field values.
     */
    public function compileFields()
    {
        if (count($this->fields) === 0) {
            return;
        }

        $this->compiledValues[] = static::PARAM_FIELDS.'='.implode(',', $this->fields);
    }

    /**
     * Compile the the full URL.
     *
     * @return string
     */
    public function compileUrl()
    {
        $append = '';
        if (count($this->compiledValues) > 0) {
            $append = '?'.implode('&', $this->compiledValues);
        }

        return '/'.$this->name.$append;
    }

    /**
     * Compile the final URL as a string.
     *
     * @param string|null $appSecret The app secret for signing the URL with app secret proof.
     *
     * @return string
     */
    public function asUrl($appSecret = null)
    {
        $this->resetCompiledValues();

        if ($appSecret) {
            $this->addAppSecretProofModifier($appSecret);
        }

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

    /**
     * Generate an app secret proof modifier based on the app secret & access token.
     *
     * @param string $appSecret
     */
    private function addAppSecretProofModifier($appSecret)
    {
        $accessToken = $this->getModifier(static::PARAM_ACCESS_TOKEN);
        if (!$accessToken) {
            return;
        }

        $this->modifiers([
          static::PARAM_APP_SECRET_PROOF => hash_hmac('sha256', $accessToken, $appSecret),
        ]);
    }
}
