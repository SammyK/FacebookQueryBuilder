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
            // If JSON returned
            $decoded_response = json_decode($raw_response->getRawResponse(), true);

            // If kay/value pairs returned
            if ($decoded_response === null)
            {
                parse_str($raw_response->getRawResponse(), $decoded_response);
            }
        }
        else
        {
            $decoded_response = $raw_response;
        }

        $this->raw_response = ! is_array($decoded_response) ? [$decoded_response] : $decoded_response;

        $this->response = GraphObjectInitializer::castGraphObjects($this->raw_response);
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

}
