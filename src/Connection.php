<?php namespace SammyK\FacebookQueryBuilder;

use Facebook\FacebookSession;
use Facebook\FacebookSDKException;
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
     * Response value object.
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
     * The Facebook SDK session object using an app access token.
     *
     * @var \Facebook\FacebookSession
     */
    protected static $facebook_app_session;

    /**
     * Facebook app ID.
     *
     * @var int
     */
    public static $app_id;

    /**
     * Facebook app secret.
     *
     * @var string
     */
    public static $app_secret;

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
     * @param AccessToken|string $access_token
     */
    public static function setAccessToken($access_token)
    {
        static::$access_token = (string) $access_token;
        static::$facebook_session = null; // Reset the FacebookSession
    }

    /**
     * Get the existing FacebookSession object or new up one.
     *
     * @return \Facebook\FacebookSession
     */
    public static function getFacebookSession()
    {
        if (isset(static::$facebook_session)) return static::$facebook_session;

        return static::$facebook_session = new FacebookSession(static::$access_token);
    }

    /**
     * Set the FacebookSession object.
     *
     * @param \Facebook\FacebookSession $facebook_session
     */
    public static function setFacebookSession(FacebookSession $facebook_session)
    {
        static::$facebook_session = $facebook_session;
    }

    /**
     * Get the existing FacebookSession object using an app access token or new up one.
     *
     * @return \Facebook\FacebookSession
     */
    public static function getFacebookAppSession()
    {
        if (isset(static::$facebook_app_session)) return static::$facebook_app_session;

        return static::$facebook_app_session = new FacebookSession(static::$app_id . '|' . static::$app_secret);
    }

    /**
     * Set the FacebookSession object using an app access token.
     *
     * @param \Facebook\FacebookSession $facebook_app_session
     */
    public static function setFacebookAppSession(FacebookSession $facebook_app_session)
    {
        static::$facebook_app_session = $facebook_app_session;
    }

    /**
     * Send GET request to Facebook API.
     *
     * @param \SammyK\FacebookQueryBuilder\RootEdge $root_edge
     *
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
     *
     * @return \SammyK\FacebookQueryBuilder\Response
     */
    public function post(RootEdge $root_edge, $data = [])
    {
        return $this->send((string) $root_edge, 'POST', $data);
    }

    /**
     * Send DELETE request to Facebook API.
     *
     * @param \SammyK\FacebookQueryBuilder\RootEdge $root_edge
     * @param array $data
     *
     * @return \SammyK\FacebookQueryBuilder\Response
     */
    public function delete(RootEdge $root_edge, $data = [])
    {
        return $this->send((string) $root_edge, 'DELETE', $data);
    }

    /**
     * Send a request to the Facebook Graph API.
     *
     * @param string $path
     * @param string $method
     * @param array $params
     * @param boolean $app_request
     *
     * @return \Facebook\FacebookResponse
     *
     * @throws FacebookQueryBuilderException
     */
    public function send($path, $method = 'GET', array $params = [], $app_request = false)
    {
        try
        {
            $session = $app_request ? static::getFacebookAppSession() : static::getFacebookSession();

            $request = $this->facebook_request->make($session, $method, $path, $params);
            $response = $request->execute();

            return $this->response->create($response);
        }
        catch (FacebookSDKException $e)
        {
            throw new FacebookQueryBuilderException($e);
        }
    }
}
