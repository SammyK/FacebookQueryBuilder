<?php

use SammyK\FacebookQueryBuilder\Edge;
use SammyK\FacebookQueryBuilder\RootEdge;

class RootEdgeTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function the_root_edge_can_instantiate_with_just_the_edge_name()
    {
        $edge = new RootEdge('foo');

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\RootEdge', $edge);
    }

    /** @test */
    public function the_root_edge_can_be_converted_to_a_string()
    {
        $edge = new RootEdge('foo');

        $this->assertEquals('/foo', (string) $edge);
    }

    /** @test */
    public function the_root_edge_can_be_converted_to_a_string_with_fields()
    {
        $edge_one = new RootEdge('foo', ['bar']);
        $edge_two = new RootEdge('foo', ['bar', 'baz']);

        $this->assertEquals('/foo?fields=bar', (string) $edge_one);
        $this->assertEquals('/foo?fields=bar,baz', (string) $edge_two);
    }

    /** @test */
    public function the_root_edge_can_be_converted_to_a_string_with_fields_and_limit()
    {
        $edge = new RootEdge('foo', ['bar', 'baz'], 3);

        $this->assertEquals('/foo?limit=3&fields=bar,baz', (string) $edge);
    }

    /** @test */
    public function the_root_edge_can_be_converted_to_a_string_with_fields_and_limit_and_modifiers()
    {
        $edge = new RootEdge('foo', ['bar', 'baz'], 3);
        $edge->with(['foo' => 'bar']);
        $this->assertEquals('/foo?limit=3&fields=bar,baz&foo=bar', (string) $edge);

        $edge2 = new RootEdge('foo', ['bar', 'baz'], 3);
        $edge2->with([
                'foo' => 'bar',
                'faz' => 'baz',
            ]);
        $this->assertEquals('/foo?limit=3&fields=bar,baz&foo=bar&faz=baz', (string) $edge2);
    }

    /** @test */
    public function other_edges_can_be_embedded_in_the_root_edge()
    {
        $edge_to_embed = new Edge('embeds', ['faz', 'boo'], 6);
        $edge = new RootEdge('root', ['bar', 'baz', $edge_to_embed], 3);

        $this->assertEquals('/root?limit=3&fields=bar,baz,embeds.limit(6){faz,boo}', (string) $edge);
    }

    /** @test */
    public function embedded_edges_can_be_traversed_recursively()
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

    /** @test */
    public function root_edge_name_is_returned_when_there_are_no_children()
    {
        $edge = new RootEdge('foo', ['bar', 'baz'], 1);

        $children = $edge->getChildEdges();

        $this->assertEquals([['foo']], $children);
    }

    /** @test */
    public function the_root_edge_can_be_converted_to_an_endpoint()
    {
        $edge = new RootEdge('one', ['bar', 'baz'], 1);

        $endpoints = $edge->toEndpoints();

        $this->assertEquals(['/one'], $endpoints);
    }

    /** @test */
    public function the_root_edge_can_be_converted_to_an_endpoint_with_embedded_endpoints()
    {
        $edge_four = new Edge('four', ['bla'], 4);
        $edge_three = new Edge('three', ['faz', 'boo', $edge_four], 3);
        $edge_two = new Edge('two', ['faz', 'boo', $edge_three], 2);
        $edge = new RootEdge('one', ['bar', 'baz', $edge_two], 1);

        $endpoints = $edge->toEndpoints();

        $this->assertEquals(['/one/two/three/four'], $endpoints);
    }

    /** @test */
    public function the_root_edge_can_be_converted_to_an_endpoint_with_multiple_embedded_endpoints()
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
