<?php namespace SammyK\FacebookQueryBuilder;

class GraphCollection extends BaseGraphObject
{
    /**
     * Init this Graph object
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if ( ! isset($data['data'])) return;

        // @TODO: Look for meta data here

        parent::__construct($data['data']);
    }

    /**
     * Get the next page of results for this object
     *
     * @TODO
     *
     * @return \SammyK\FacebookQueryBuilder\Response
     */
    public function next()
    {
    }

    /**
     * Get the previous page of results for this object
     *
     * @TODO
     *
     * @return \SammyK\FacebookQueryBuilder\Response
     */
    public function previous()
    {
    }

}
