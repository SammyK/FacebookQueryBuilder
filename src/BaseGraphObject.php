<?php namespace SammyK\FacebookQueryBuilder;

use Carbon\Carbon;

class BaseGraphObject extends Collection
{
    /**
     * Date fields that should be cast as Carbon objects
     *
     * @var array
     */
    protected static $cast_to_carbon = [
        'created_time',
        'updated_time',
        'start_time',
        'end_time',
        'backdated_time',
        'issued_at',
        'expires_at',
    ];

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
                if (GraphObjectInitializer::isCastableAsCollection($v['data']))
                {
                    $items[$k] = new GraphCollection($v);
                }
                $items[$k] = new GraphObject($v['data']);
            }
            elseif (is_array($v))
            {
                $items[$k] = new GraphObject($v);
            }
            elseif (in_array($k, static::$cast_to_carbon, true))
            {
                $items[$k] = is_int($v) ? Carbon::createFromTimeStamp($v) : new Carbon($v);
            }
            else
            {
                $items[$k] = $v;
            }
        }

        parent::__construct($items);
    }

}
