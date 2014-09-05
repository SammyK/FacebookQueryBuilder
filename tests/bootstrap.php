<?php
require __DIR__ . '/../vendor/autoload.php';

if ( ! ini_get('date.timezone'))
{
    date_default_timezone_set('UTC');
}
