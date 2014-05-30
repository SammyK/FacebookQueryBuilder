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

    public function testCanGetInstanceOfAuth()
    {
        $auth = $this->fqb->auth();

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\Auth', $auth);
    }

    public function testCanInstantiateRootEdgeDynamically()
    {
        $edge = $this->fqb->object('foo');

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\FQB', $edge);
        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\RootEdge', $edge->root_edge);
    }

    public function testProperlySetsRootEdgeName()
    {
        $edge = $this->fqb->object('foo');

        $this->assertEquals('foo', $edge->root_edge->name);
    }

    public function testProperlySetsRootEdgeFieldsViaArgument()
    {
        $edge = $this->fqb->object('foo', ['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $edge->root_edge->fields);
    }

    public function testProperlyAliasesLimitMethodToRootEdge()
    {
        $edge = $this->fqb->object('foo')->limit(2);

        $this->assertEquals(2, $edge->root_edge->limit);
        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\FQB', $edge);
    }

    public function testProperlyAliasesRootEdgeFieldsMethodToRootEdge()
    {
        $edge = $this->fqb->object('foo')->fields(['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $edge->root_edge->fields);
        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\FQB', $edge);
    }

    public function testProperlyAliasesRootEdgeFieldsMethodToRootEdgeAsArguments()
    {
        $edge = $this->fqb->object('foo')->fields('foo', 'bar');

        $this->assertEquals(['foo', 'bar'], $edge->root_edge->fields);
    }

    public function testGetQueryUrlReturnsUrlAsString()
    {
        $edge = $this->fqb->object('foo', ['bar', 'baz'])->limit(2);

        $url = $edge->getQueryUrl();

        $this->assertEquals('/foo?limit=2&fields=bar,baz', $url);
    }

    public function testCanNewUpSubEdgeDynamically()
    {
        $edge = $this->fqb->edge('foo');

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\Edge', $edge);
    }

    public function testCanNewUpSubEdgeWithFieldsDynamically()
    {
        $edge = $this->fqb->edge('foo', ['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $edge->fields);
    }

    public function testGetsResponseFromConnectionObject()
    {
        $connection_mock = m::mock('SammyK\FacebookQueryBuilder\Connection');
        FQB::setConnection($connection_mock);

        $response_mock = m::mock('SammyK\FacebookQueryBuilder\Response');
        $response_mock
            ->shouldReceive('getResponse')
            ->once()
            ->andReturn('foo response');

        $edge = $this->fqb->object('foo', ['bar', 'baz']);
        $connection_mock
            ->shouldReceive('get')
            ->with($edge->root_edge)
            ->once()
            ->andReturn($response_mock);

        $response = $edge->get();

        $this->assertEquals('foo response', $response);
    }

    public function testGetsResponseFromConnectionObjectWithFieldsPassedInGetMethod()
    {
        $connection_mock = m::mock('SammyK\FacebookQueryBuilder\Connection');
        FQB::setConnection($connection_mock);

        $response_mock = m::mock('SammyK\FacebookQueryBuilder\Response');
        $response_mock
            ->shouldReceive('getResponse')
            ->once()
            ->andReturn('foo response');

        $edge = $this->fqb->object('foo', ['bar', 'baz']);
        $edge->root_edge = m::mock('SammyK\FacebookQueryBuilder\RootEdge');

        $edge->root_edge
            ->shouldReceive('fields')
            ->with(['foo', 'boo'])
            ->once()
            ->andReturn(null);

        $connection_mock
            ->shouldReceive('get')
            ->with($edge->root_edge)
            ->once()
            ->andReturn($response_mock);

        $response = $edge->get(['foo', 'boo']);

        $this->assertEquals('foo response', $response);
    }
}
