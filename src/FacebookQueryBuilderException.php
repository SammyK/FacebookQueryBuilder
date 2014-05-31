<?php namespace SammyK\FacebookQueryBuilder;

use Facebook\FacebookRequestException;

class FacebookQueryBuilderException extends \Exception
{
    /**
     * Response object.
     *
     * @var Response
     */
    protected $response;

    /**
     * Type of error from the Facebook Graph API.
     *
     * @var string
     */
    protected $type;

    /**
     * Make a new API Exception with the given result.
     *
     * @param \Facebook\FacebookRequestException|string $e
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($e, $code = 0, \Exception $previous = null)
    {
        if ($e instanceof FacebookRequestException)
        {
            $this->response = new Response($e->getResponse());

            $this->type = $e->getErrorType();

            parent::__construct('Graph returned an error response.', 10, $e);

            return;
        }

        parent::__construct($e, $code, $previous);
    }

    /**
     * Get the response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response instanceof Response ? $this->response->getResponse() : null;
    }

    /**
     * Get the type of error from the Facebook Graph API.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Translates the response code into something more readable.
     *
     * @return string
     */
    public function errorSummary()
    {
        $code = $this->getPrevious()->getCode();

        switch ($code)
        {
            // Login Required
            case 0:
            case 102:
            case 458:
            case 460:
            case 463:
            case 467:
                return 'Login required.';
                break;

            // Downtime on Facebook's end
            case 1:
            case 2:
            case 4:
            case 17:
            case 341:
                return 'Downtime. Try again later.';
                break;

            // Duplicate Post
            case 506:
                return 'Duplicate post. Change and try again.';
                break;

            // Facebook has issue with this user
            case 459:
            case 464:
                return 'User issue on Facebook.';
                break;
        }

        // More Permissions Required
        if ($code == 10 || ($code >= 200 && $code <= 299))
        {
            return 'Extended permission required.';
        }

        if ($this->getType() === 'OAuthException')
        {
            return 'Login required.';
        }

        return 'Unknown Error';
    }

    /**
     * Parses the API exception message for the required permissions
     *
     * @return array
     */
    public function detectRequiredPermissions()
    {
        if (preg_match('/\(#[0-9]+\) Requires extended permission: (.+)/', $this->getPrevious()->getMessage(), $a) === 1)
        {
            return [$a[1]];
        }

        return [];
    }
}
