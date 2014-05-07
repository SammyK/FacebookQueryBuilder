<?php

use Mockery as m;
use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

class FacebookQueryBuilderExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testCanInstantiateWithMessageAndCode()
    {
        $e = new FacebookQueryBuilderException('Foo message', 123);

        $this->assertEquals('Foo message', $e->getMessage());
        $this->assertEquals(123, $e->getCode());
    }

    public function testCanInstantiateWithFacebookSdkException()
    {
        $exception_mock = $this->setUpApiException(123, 'OAuthException', 'Foo message');

        $e = new FacebookQueryBuilderException($exception_mock);

        $this->assertInstanceOf('\SammyK\FacebookQueryBuilder\GraphError', $e->getResponse());
        $this->assertEquals('Error communicating with Facebook: Foo message', $e->getMessage());
        $this->assertEquals(123, $e->getCode());
        $this->assertEquals('OAuthException', $e->getType());
    }

    public function testErrorCodeHandling()
    {
        $exception_mock = $this->setUpApiException(102);
        $this->runTheApiExceptionTest($exception_mock, 'Login required.');

        $exception_mock = $this->setUpApiException(190, 'OAuthException');
        $this->runTheApiExceptionTest($exception_mock, 'Login required.');

        $exception_mock = $this->setUpApiException(4);
        $this->runTheApiExceptionTest($exception_mock, 'Downtime. Try again later.');

        $exception_mock = $this->setUpApiException(506);
        $this->runTheApiExceptionTest($exception_mock, 'Duplicate post. Change and try again.');

        $exception_mock = $this->setUpApiException(459);
        $this->runTheApiExceptionTest($exception_mock, 'User issue on Facebook.');

        $exception_mock = $this->setUpApiException(10);
        $this->runTheApiExceptionTest($exception_mock, 'Extended permission required.');

        $exception_mock = $this->setUpApiException(200);
        $this->runTheApiExceptionTest($exception_mock, 'Extended permission required.');

        $exception_mock = $this->setUpApiException(298, 'OAuthException', '(#298) Requires extended permission: read_mailbox');
        $this->runTheApiExceptionTest($exception_mock, 'Extended permission required.');
    }

    public function testExtendedPermissionDetecting()
    {
        $exception_mock = $this->setUpApiException(298, 'OAuthException', '(#298) Requires extended permission: read_mailbox');

        $e = new FacebookQueryBuilderException($exception_mock);

        $this->assertEquals(['read_mailbox'], $e->detectRequiredPermissions());
    }

    public function setUpApiException($code, $type = 'Exception', $message = 'foo')
    {
        $response = [
            'error' => [
                'code' => $code,
                'message' => $message,
                'type' => $type,
                'error_subcode' => 0,
            ],
        ];

        $mock = m::mock('Facebook\FacebookRequestException', [json_encode($response), $response, 401]);

        $mock->shouldReceive('getResponse')
             ->once()
             ->withNoArgs()
             ->andReturn($response);

        $mock->shouldReceive('getErrorType')
             ->once()
             ->withNoArgs()
             ->andReturn($type);

        return $mock;
    }

    public function runTheApiExceptionTest($exception_mock, $expected_summary)
    {
        $e = new FacebookQueryBuilderException($exception_mock);

        $this->assertEquals($expected_summary, $e->errorSummary());
    }
}
