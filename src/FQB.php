<?php namespace SammyK\FacebookQueryBuilder;

class FQB
{
    /**
     * Production Graph API URL.
     *
     * @const string
     */
    const BASE_GRAPH_URL = 'https://graph.facebook.com';

    /**
     * Beta tier URL of the Graph API.
     *
     * @const string
     */
    const BASE_GRAPH_URL_BETA = 'https://graph.beta.facebook.com';

    /**
     * The GraphNode we are working with.
     *
     * @var GraphNode
     */
    private $graphNode;

    /**
     * The URL prefix version of the Graph API.
     *
     * @var string|null
     */
    private $graphVersion;

    /**
     * The application secret key.
     *
     * @var string|null
     */
    private $appSecret;

    /**
     * A toggle to enable the beta tier of the Graph API.
     *
     * @var boolean
     */
    private $enableBetaMode = false;

    /**
     * The config options sent in from the user.
     *
     * @var array
     */
    private $config = [];

    /**
     * @param array $config An array of config options.
     * @param string|null $graphEndpoint The name of the Graph API endpoint.
     */
    public function __construct(array $config = [], $graphEndpoint = '')
    {
        if (isset($graphEndpoint)) {
            $this->graphNode = new GraphNode($graphEndpoint);
        }

        $this->config = $config;

        if (isset($config['default_access_token'])) {
            $this->accessToken($config['default_access_token']);
        }

        if (isset($config['default_graph_version'])) {
            $this->graphVersion($config['default_graph_version']);
        }

        if (isset($config['app_secret'])) {
            $this->appSecret = $config['app_secret'];
        }

        if (isset($config['enable_beta_mode']) && $config['enable_beta_mode'] === true) {
            $this->enableBetaMode = true;
        }
    }

    /**
     * New up an instance of self.
     *
     * @param string $graph_node_name The node name
     *
     * @return FQB
     */
    public function node($graph_node_name)
    {
        return new static($this->config, $graph_node_name);
    }

    /**
     * New up an Edge instance.
     *
     * @param string $edgeName
     * @param array  $fields The fields we want on the edge
     *
     * @return GraphEdge
     */
    public function edge($edgeName, array $fields = [])
    {
        return new GraphEdge($edgeName, $fields);
    }

    /**
     * Alias to method on GraphNode.
     *
     * @param array|string $fields
     *
     * @return FQB
     */
    public function fields($fields)
    {
        if (!is_array($fields)) {
            $fields = func_get_args();
        }

        $this->graphNode->fields($fields);

        return $this;
    }

    /**
     * Sets the access token to use with this request.
     *
     * @param string $accessToken The access token to overwrite the default.
     *
     * @return FQB
     */
    public function accessToken($accessToken)
    {
        $this->graphNode->modifiers([
          GraphNode::PARAM_ACCESS_TOKEN => $accessToken,
        ]);

        return $this;
    }

    /**
     * Sets the graph version to use with this request.
     *
     * @param string $graphVersion The access token to overwrite the default.
     *
     * @return FQB
     */
    public function graphVersion($graphVersion)
    {
        $this->graphVersion = $graphVersion;

        return $this;
    }

    /**
     * Alias to method on GraphNode.
     *
     * @param int $limit
     *
     * @return FQB
     */
    public function limit($limit)
    {
        $this->graphNode->limit($limit);

        return $this;
    }

    /**
     * Alias to method on GraphNode.
     *
     * @param array $data
     *
     * @return FQB
     */
    public function modifiers(array $data)
    {
        $this->graphNode->modifiers($data);

        return $this;
    }

    /**
     * Return the generated request as a URL with the hostname.
     *
     * @return string
     */
    public function asUrl()
    {
        return $this->getHostname().$this->asEndpoint();
    }

    /**
     * Return the generated request as a URL endpoint sans the hostname.
     *
     * @return string
     */
    public function asEndpoint()
    {
        $graphVersionPrefix = '';
        if ($this->graphVersion) {
            $graphVersionPrefix = '/'.$this->graphVersion;
        }

        return $graphVersionPrefix.$this->graphNode->asUrl($this->appSecret);
    }

    /**
     * Returns the Graph URL as nicely formatted string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->asUrl();
    }

    /**
     * Returns the Graph API hostname.
     *
     * @return string
     */
    private function getHostname()
    {
        if ($this->enableBetaMode === true) {
            return static::BASE_GRAPH_URL_BETA;
        }

        return static::BASE_GRAPH_URL;
    }
}
