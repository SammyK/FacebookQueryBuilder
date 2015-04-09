<?php namespace SammyK\FacebookQueryBuilder;

use Facebook\Facebook;

class FQB
{
    /**
     * The Facebook PHP SDK v4.1 super service class.
     *
     * @var Facebook
     */
    private $fb;

    /**
     * The GraphNode we are working with.
     *
     * @var \SammyK\FacebookQueryBuilder\GraphNode
     */
    private $graph_node;

    /**
     * The access token associated with this request.
     *
     * @var \Facebook\Authentication\AccessToken|string|null
     */
    private $fqb_access_token;

    /**
     * The etag associated with this node.
     *
     * @var string|null
     */
    private $fqb_etag;

    /**
     * Data that will be sent in the body of a POST request.
     *
     * @var array
     */
    private $post_data = [];

    /**
     * New up a new GraphNode instance.
     *
     * @param Facebook $fb The Facebook super service class.
     * @param string|null $node The node we want to work with.
     */
    public function __construct(Facebook $fb, $node = null)
    {
        $this->fb = $fb;

        if (isset($node)) {
            $this->graph_node = new GraphNode($node);
        }
    }

    /**
     * Send GET request to Graph.
     * The arguments are there to keep notices from showing up in strict mode.
     *
     * @return \Facebook\FacebookResponse
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function get()
    {
        $url = $this->asUrl();

        return $this->fb->get($url, $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Send POST request to Graph.
     * The arguments are there to keep notices from showing up in strict mode.
     *
     * @return \Facebook\FacebookResponse
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function post()
    {
        $url = $this->asUrl();

        return $this->fb->post($url, $this->post_data, $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Send DELETE request to Graph.
     * The arguments are there to keep notices from showing up in strict mode.
     *
     * @return \Facebook\FacebookResponse
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function delete()
    {
        $url = $this->asUrl();

        return $this->fb->delete($url, $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Make a FacebookRequest from a GET request.
     *
     * @return \Facebook\FacebookRequest
     */
    public function asGetRequest()
    {
        $url = $this->asUrl();

        return $this->fb->request('GET', $url, [], $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Make a FacebookRequest from a POST request.
     *
     * @return \Facebook\FacebookRequest
     */
    public function asPostRequest()
    {
        $url = $this->asUrl();

        return $this->fb->request('POST', $url, $this->post_data, $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Make a FacebookRequest from a DELETE request.
     *
     * @return \Facebook\FacebookRequest
     */
    public function asDeleteRequest()
    {
        $url = $this->asUrl();

        return $this->fb->request('DELETE', $url, [], $this->fqb_access_token, $this->fqb_etag);
    }

    /**
     * Sends a batched request to Graph and returns the result.
     *
     * @param array $requests
     *
     * @return \Facebook\FacebookBatchResponse
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function sendBatchRequest(array $requests)
    {
        return $this->fb->sendBatchRequest($requests, $this->fqb_access_token);
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

        if ($type) {
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
        if (! is_array($fields)) {
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
     * @param \Facebook\Authentication\AccessToken|string $access_token The access token to overwrite the default.
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
     * @param string $edge_name
     * @param array $fields The fields we want on the edge
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
        return new static($this->fb, $graph_node_name);
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
