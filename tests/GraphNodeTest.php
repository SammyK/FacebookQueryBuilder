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
        $node->modifiers(['foo' => 'bar']);
        $this->assertEquals('/foo?limit=3&foo=bar&fields=bar,baz', (string) $node);

        $node2 = new GraphNode('foo', ['bar', 'baz'], 3);
        $node2->modifiers([
                'foo' => 'bar',
                'faz' => 'baz',
            ]);
        $this->assertEquals('/foo?limit=3&foo=bar&faz=baz&fields=bar,baz', (string) $node2);
    }

    /** @test */
    public function other_edges_can_be_embedded_in_the_node()
    {
        $edge_to_embed = new GraphEdge('embeds', ['faz', 'boo'], 6);
        $node = new GraphNode('root', ['bar', 'baz', $edge_to_embed], 3);

        $this->assertEquals('/root?limit=3&fields=bar,baz,embeds.limit(6){faz,boo}', (string) $node);
    }
}
