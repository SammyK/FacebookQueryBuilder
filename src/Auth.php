<?php namespace SammyK\FacebookQueryBuilder;

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookCanvasLoginHelper;
use Facebook\FacebookJavaScriptLoginHelper;
use Facebook\FacebookSDKException;

class Auth
{
    /**
     * The Facebook redirect helper object.
     *
     * @var \Facebook\FacebookRedirectLoginHelper
     */
    protected static $redirect_helper;

    /**
     * The class name of the setRedirectHelperAlias
     *
     * @var \Facebook\FacebookRedirectLoginHelper
     */
    protected static $redirect_helper_alias = '\Facebook\FacebookRedirectLoginHelper';

    /**
     * The Facebook redirect helper object.
     *
     * @var \Facebook\FacebookCanvasLoginHelper
     */
    protected static $canvas_helper;

    /**
     * The Facebook redirect helper object.
     *
     * @var \Facebook\FacebookJavaScriptLoginHelper
     */
    protected static $javascript_helper;

    /**
     * Get the Facebook redirect helper object.
     *
     * @param string $redirect_url
     *
     * @return \Facebook\FacebookRedirectLoginHelper
     */
    public static function getRedirectHelper($redirect_url)
    {
        if (isset(static::$redirect_helper)) return static::$redirect_helper;

        return static::$redirect_helper = new static::$redirect_helper_alias($redirect_url);
    }

    /**
     * Set the Facebook redirect helper object.
     *
     * @param \Facebook\FacebookRedirectLoginHelper $redirect_helper
     */
    public static function setRedirectHelper(FacebookRedirectLoginHelper $redirect_helper)
    {
        static::$redirect_helper = $redirect_helper;
    }

    /**
     * The name of a custom class that extends the \Facebook\FacebookRedirectLoginHelper
     *
     * @param string $redirect_helper_alias
     */
    public static function setRedirectHelperAlias($redirect_helper_alias)
    {
        static::$redirect_helper_alias = $redirect_helper_alias;
    }

    /**
     * Get the Facebook canvas helper object.
     *
     * @return \Facebook\FacebookCanvasLoginHelper
     */
    public static function getCanvasHelper()
    {
        if (isset(static::$canvas_helper)) return static::$canvas_helper;

        return static::$canvas_helper = new FacebookCanvasLoginHelper();
    }

    /**
     * Set the Facebook canvas helper object.
     *
     * @param \Facebook\FacebookCanvasLoginHelper $canvas_helper
     */
    public static function setCanvasHelper(FacebookCanvasLoginHelper $canvas_helper)
    {
        static::$canvas_helper = $canvas_helper;
    }

    /**
     * Get the Facebook javascript helper object.
     *
     * @return \Facebook\FacebookJavaScriptLoginHelper
     */
    public static function getJavascriptHelper()
    {
        if (isset(static::$javascript_helper)) return static::$javascript_helper;

        return static::$javascript_helper = new FacebookJavaScriptLoginHelper();
    }

    /**
     * Set the Facebook javascript helper object.
     *
     * @param \Facebook\FacebookJavaScriptLoginHelper $javascript_helper
     */
    public static function setJavascriptHelper(FacebookJavaScriptLoginHelper $javascript_helper)
    {
        static::$javascript_helper = $javascript_helper;
    }

    /**
     * Gets a login URL.
     *
     * @param string $redirect_url
     * @param array $scope
     *
     * @return string
     */
    public function getLoginUrl($redirect_url, array $scope = [])
    {
        return static::getRedirectHelper($redirect_url)->getLoginUrl($scope);
    }

    /**
     * Gets an access token from a redirect.
     *
     * @param string $redirect_url
     *
     * @return AccessToken|null
     *
     * @throws FacebookQueryBuilderException
     */
    public function getTokenFromRedirect($redirect_url)
    {
        try
        {
            $session = static::getRedirectHelper($redirect_url)->getSessionFromRedirect();
        }
        catch (FacebookSDKException $e)
        {
            throw new FacebookQueryBuilderException('Unable to obtain access token from redirect', 100, $e);
        }

        return $session ? new AccessToken($session->getToken()) : null;
    }

    /**
     * Gets an access token from a canvas context.
     *
     * @return AccessToken|null
     *
     * @throws FacebookQueryBuilderException
     */
    public function getTokenFromCanvas()
    {
        try
        {
            $session = static::getCanvasHelper()->getSession();
        }
        catch (FacebookSDKException $e)
        {
            throw new FacebookQueryBuilderException('Unable to obtain access token from canvas', 100, $e);
        }

        return $session ? new AccessToken($session->getToken()) : null;
    }

    /**
     * Gets an access token from the Javascript SDK.
     *
     * @return AccessToken|null
     *
     * @throws FacebookQueryBuilderException
     */
    public function getTokenFromJavascript()
    {
        try
        {
            $session = static::getJavascriptHelper()->getSession();
        }
        catch (FacebookSDKException $e)
        {
            throw new FacebookQueryBuilderException('Unable to obtain access token from Javascript', 100, $e);
        }

        return $session ? new AccessToken($session->getToken()) : null;
    }

}
