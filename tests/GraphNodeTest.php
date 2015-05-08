<?php namespace SammyK\FacebookQueryBuilderTests;

use SammyK\FacebookQueryBuilder\GraphEdge;
use SammyK\FacebookQueryBuilder\GraphNode;

class GraphNodeTest extends \PHPUnit_Framework_TestCase
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
    public function the_limit_gets_set_properly()
    {
        $node = new GraphNode('foo');
        $node->limit(5);

        $this->assertEquals('/foo?limit=5', (string) $node);
    }

    /** @test */
    public function the_fields_can_be_set_by_sending_an_array()
    {
        $node = new GraphNode('foo');
        $node->fields(['bar', 'baz']);

        $this->assertEquals(['bar', 'baz'], $node->getFields());
    }

    /** @test */
    public function new_fields_will_get_merged_into_existing_fields()
    {
        $node = new GraphNode('foo', ['foo', 'bar']);
        $node->fields('baz');

        $this->assertEquals(['foo', 'bar', 'baz'], $node->getFields());
    }

    /** @test */
    public function the_modifiers_can_be_set_by_sending_an_array()
    {
        $node = new GraphNode('foo');
        $node->modifiers(['bar' => 'baz']);

        $this->assertEquals(['bar' => 'baz'], $node->getModifiers());
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
        $edgeToEmbed = new GraphEdge('embeds', ['faz', 'boo'], 6);
        $node = new GraphNode('root', ['bar', 'baz', $edgeToEmbed], 3);

        $this->assertEquals('/root?limit=3&fields=bar,baz,embeds.limit(6){faz,boo}', (string) $node);
    }
}
