<?php namespace SammyK\FacebookQueryBuilder;

use Carbon\Carbon;
use Facebook\FacebookSDKException;

class AccessToken
{
    /**
     * The access token.
     *
     * @var string
     */
    public $access_token;

    /**
     * Date when token expires.
     *
     * @var \Carbon\Carbon
     */
    public $expires_at;

    /**
     * Create a new access token entity.
     *
     * @param string $access_token
     * @param int $expires_at
     */
    public function __construct($access_token, $expires = 0)
    {
        $this->access_token = $access_token;
        // if $expires is 0 then call facebook to get expiration time of token
        if($expires == 0)
        {
            $this->getInfo();
        }
        else
        {
            $this->setExpiresAtFromTimeStamp($expires);
        }
    }

    /**
     * Setter for expires_at.
     *
     * @param int $expires
     */
    public function setExpiresAtFromTimeStamp($expires)
    {   
        $time_stamp = time() + $expires;
        $this->expires_at = Carbon::createFromTimeStamp($time_stamp);
    }

    /**
     * Getter for expires_at.
     *
     * @return \Carbon\Carbon
     */
    public function expiresAt()
    {
        return $this->expires_at;
    }

    /**
     * Determines whether or not this is a long-lived token.
     *
     * @return bool
     */
    public function isLongLived()
    {
        return $this->expires_at->diffInHours(null, false) < -2;
    }

    /**
     * Exchanges a short lived access token with a long lived access token.
     *
     * @return AccessToken
     *
     * @throws FacebookQueryBuilderException
     */
    public function extend()
    {
        try
        {
            $params = [
                'client_id' => Connection::$app_id,
                'client_secret' => Connection::$app_secret,
                'grant_type' => 'fb_exchange_token',
                'fb_exchange_token' => $this->access_token,
            ];

            $response = FQB::getConnection()->send('/oauth/access_token', 'GET', $params, $app_request = true);
        }
        catch (FacebookSDKException $e)
        {
            throw new FacebookQueryBuilderException('Unable to extend access token', 101, $e);
        }

        $data = $response->getResponse();

        $access_token = isset($data['access_token']) ? $data['access_token'] : null;
        /**
         * If facebook does not send `expires` in response that means token never expires. 
         * So set its age to a year from now.
         */
        $expires = isset($data['expires']) ? $data['expires'] : 31536000;
        
        return new static($access_token, $expires);
    }

    /**
     * Get more info about an access token.
     *
     * @return Response
     *
     * @throws FacebookQueryBuilderException
     */
    public function getInfo()
    {
        try
        {
            $params = ['input_token' => $this->access_token];
            $response = FQB::getConnection()->send('/debug_token', 'GET', $params, $app_request = true);
        }
        catch (FacebookSDKException $e)
        {
            throw new FacebookQueryBuilderException('Unable to get access token info', 102, $e);
        }

        $data = $response->getResponse();

        // Update the data on this token
        if (isset($data['expires_at']))
        {
            $this->expires_at = $data['expires_at'];
        }

        return $data;
    }

    /**
     * Returns the access token as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->access_token;
    }
}
