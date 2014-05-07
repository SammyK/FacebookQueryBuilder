<?php namespace SammyK\FacebookQueryBuilder;

use Carbon\Carbon;

class GraphObject extends Collection
{
    /**
     * Date fields that should be cast as Carbon objects
     *
     * @var array
     */
    protected $cast_to_carbon = ['created_time', 'updated_time'];

    /**
     * Init this Graph object
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $items = [];

        foreach ($data as $k => $v)
        {
            if (isset($v['data']))
            {
                $items[$k] = new GraphCollection($v);
            }
            elseif (is_array($v))
            {
                $items[$k] = new GraphObject($v);
            }
            elseif (in_array($k, $this->cast_to_carbon, true))
            {
                $items[$k] = new Carbon($v);
            }
            else
            {
                $items[$k] = $v;
            }
        }

        parent::__construct($items);
    }
}
