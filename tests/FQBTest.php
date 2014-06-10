<?php

use Mockery as m;
use SammyK\FacebookQueryBuilder\FQB;

class FQBTest extends PHPUnit_Framework_TestCase
{
    protected $fqb;

    public function setUp()
    {
        $this->fqb = new FQB();
    }

    public function tearDown()
    {
        m::close();
    }

    /** @test */
    public function an_instance_of_auth_can_be_grabbed()
    {
        $auth = $this->fqb->auth();

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\Auth', $auth);
    }

    /** @test */
    public function a_root_edge_can_be_instantiated_magically()
    {
        $fqb = $this->fqb->object('foo');

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\FQB', $fqb);
        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\RootEdge', $fqb->root_edge);
    }

    /** @test */
    public function a_root_edge_name_can_be_set_properly()
    {
        $fqb = $this->fqb->object('foo');

        $this->assertEquals('foo', $fqb->root_edge->name);
    }

    /** @test */
    public function a_root_edge_name_and_fields_can_be_set_properly()
    {
        $fqb = $this->fqb->object('foo', ['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $fqb->root_edge->fields);
    }

    /** @test */
    public function the_limit_method_is_aliased_to_the_root_edge()
    {
        $fqb = $this->fqb->object('foo')->limit(2);

        $this->assertEquals(2, $fqb->root_edge->limit);
        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\FQB', $fqb);
    }

    /** @test */
    public function the_fields_method_is_aliased_to_the_root_edge_with_an_array_as_the_argument()
    {
        $fqb = $this->fqb->object('foo')->fields(['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $fqb->root_edge->fields);
        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\FQB', $fqb);
    }

    /** @test */
    public function the_fields_method_is_aliased_to_the_root_edge_with_a_key_and_value_as_the_arguments()
    {
        $fqb = $this->fqb->object('foo')->fields('foo', 'bar');

        $this->assertEquals(['foo', 'bar'], $fqb->root_edge->fields);
    }

    /** @test */
    public function a_full_url_endpoint_is_properly_generated()
    {
        $fqb = $this->fqb->object('foo', ['bar', 'baz'])->limit(2);

        $url = $fqb->getQueryUrl();

        $this->assertEquals('/foo?limit=2&fields=bar,baz', $url);
    }

    /** @test */
    public function a_new_edge_can_be_instantiated_magically()
    {
        $edge = $this->fqb->edge('foo');

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\Edge', $edge);
    }

    /** @test */
    public function a_new_edge_with_fields_can_be_instantiated_magically()
    {
        $edge = $this->fqb->edge('foo', ['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $edge->fields);
    }

    /** @test */
    public function a_response_is_returned_with_the_get_method()
    {
        $connection_mock = m::mock('SammyK\FacebookQueryBuilder\Connection');
        FQB::setConnection($connection_mock);

        $response_mock = m::mock('SammyK\FacebookQueryBuilder\Response');
        $response_mock
            ->shouldReceive('getResponse')
            ->once()
            ->andReturn('foo response');

        $fqb = $this->fqb->object('foo', ['bar', 'baz']);
        $connection_mock
            ->shouldReceive('get')
            ->with($fqb->root_edge)
            ->once()
            ->andReturn($response_mock);

        $response = $fqb->get();

        $this->assertEquals('foo response', $response);
    }

    /** @test */
    public function a_response_is_returned_with_the_get_method_when_fields_are_passed_to_get_method()
    {
        $connection_mock = m::mock('SammyK\FacebookQueryBuilder\Connection');
        FQB::setConnection($connection_mock);

        $response_mock = m::mock('SammyK\FacebookQueryBuilder\Response');
        $response_mock
            ->shouldReceive('getResponse')
            ->once()
            ->andReturn('foo response');

        $fqb = $this->fqb->object('foo', ['bar', 'baz']);
        $fqb->root_edge = m::mock('SammyK\FacebookQueryBuilder\RootEdge');

        $fqb->root_edge
            ->shouldReceive('fields')
            ->with(['foo', 'boo'])
            ->once()
            ->andReturn(null);

        $connection_mock
            ->shouldReceive('get')
            ->with($fqb->root_edge)
            ->once()
            ->andReturn($response_mock);

        $response = $fqb->get(['foo', 'boo']);

        $this->assertEquals('foo response', $response);
    }

    /** @test */
    public function modifiers_are_sent_in_the_body_of_post_requests()
    {
        $connection_mock = m::mock('SammyK\FacebookQueryBuilder\Connection');
        FQB::setConnection($connection_mock);

        $response_mock = m::mock('SammyK\FacebookQueryBuilder\Response');
        $response_mock
            ->shouldReceive('getResponse')
            ->once()
            ->andReturn('foo response');

        $modifiers = ['foo' => 'bar'];
        $fqb = $this->fqb->object('foo');
        $fqb->with($modifiers);
        $fqb->root_edge = m::mock('SammyK\FacebookQueryBuilder\RootEdge');

        $connection_mock
            ->shouldReceive('post')
            ->with($fqb->root_edge, $modifiers)
            ->once()
            ->andReturn($response_mock);

        $response = $fqb->post();

        $this->assertEquals('foo response', $response);
    }

    /** @test */
    public function modifiers_are_sent_in_the_body_of_delete_requests()
    {
        $connection_mock = m::mock('SammyK\FacebookQueryBuilder\Connection');
        FQB::setConnection($connection_mock);

        $response_mock = m::mock('SammyK\FacebookQueryBuilder\Response');
        $response_mock
            ->shouldReceive('getResponse')
            ->once()
            ->andReturn('foo response');

        $modifiers = ['foo' => 'bar'];
        $fqb = $this->fqb->object('foo');
        $fqb->with($modifiers);
        $fqb->root_edge = m::mock('SammyK\FacebookQueryBuilder\RootEdge');

        $connection_mock
            ->shouldReceive('delete')
            ->with($fqb->root_edge, $modifiers)
            ->once()
            ->andReturn($response_mock);

        $response = $fqb->delete();

        $this->assertEquals('foo response', $response);
    }

    /** @test */
    public function modifiers_are_sent_in_the_url_of_get_requests()
    {
        $connection_mock = m::mock('SammyK\FacebookQueryBuilder\Connection');
        FQB::setConnection($connection_mock);

        $response_mock = m::mock('SammyK\FacebookQueryBuilder\Response');
        $response_mock
            ->shouldReceive('getResponse')
            ->once()
            ->andReturn('foo response');

        $modifiers = ['foo' => 'bar'];
        $fqb = $this->fqb->object('foo');
        $fqb->with($modifiers);
        $fqb->root_edge = m::mock('SammyK\FacebookQueryBuilder\RootEdge');

        $fqb->root_edge
            ->shouldReceive('with')
            ->with($modifiers)
            ->once()
            ->andReturn(null);

        $connection_mock
            ->shouldReceive('get')
            ->with($fqb->root_edge)
            ->once()
            ->andReturn($response_mock);

        $response = $fqb->get();

        $this->assertEquals('foo response', $response);
    }

    /** @test */
    public function search_method_will_return_a_search_instance()
    {
        $fqb = $this->fqb->search('foo search');
        $fqb->prepareRootEdgeForGetRequest();
        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\FQB', $fqb);
        $this->assertEquals('/search?q=foo+search', $fqb->getQueryUrl());
        $this->assertEquals(['q' => 'foo search'], $fqb->modifiers);

        $fqb2 = $this->fqb->search('foo search', 'bar');
        $fqb2->prepareRootEdgeForGetRequest();
        $this->assertEquals('/search?q=foo+search&type=bar', $fqb2->getQueryUrl());
        $this->assertEquals(['q' => 'foo search', 'type' => 'bar'], $fqb2->modifiers);
    }
}
