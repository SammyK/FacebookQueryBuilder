<?php

use SammyK\FacebookQueryBuilder\GraphEdge;
use SammyK\FacebookQueryBuilder\GraphNode;

class GraphNodeTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function the_node_can_instantiate_with_just_the_edge_name()
    {
        $node = new GraphNode('foo');

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\GraphNode', $node);
    }

    /** @test */
    public function the_node_can_be_converted_to_a_string()
    {
        $node = new GraphNode('foo');

        $this->assertEquals('/foo', (string) $node);
    }

    /** @test */
    public function the_node_can_be_converted_to_a_string_with_fields()
    {
        $node_one = new GraphNode('foo', ['bar']);
        $node_two = new GraphNode('foo', ['bar', 'baz']);

        $this->assertEquals('/foo?fields=bar', (string) $node_one);
        $this->assertEquals('/foo?fields=bar,baz', (string) $node_two);
    }

    /** @test */
    public function the_node_can_be_converted_to_a_string_with_fields_and_limit()
    {
        $node = new GraphNode('foo', ['bar', 'baz'], 3);

        $this->assertEquals('/foo?limit=3&fields=bar,baz', (string) $node);
    }

    /** @test */
    public function the_node_can_be_converted_to_a_string_with_fields_and_limit_and_modifiers()
    {
        $node = new GraphNode('foo', ['bar', 'baz'], 3);
        $node->with(['foo' => 'bar']);
        $this->assertEquals('/foo?limit=3&fields=bar,baz&foo=bar', (string) $node);

        $node2 = new GraphNode('foo', ['bar', 'baz'], 3);
        $node2->with([
                'foo' => 'bar',
                'faz' => 'baz',
            ]);
        $this->assertEquals('/foo?limit=3&fields=bar,baz&foo=bar&faz=baz', (string) $node2);
    }

    /** @test */
    public function other_edges_can_be_embedded_in_the_node()
    {
        $edge_to_embed = new GraphEdge('embeds', ['faz', 'boo'], 6);
        $node = new GraphNode('root', ['bar', 'baz', $edge_to_embed], 3);

        $this->assertEquals('/root?limit=3&fields=bar,baz,embeds.limit(6){faz,boo}', (string) $node);
    }

    /** @test */
    public function embedded_edges_can_be_traversed_recursively()
    {
        $edge_r_four = new GraphEdge('r_four', ['bla'], 4);

        $edge_four = new GraphEdge('four', ['bla'], 4);
        $edge_three = new GraphEdge('three', ['faz', 'boo', $edge_four, $edge_r_four], 3);
        $edge_two = new GraphEdge('two', ['faz', 'boo', $edge_three], 2);

        $edge_r_three = new GraphEdge('r_three');
        $edge_r_two = new GraphEdge('r_two', [$edge_r_three]);

        $node = new GraphNode('one', ['bar', 'baz', $edge_two, $edge_r_two], 1);

        $children = $node->getChildEdges();

        $expected = [
            ['one', 'two', 'three', 'four'],
            ['one', 'two', 'three', 'r_four'],
            ['one', 'r_two', 'r_three'],
        ];

        $this->assertEquals($expected, $children);
    }

    /** @test */
    public function node_name_is_returned_when_there_are_no_children()
    {
        $node = new GraphNode('foo', ['bar', 'baz'], 1);

        $children = $node->getChildEdges();

        $this->assertEquals([['foo']], $children);
    }

    /** @test */
    public function the_node_can_be_converted_to_an_endpoint()
    {
        $node = new GraphNode('one', ['bar', 'baz'], 1);

        $endpoints = $node->toEndpoints();

        $this->assertEquals(['/one'], $endpoints);
    }

    /** @test */
    public function the_node_can_be_converted_to_an_endpoint_with_embedded_endpoints()
    {
        $edge_four = new GraphEdge('four', ['bla'], 4);
        $edge_three = new GraphEdge('three', ['faz', 'boo', $edge_four], 3);
        $edge_two = new GraphEdge('two', ['faz', 'boo', $edge_three], 2);
        $node = new GraphNode('one', ['bar', 'baz', $edge_two], 1);

        $endpoints = $node->toEndpoints();

        $this->assertEquals(['/one/two/three/four'], $endpoints);
    }

    /** @test */
    public function the_node_can_be_converted_to_an_endpoint_with_multiple_embedded_endpoints()
    {
        $edge_tags = new GraphEdge('tags');

        $edge_d = new GraphEdge('d');
        $edge_c = new GraphEdge('c', [$edge_d]);
        $edge_b = new GraphEdge('b', [$edge_c, $edge_tags]);
        $edge_a = new GraphEdge('a', [$edge_b]);

        $edge_four = new GraphEdge('four', ['bla'], 4);
        $edge_three = new GraphEdge('three', ['faz', 'boo', $edge_four], 3);
        $edge_two = new GraphEdge('two', ['faz', 'boo', $edge_three], 2);
        $node = new GraphNode('one', [$edge_a, 'bar', 'baz', $edge_two, 'foo'], 1);

        $endpoints = $node->toEndpoints();

        $expected = [
            '/one/a/b/c/d',
            '/one/a/b/tags',
            '/one/two/three/four',
        ];

        $this->assertEquals($expected, $endpoints);
    }
}
