<?php namespace SammyK\FacebookQueryBuilder;

/*
 * Because mocking the FacebookRequest is a bitch.
 * Hopefully this class will die in the future with a refactor of Facebook\FacebookRequest.
 */

use Facebook\FacebookRequest;
use Facebook\FacebookSession;

class FacebookRequestMaker
{
    /**
     * Make a FacebookRequest object
     *
     * @param \Facebook\FacebookSession $session
     * @param string $method
     * @param string $path
     * @param array|null $parameters
     * @param string $version
     *
     * @return \Facebook\FacebookRequest
     */
    public function make(FacebookSession $session, $method, $path, $parameters = null, $version = null)
    {
        return new FacebookRequest($session, $method, $path, $parameters, $version);
    }
}
