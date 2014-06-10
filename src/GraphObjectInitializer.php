<?php namespace SammyK\FacebookQueryBuilder;

class GraphObjectInitializer
{
    /**
     * Iterate response data recursively and cast to Graph value objects.
     *
     * @param array $data
     *
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
            if (static::isCastableAsCollection($data['data']))
            {
                return new GraphCollection($data);
            }
            return new GraphObject($data['data']);
        }

        return new GraphObject($data);
    }

    /**
     * Determines whether or not the data should be cast as a GraphCollection.
     *
     * @param array $data
     *
     * @return boolean
     */
    public static function isCastableAsCollection(array $data)
    {
        // Checks for a sequential numeric array which would be a GraphCollection
        return array_keys($data) === range(0, count($data) - 1);
    }

}
