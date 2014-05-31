<?php

use Mockery as m;
use SammyK\FacebookQueryBuilder\AccessToken;
use SammyK\FacebookQueryBuilder\Connection;
use SammyK\FacebookQueryBuilder\FQB;

class AccessTokenTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Connection::setAppCredentials(123, 'foo_secret');
    }

    public function tearDown()
    {
        m::close();
    }

    /** @test */
    public function a_short_lived_access_token_can_be_exchanged_for_a_long_lived_access_token()
    {
        $fb_reponse = m::mock('SammyK\FacebookQueryBuilder\Response');
        $fb_connection = m::mock('SammyK\FacebookQueryBuilder\Connection');

        $params = [
            'client_id' => 123,
            'client_secret' => 'foo_secret',
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => 'foo_short_token',
        ];

        $fb_reponse
            ->shouldReceive('getResponse')
            ->once()
            ->andReturn([
                'access_token' => 'foo_long_token',
                ]);
        $fb_connection
            ->shouldReceive('send')
            ->with('/oauth/access_token', 'GET', $params, true)
            ->once()
            ->andReturn($fb_reponse);

        FQB::setConnection($fb_connection);

        $short_lived_token = new AccessToken('foo_short_token');
        $long_lived_token = $short_lived_token->extend();

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\AccessToken', $long_lived_token);
        $this->assertEquals('foo_long_token', (string) $long_lived_token);
    }

    /** @test */
    public function an_access_token_entity_can_return_info_about_itself_from_graph()
    {
        $fb_reponse = m::mock('SammyK\FacebookQueryBuilder\Response');
        $fb_connection = m::mock('SammyK\FacebookQueryBuilder\Connection');

        $fb_reponse
            ->shouldReceive('getResponse')
            ->once()
            ->andReturn('foo_data');
        $fb_connection
            ->shouldReceive('send')
            ->with('/debug_token', 'GET', ['input_token' => 'foo_token'], true)
            ->once()
            ->andReturn($fb_reponse);

        FQB::setConnection($fb_connection);

        $token = new AccessToken('foo_token');
        $token_data = $token->getInfo();

        $this->assertEquals('foo_data', $token_data);
    }

    /** @test */
    public function an_access_token_properly_determines_it_is_short_lived()
    {
        $expires_in_one_hour = time() + (60 * 60);
        $token = new AccessToken('foo_token', $expires_in_one_hour);
        $is_long_lived = $token->isLongLived();

        $this->assertFalse($is_long_lived, 'Did not expect token to be long lived.');
    }

    /** @test */
    public function an_access_token_properly_determines_it_is_long_lived()
    {
        $expires_in_one_week = time() + (60 * 60 * 24 * 7);
        $token = new AccessToken('foo_token', $expires_in_one_week);
        $is_long_lived = $token->isLongLived();

        $this->assertTrue($is_long_lived, 'Expected token to be long lived.');
    }

    /** @test */
    public function an_expired_access_token_will_not_say_it_is_long_lived()
    {
        $expired_one_week_ago = time() - (60 * 60 * 24 * 7);
        $token = new AccessToken('foo_token', $expired_one_week_ago);
        $is_long_lived = $token->isLongLived();

        $this->assertFalse($is_long_lived, 'Did not expect expired token to be long lived.');
    }

}
