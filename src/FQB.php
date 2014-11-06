<?php namespace SammyK\FacebookQueryBuilder;

use Facebook\Facebook;

class FQB extends Facebook
{
    /**
     * The GraphNode we are working with.
     *
     * @var \SammyK\FacebookQueryBuilder\GraphNode
     */
    protected $graph_node;

    /**
     * The access token associated with this request.
     *
     * @var \Facebook\Entities\AccessToken|string|null
     */
    protected $fqb_access_token;

    /**
     * The etag associated with this node.
     *
     * @var string|null
     */
    protected $fqb_etag;

    /**
     * Data that will be sent in the body of a POST or DELETE request
     * and in the URL query params for GET requests.
     *
     * @var array
     */
    protected $modifiers = [];

    /**
     * Remembers the last config sent to the constructor.
     *
     * @var array
     */
    protected $fqb_config = [];

    /**
     * New up a new GraphNode instance.
     *
     * @param array $config The configuration
     */
    public function __construct(array $config = [])
    {
        if (isset($config['fqb:graph_node_name']))
        {
            $this->graph_node = new GraphNode($config['fqb:graph_node_name']);
            unset($config['fqb:graph_node_name']);
        }

        $this->fqb_config = $config;

        parent::__construct($config);
    }

    /**
     * Send GET request to Graph.
     * The arguments are there to keep notices from showing up in strict mode.
     *
     * @param string $endpoint
     * @param \Facebook\Entities\AccessToken|string|null $accessToken
     * @param string|null $eTag
     * @param string|null $graphVersion
     *
     * @return \Facebook\Entities\FacebookResponse
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function get($endpoint = null, $accessToken = null, $eTag = null, $graphVersion = null)
    {
        $this->prepareGraphNodeForGetRequest();
        $url = $this->asUrl();
        return parent::get($url, $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Prepare the root edge for a GET request.
     */
    public function prepareGraphNodeForGetRequest()
    {
        if (count($this->modifiers) > 0)
        {
            $this->graph_node->with($this->modifiers);
        }
    }

    /**
     * Send POST request to Graph.
     * The arguments are there to keep notices from showing up in strict mode.
     *
     * @param string $endpoint
     * @param array $params
     * @param \Facebook\Entities\AccessToken|string|null $accessToken
     * @param string|null $eTag
     * @param string|null $graphVersion
     *
     * @return \Facebook\Entities\FacebookResponse
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function post($endpoint = null, array $params = [], $accessToken = null, $eTag = null, $graphVersion = null)
    {
        $url = $this->asUrl();
        return parent::post($url, $this->modifiers, $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Send DELETE request to Graph.
     * The arguments are there to keep notices from showing up in strict mode.
     *
     * @param string $endpoint
     * @param \Facebook\Entities\AccessToken|string|null $accessToken
     * @param string|null $eTag
     * @param string|null $graphVersion
     *
     * @return \Facebook\Entities\FacebookResponse
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function delete($endpoint = null, $accessToken = null, $eTag = null, $graphVersion = null)
    {
        $url = $this->asUrl();
        return parent::delete($url, $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Convenience method for searching Graph.
     *
     * @param string $search
     * @param string $type
     *
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function search($search, $type = null)
    {
        $fqb = $this->node('search')->with(['q' => $search]);

        if ($type)
        {
            $fqb->with(['type' => $type]);
        }

        return $fqb;
    }

    /**
     * Alias to method on GraphNode.
     *
     * @param int $limit
     *
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function limit($limit)
    {
        $this->graph_node->limit($limit);

        return $this;
    }

    /**
     * Alias to method on GraphNode.
     *
     * @param array|string $fields The fields we want on the root edge
     *
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function fields($fields)
    {
        if ( ! is_array($fields))
        {
            $fields = func_get_args();
        }

        $this->graph_node->fields($fields);

        return $this;
    }

    /**
     * Sets the etag.
     *
     * @param string $etag The eTag.
     *
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function etag($etag)
    {
        $this->fqb_etag = $etag;

        return $this;
    }

    /**
     * Sets the access token.
     *
     * @param \Facebook\Entities\AccessToken|string $access_token The access token to overwrite the default.
     *
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function accessToken($access_token)
    {
        $this->fqb_access_token = $access_token;

        return $this;
    }

    /**
     * Alias of modifiers().
     *
     * @param array $data
     *
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function with(array $data)
    {
        return $this->modifiers($data);
    }

    /**
     * Data that will be sent in the body of a POST or DELETE request
     * and in the URL query params for GET requests.
     *
     * @param array $data
     *
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function modifiers(array $data)
    {
        $this->modifiers = array_merge($this->modifiers, $data);

        return $this;
    }

    /**
     * New up an Edge instance.
     *
     * @param array $fields The fields we want on the edge
     * @param string $edge_name
     *
     * @return \SammyK\FacebookQueryBuilder\GraphEdge
     */
    public function edge($edge_name, array $fields = [])
    {
        return new GraphEdge($edge_name, $fields);
    }

    /**
     * New up an instance of self.
     *
     * @param string $graph_node_name The node name
     *
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function node($graph_node_name)
    {
        $this->fqb_config['fqb:graph_node_name'] = $graph_node_name;
        return new static($this->fqb_config);
    }

    /**
     * Return the GraphNode as a URL string.
     *
     * @return string
     */
    public function asUrl()
    {
        return $this->graph_node->asUrl();
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
}
