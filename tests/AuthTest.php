<?php

use Mockery as m;
use SammyK\FacebookQueryBuilder\Auth;

class MyCustomFooFacebookRedirectLoginHelper extends \Facebook\FacebookRedirectLoginHelper
{
    public function foo()
    {
        return 'bar';
    }
}

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

    /** @test */
    public function the_facebook_sdk_redirect_helper_can_be_overwritten()
    {
        Auth::setRedirectHelperAlias('MyCustomFooFacebookRedirectLoginHelper');

        $data = Auth::getRedirectHelper('http://foo')->foo();

        $this->assertEquals('bar', $data);
    }

    /** @test */
    public function can_get_a_login_url_from_the_facebook_sdk_redirect_helper()
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

    /** @test */
    public function can_obtain_an_access_token_object_from_a_redirect()
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

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\AccessToken', $token);
    }

    /** @test */
    public function can_obtain_an_access_token_object_from_the_canvas()
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

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\AccessToken', $token);
    }

    /** @test */
    public function can_obtain_an_access_token_object_from_the_cookie_set_by_the_javascript_sdk()
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

        $this->auth->setJavascriptHelper($fb_javascript_helper);
        $token = $this->auth->getTokenFromJavascript();

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\AccessToken', $token);
    }

}
