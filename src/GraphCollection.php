<?php namespace SammyK\FacebookQueryBuilder;

class GraphCollection extends Collection
{
    /**
     * Init this Graph collection as a collection
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if ( ! isset($data['data'])) return;

        $collection = [];

        foreach ($data['data'] as $graph_object)
        {
            $collection[] = new GraphObject($graph_object);
        }

        parent::__construct($collection);
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
