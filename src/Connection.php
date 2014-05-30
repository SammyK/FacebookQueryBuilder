<?php namespace SammyK\FacebookQueryBuilder;

use Facebook\FacebookSession;
use Facebook\FacebookRequestException;
use SammyK\FacebookQueryBuilder\RootEdge;
use SammyK\FacebookQueryBuilder\Response;

class Connection
{
    /**
     * The Facebook request maker.
     *
     * @var \SammyK\FacebookQueryBuilder\FacebookRequestMaker
     */
    protected $facebook_request;

    /**
     * Response value object
     *
     * @var \SammyK\FacebookQueryBuilder\Response
     */
    protected $response;

    /**
     * The Facebook SDK session object.
     *
     * @var \Facebook\FacebookSession
     */
    protected static $facebook_session;

    /**
     * Facebook app ID.
     *
     * @var int
     */
    protected static $app_id;

    /**
     * Facebook app secret.
     *
     * @var string
     */
    protected static $app_secret;

    /**
     * Access token to use for API calls.
     *
     * @var string
     */
    protected static $access_token;

    /**
     * Create a new Facebook connection instance.
     *
     * @param \SammyK\FacebookQueryBuilder\FacebookRequestMaker $facebook_request
     * @param \SammyK\FacebookQueryBuilder\Response $response
     */
    public function __construct(FacebookRequestMaker $facebook_request, Response $response)
    {
        $this->facebook_request = $facebook_request;
        $this->response = $response;
    }

    /**
     * Sets the app credentials.
     *
     * @param int $app_id
     * @param string $app_secret
     */
    public static function setAppCredentials($app_id, $app_secret)
    {
        static::$app_id = $app_id;
        static::$app_secret = $app_secret;

        FacebookSession::setDefaultApplication(static::$app_id, static::$app_secret);
    }

    /**
     * Sets the access token to be used for all API requests.
     *
     * @param string $access_token
     */
    public static function setAccessToken($access_token)
    {
        static::$access_token = $access_token;
        static::$facebook_session = null; // Reset the FacebookSession
    }

    /**
     * Get the existing FacebookSession object or new up one
     *
     * @return \Facebook\FacebookSession
     */
    public static function getFacebookSession()
    {
        if (isset(static::$facebook_session)) return static::$facebook_session;

        return static::$facebook_session = new FacebookSession(static::$access_token);
    }

    /**
     * Set the FacebookSession object
     *
     * @param \Facebook\FacebookSession $facebook_session
     */
    public static function setFacebookSession(FacebookSession $facebook_session)
    {
        static::$facebook_session = $facebook_session;
    }

    /**
     * Send GET request to Facebook API.
     *
     * @param \SammyK\FacebookQueryBuilder\RootEdge $root_edge
     * @return \SammyK\FacebookQueryBuilder\Response
     */
    public function get(RootEdge $root_edge)
    {
        return $this->send((string) $root_edge);
    }

    /**
     * Send POST request to Facebook API.
     *
     * @param \SammyK\FacebookQueryBuilder\RootEdge $root_edge
     * @param array $data
     * @return \SammyK\FacebookQueryBuilder\Response
     */
    public function post(RootEdge $root_edge, $data)
    {
        return $this->send((string) $root_edge, 'POST', $data);
    }

    /**
     * Send DELETE request to Facebook API.
     *
     * @param \SammyK\FacebookQueryBuilder\RootEdge $root_edge
     * @return \SammyK\FacebookQueryBuilder\Response
     */
    public function delete(RootEdge $root_edge)
    {
        return $this->send((string) $root_edge, 'DELETE');
    }

    /**
     * Send a request to the Facebook Graph API.
     *
     * @throws \SammyK\FacebookQueryBuilder\FacebookQueryBuilderException
     * @param string $path
     * @param string $method
     * @param array $params
     * @return \Facebook\FacebookResponse
     */
    public function send($path, $method = 'GET', array $params = [])
    {
        try
        {
            $request = $this->facebook_request->make(static::getFacebookSession(), $method, $path, $params);
            $response = $request->execute();

            return $this->response->create($response);
        }
        catch (FacebookRequestException $e)
        {
            throw new FacebookQueryBuilderException($e);
        }
    }
}
