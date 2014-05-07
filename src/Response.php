<?php namespace SammyK\FacebookQueryBuilder;

use Facebook\FacebookResponse;

class Response extends Collection
{
    /**
     * The raw response from the Facebook SDK.
     *
     * @var array
     */
    public $raw_response;

    /**
     * The parsed response from the Facebook SDK.
     *
     * @var \SammyK\FacebookQueryBuilder\Collection
     */
    public $response;

    /**
     * Iterate response data recursively and cast to Graph value objects.
     *
     * @param \Facebook\FacebookResponse|array|null $raw_response
     */
    public function __construct($raw_response = null)
    {
        if ( ! isset($raw_response)) return;

        if ($raw_response instanceof FacebookResponse)
        {
            $raw_response = json_decode($raw_response->getRawResponse(), true);
        }

        $this->raw_response = ! is_array($raw_response) ? [$raw_response] : $raw_response;

        $this->response = static::castGraphObjects($this->raw_response);
    }

    /**
     * Return the raw response from the Facebook SDK.
     *
     * @return array
     */
    public function getRawResponse()
    {
        return $this->raw_response;
    }

    /**
     * The parsed response from the Facebook SDK.
     *
     * @return \SammyK\FacebookQueryBuilder\Collection
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * New up an instance of self with the response data.
     *
     * @param \Facebook\FacebookResponse|array $response
     * @return \SammyK\FacebookQueryBuilder\Response
     */
    public function create($response)
    {
        return new static($response);
    }

    /**
     * Iterate response data recursively and cast to Graph value objects.
     *
     * @param array $data
     * @return \SammyK\FacebookQueryBuilder\Collection
     */
    public static function castGraphObjects(array $data)
    {
        if (isset($data['error']))
        {
            return new GraphError($data['error']);
        }
        elseif (isset($data['data']))
        {
            //$collection = $this->castGraphObjects($data['data']);
            return new GraphCollection($data);
        }

        return new GraphObject($data);
    }
}
