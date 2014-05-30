<?php

use Mockery as m;
use SammyK\FacebookQueryBuilder\Auth;

class AuthTest extends PHPUnit_Framework_TestCase
{
    protected $auth;

    public function setUp()
    {
        $this->auth = new Auth();
    }

    public function tearDown()
    {
        m::close();
    }

    public function testCanGetLoginUrl()
    {
        $fb_redirect_helper = m::mock('Facebook\FacebookRedirectLoginHelper');

        $fb_redirect_helper
            ->shouldReceive('getLoginUrl')
            ->once()
            ->andReturn('http://bar');

        $this->auth->setRedirectHelper($fb_redirect_helper);
        $login_url = $this->auth->getLoginUrl('http://foo');

        $this->assertEquals('http://bar', $login_url);
    }

    public function testCanGetTokenFromRedirect()
    {
        $fb_redirect_helper = m::mock('Facebook\FacebookRedirectLoginHelper');
        $fb_session = m::mock('Facebook\FacebookSession');

        $fb_session
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('foo');
        $fb_redirect_helper
            ->shouldReceive('getSessionFromRedirect')
            ->once()
            ->andReturn($fb_session);

        $this->auth->setRedirectHelper($fb_redirect_helper);
        $token = $this->auth->getTokenFromRedirect('http://foo');

        $this->assertEquals('foo', $token);
    }

    public function testCanGetTokenFromCanvas()
    {
        $fb_canvas_helper = m::mock('Facebook\FacebookCanvasLoginHelper');
        $fb_session = m::mock('Facebook\FacebookSession');

        $fb_session
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('foo');
        $fb_canvas_helper
            ->shouldReceive('getSession')
            ->once()
            ->andReturn($fb_session);

        $this->auth->setCanvasHelper($fb_canvas_helper);
        $token = $this->auth->getTokenFromCanvas();

        $this->assertEquals('foo', $token);
    }

    public function getTokenFromJavascript()
    {
        $fb_javascript_helper = m::mock('Facebook\FacebookJavaScriptLoginHelper');
        $fb_session = m::mock('Facebook\FacebookSession');

        $fb_session
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('foo');
        $fb_javascript_helper
            ->shouldReceive('getSession')
            ->once()
            ->andReturn($fb_session);

        $this->auth->getJavascriptHelper($fb_javascript_helper);
        $token = $this->auth->getTokenFromJavascript();

        $this->assertEquals('foo', $token);
    }

}
