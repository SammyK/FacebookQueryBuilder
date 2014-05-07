<?php

use Mockery as m;
use SammyK\FacebookQueryBuilder\Connection;

class ConnectionTest extends PHPUnit_Framework_TestCase
{
    protected $facebook_request_maker_mock;
    protected $facebook_response_mock;
    protected $response_mock;
    protected $facebook_session_mock;
    protected $root_edge_mock;
    protected $connection;

    public function setUp()
    {
        $this->facebook_request_maker_mock = m::mock('SammyK\FacebookQueryBuilder\FacebookRequestMaker');
        $this->response_mock = m::mock('SammyK\FacebookQueryBuilder\Response');

        $this->connection = new Connection($this->facebook_request_maker_mock, $this->response_mock);

        $this->facebook_session_mock = m::mock('Facebook\FacebookSession');
        $this->connection->setFacebookSession($this->facebook_session_mock);

        $this->facebook_response_mock = m::mock('Facebook\FacebookResponse');
        $this->root_edge_mock = m::mock('SammyK\FacebookQueryBuilder\RootEdge');
    }

    public function tearDown()
    {
        m::close();
    }

    public function testGetsDataFromApi()
    {
        $fb_response = m::mock('Facebook\FacebookResponse');

        $this->root_edge_mock
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('/foo');
        $this->facebook_response_mock
            ->shouldReceive('execute')
            ->once()
            ->andReturn($fb_response);
        $this->facebook_request_maker_mock
            ->shouldReceive('make')
            ->with($this->facebook_session_mock, 'GET', '/foo', [])
            ->once()
            ->andReturn($this->facebook_response_mock);

        $this->response_mock
            ->shouldReceive('create')
            ->with($fb_response)
            ->once()
            ->andReturn($this->response_mock);

        $api_data = $this->connection->get($this->root_edge_mock);

        $this->assertInstanceOf('\SammyK\FacebookQueryBuilder\Response', $api_data);
    }

    public function testPostsDataToApi()
    {
        $fb_response = m::mock('Facebook\FacebookResponse');

        $this->root_edge_mock
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('/foo');
        $this->facebook_response_mock
            ->shouldReceive('execute')
            ->once()
            ->andReturn($fb_response);
        $this->facebook_request_maker_mock
            ->shouldReceive('make')
            ->with($this->facebook_session_mock, 'POST', '/foo', ['foo' => 'bar'])
            ->once()
            ->andReturn($this->facebook_response_mock);

        $this->response_mock
            ->shouldReceive('create')
            ->with($fb_response)
            ->once()
            ->andReturn($this->response_mock);

        $api_data = $this->connection->post($this->root_edge_mock, ['foo' => 'bar']);

        $this->assertInstanceOf('\SammyK\FacebookQueryBuilder\Response', $api_data);
    }

    public function testDeletesDataFromApi()
    {
        $fb_response = m::mock('Facebook\FacebookResponse');

        $this->root_edge_mock
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('/foo');
        $this->facebook_response_mock
            ->shouldReceive('execute')
            ->once()
            ->andReturn($fb_response);
        $this->facebook_request_maker_mock
            ->shouldReceive('make')
            ->with($this->facebook_session_mock, 'DELETE', '/foo', [])
            ->once()
            ->andReturn($this->facebook_response_mock);

        $this->response_mock
            ->shouldReceive('create')
            ->with($fb_response)
            ->once()
            ->andReturn($this->response_mock);

        $api_data = $this->connection->delete($this->root_edge_mock);

        $this->assertInstanceOf('\SammyK\FacebookQueryBuilder\Response', $api_data);
    }

    /**
     * @expectedException \SammyK\FacebookQueryBuilder\FacebookQueryBuilderException
     */
    public function testSendMethodCatchesAndThrowsException()
    {
        $facebook_api_exception_mock = m::mock('Facebook\FacebookRequestException');

        $facebook_api_exception_mock
            ->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn([
                    'error' => [
                        'foo' => 'bar',
                    ],
                ]);

        $facebook_api_exception_mock
            ->shouldReceive('getErrorType')
            ->once()
            ->withNoArgs()
            ->andReturn('foo_type');


        $this->facebook_response_mock
            ->shouldReceive('execute')
            ->once()
            ->andThrow($facebook_api_exception_mock);
        $this->facebook_request_maker_mock
            ->shouldReceive('make')
            ->with($this->facebook_session_mock, 'GET', '/foo', [])
            ->once()
            ->andReturn($this->facebook_response_mock);

        $this->connection->send('/foo');
    }
}
