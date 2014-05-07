<?php

use SammyK\FacebookQueryBuilder\Edge;
use SammyK\FacebookQueryBuilder\RootEdge;

class RootEdgeTest extends PHPUnit_Framework_TestCase
{
    public function testOnlyNeedsEdgeNameToInstantiate()
    {
        $edge = new RootEdge('foo');

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\RootEdge', $edge);
    }

    public function testConvertsRootEdgeToString()
    {
        $edge = new RootEdge('foo');

        $this->assertEquals('/foo', (string) $edge);
    }

    public function testConvertsRootEdgeWithFieldsToString()
    {
        $edge_one = new RootEdge('foo', ['bar']);
        $edge_two = new RootEdge('foo', ['bar', 'baz']);

        $this->assertEquals('/foo?fields=bar', (string) $edge_one);
        $this->assertEquals('/foo?fields=bar,baz', (string) $edge_two);
    }

    public function testConvertsRootEdgeWithFieldsAndLimitToString()
    {
        $edge = new RootEdge('foo', ['bar', 'baz'], 3);

        $this->assertEquals('/foo?limit=3&fields=bar,baz', (string) $edge);
    }

    public function testCanEmbedOtherEdges()
    {
        $edge_to_embed = new Edge('embeds', ['faz', 'boo'], 6);
        $edge = new RootEdge('root', ['bar', 'baz', $edge_to_embed], 3);

        $this->assertEquals('/root?limit=3&fields=bar,baz,embeds.limit(6).fields(faz,boo)', (string) $edge);
    }

    public function testRecursivelyTraverseNestedEdges()
    {
        $edge_r_four = new Edge('r_four', ['bla'], 4);

        $edge_four = new Edge('four', ['bla'], 4);
        $edge_three = new Edge('three', ['faz', 'boo', $edge_four, $edge_r_four], 3);
        $edge_two = new Edge('two', ['faz', 'boo', $edge_three], 2);

        $edge_r_three = new Edge('r_three');
        $edge_r_two = new Edge('r_two', [$edge_r_three]);

        $edge = new RootEdge('one', ['bar', 'baz', $edge_two, $edge_r_two], 1);

        $children = $edge->getChildEdges();

        $expected = [
            ['one', 'two', 'three', 'four'],
            ['one', 'two', 'three', 'r_four'],
            ['one', 'r_two', 'r_three'],
        ];

        $this->assertEquals($expected, $children);
    }

    public function testGettingChildEdgesReturnsSelf()
    {
        $edge = new RootEdge('foo', ['bar', 'baz'], 1);

        $children = $edge->getChildEdges();

        $this->assertEquals([['foo']], $children);
    }

    public function testCanConvertEdgeToEndpointsWithNoChildren()
    {
        $edge = new RootEdge('one', ['bar', 'baz'], 1);

        $endpoints = $edge->toEndpoints();

        $this->assertEquals(['/one'], $endpoints);
    }

    public function testCanConvertEdgeToEndpointsWithDepth()
    {
        $edge_four = new Edge('four', ['bla'], 4);
        $edge_three = new Edge('three', ['faz', 'boo', $edge_four], 3);
        $edge_two = new Edge('two', ['faz', 'boo', $edge_three], 2);
        $edge = new RootEdge('one', ['bar', 'baz', $edge_two], 1);

        $endpoints = $edge->toEndpoints();

        $this->assertEquals(['/one/two/three/four'], $endpoints);
    }

    public function testCanConvertEdgeToEndpointsWithMultiEdgesAndDepth()
    {
        $edge_tags = new Edge('tags');

        $edge_d = new Edge('d');
        $edge_c = new Edge('c', [$edge_d]);
        $edge_b = new Edge('b', [$edge_c, $edge_tags]);
        $edge_a = new Edge('a', [$edge_b]);

        $edge_four = new Edge('four', ['bla'], 4);
        $edge_three = new Edge('three', ['faz', 'boo', $edge_four], 3);
        $edge_two = new Edge('two', ['faz', 'boo', $edge_three], 2);
        $edge = new RootEdge('one', [$edge_a, 'bar', 'baz', $edge_two, 'foo'], 1);

        $endpoints = $edge->toEndpoints();

        $expected = [
            '/one/a/b/c/d',
            '/one/a/b/tags',
            '/one/two/three/four',
        ];

        $this->assertEquals($expected, $endpoints);
    }
}
