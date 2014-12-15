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
     * @var \Facebook\AccessToken|string|null
     */
    protected $fqb_access_token;

    /**
     * The etag associated with this node.
     *
     * @var string|null
     */
    protected $fqb_etag;

    /**
     * Data that will be sent in the body of a POST request.
     *
     * @var array
     */
    protected $post_data = [];

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
     * @param \Facebook\AccessToken|string|null $accessToken
     * @param string|null $eTag
     * @param string|null $graphVersion
     *
     * @return \Facebook\FacebookResponse
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function get($endpoint = null, $accessToken = null, $eTag = null, $graphVersion = null)
    {
        $url = $this->asUrl();

        return parent::get($url, $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Send POST request to Graph.
     * The arguments are there to keep notices from showing up in strict mode.
     *
     * @param string $endpoint
     * @param array $params
     * @param \Facebook\AccessToken|string|null $accessToken
     * @param string|null $eTag
     * @param string|null $graphVersion
     *
     * @return \Facebook\FacebookResponse
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function post($endpoint = null, array $params = [], $accessToken = null, $eTag = null, $graphVersion = null)
    {
        $url = $this->asUrl();

        return parent::post($url, $this->post_data, $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Send DELETE request to Graph.
     * The arguments are there to keep notices from showing up in strict mode.
     *
     * @param string $endpoint
     * @param \Facebook\AccessToken|string|null $accessToken
     * @param string|null $eTag
     * @param string|null $graphVersion
     *
     * @return \Facebook\FacebookResponse
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function delete($endpoint = null, $accessToken = null, $eTag = null, $graphVersion = null)
    {
        $url = $this->asUrl();

        return parent::delete($url, $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Make a FacebookRequest from a GET request.
     *
     * @return \Facebook\FacebookRequest
     */
    public function asGetRequest()
    {
        $url = $this->asUrl();

        return parent::request('GET', $url, [], $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Make a FacebookRequest from a POST request.
     *
     * @return \Facebook\FacebookRequest
     */
    public function asPostRequest()
    {
        $url = $this->asUrl();

        return parent::request('POST', $url, $this->post_data, $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Make a FacebookRequest from a DELETE request.
     *
     * @return \Facebook\FacebookRequest
     */
    public function asDeleteRequest()
    {
        $url = $this->asUrl();

        return parent::request('DELETE', $url, [], $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Sends a batched request to Graph and returns the result.
     *
     * @param array $requests
     * @param \Facebook\AccessToken|string|null $accessToken
     * @param string|null $graphVersion
     *
     * @return \Facebook\FacebookBatchResponse
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function sendBatchRequest(
        array $requests,
        $accessToken = null,
        $graphVersion = null)
    {
        return parent::sendBatchRequest($requests, $this->fqb_access_token);
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
        $fqb = $this->node('search')->modifiers(['q' => $search]);

        if ($type)
        {
            $fqb->modifiers(['type' => $type]);
        }

        return $fqb;
    }

    /**
     * Alias to method on GraphNode.
     *
     * @param array $data
     *
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function modifiers(array $data)
    {
        $this->graph_node->modifiers($data);

        return $this;
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
     * @param array|string $fields
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
     * @param \Facebook\AccessToken|string $access_token The access token to overwrite the default.
     *
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function accessToken($access_token)
    {
        $this->fqb_access_token = $access_token;

        return $this;
    }

    /**
     * Sets an array of post data to send with the request.
     *
     * @param array $data
     *
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function withPostData(array $data)
    {
        $this->post_data = array_merge($this->post_data, $data);

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
