<?php namespace SammyK\FacebookQueryBuilderTests;

use SammyK\FacebookQueryBuilder\GraphEdge;

class GraphEdgeTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function modifiers_get_compiled_with_proper_syntax()
    {
        $edge = new GraphEdge('foo');
        $modifiers = $edge->asUrl();
        $this->assertEquals('foo', $modifiers);

        $edge2 = new GraphEdge('bar');
        $edge2->modifiers(['bar' => 'baz']);
        $modifiers2 = $edge2->asUrl();
        $this->assertEquals('bar.bar(baz)', $modifiers2);

        $edge3 = new GraphEdge('baz');
        $edge3->modifiers([
                'foo' => 'bar',
                'faz' => 'baz',
            ]);
        $modifiers3 = $edge3->asUrl();
        $this->assertEquals('baz.foo(bar).faz(baz)', $modifiers3);
    }

    /** @test */
    public function an_edge_will_convert_to_string()
    {
        $edge = new GraphEdge('foo');

        $this->assertEquals('foo', (string) $edge);
    }

    /** @test */
    public function an_edge_with_fields_will_convert_to_string()
    {
        $edge_one = new GraphEdge('foo', ['bar']);
        $edge_two = new GraphEdge('foo', ['bar', 'baz']);

        $this->assertEquals('foo{bar}', (string) $edge_one);
        $this->assertEquals('foo{bar,baz}', (string) $edge_two);
    }

    /** @test */
    public function an_edge_with_fields_and_limit_will_convert_to_string()
    {
        $edge = new GraphEdge('foo', ['bar', 'baz'], 3);

        $this->assertEquals('foo.limit(3){bar,baz}', (string) $edge);
    }

    /** @test */
    public function an_edge_with_fields_and_limit_and_modifiers_will_convert_to_string()
    {
        $edge = new GraphEdge('foo', ['bar', 'baz'], 3);
        $edge->modifiers(['foo' => 'bar']);

        $this->assertEquals('foo.limit(3).foo(bar){bar,baz}', (string) $edge);
    }

    /** @test */
    public function an_edge_can_be_embedded_into_another_edge()
    {
        $edge_to_embed = new GraphEdge('embeds', ['faz', 'boo'], 6);
        $edge = new GraphEdge('foo', ['bar', 'baz', $edge_to_embed], 3);

        $this->assertEquals('foo.limit(3){bar,baz,embeds.limit(6){faz,boo}}', (string) $edge);
    }

    /** @test */
    public function edges_can_be_embedded_into_other_edges_deeply()
    {
        $edge_level_one = new GraphEdge('level_one', ['one', 'foo'], 1);
        $edge_level_two = new GraphEdge('level_two', ['two', 'bar', $edge_level_one], 2);
        $edge_level_three = new GraphEdge('level_three', ['three', 'baz', $edge_level_two], 3);
        $edge_level_four = new GraphEdge('level_four', ['four', 'faz', $edge_level_three], 4);
        $edge = new GraphEdge('root', ['foo', 'bar', $edge_level_four], 5);

        $expected_one = 'level_one.limit(1){one,foo}';
        $expected_two = 'level_two.limit(2){two,bar,' . $expected_one .'}';
        $expected_three = 'level_three.limit(3){three,baz,' . $expected_two .'}';
        $expected_four = 'level_four.limit(4){four,faz,' . $expected_three .'}';
        $expected_edge = 'root.limit(5){foo,bar,' . $expected_four .'}';

        $this->assertEquals($expected_edge, (string) $edge);
    }

    /** @test */
    public function multiple_edges_can_be_embedded_into_other_edges_deeply()
    {
        $edge_tags = new GraphEdge('tags', [], 2);

        $edge_d = new GraphEdge('d');
        $edge_c = new GraphEdge('c', [$edge_d]);
        $edge_b = new GraphEdge('b', [$edge_c, $edge_tags]);
        $edge_a = new GraphEdge('a', [$edge_b]);

        $edge_four = new GraphEdge('four', ['one', 'foo'], 4);
        $edge_three = new GraphEdge('three', [$edge_four, 'bar', $edge_a], 3);
        $edge_two = new GraphEdge('two', [$edge_three], 2);
        $edge_one = new GraphEdge('one', ['faz', $edge_two]);
        $edge = new GraphEdge('root', ['foo', 'bar', $edge_one]);

        // Expected output
        $expected_tags = 'tags.limit(2)';

        $expected_d = 'd';
        $expected_c = 'c{' . $expected_d . '}';
        $expected_b = 'b{' . $expected_c . ',' . $expected_tags . '}';
        $expected_a = 'a{' . $expected_b . '}';

        $expected_four = 'four.limit(4){one,foo}';
        $expected_three = 'three.limit(3){' . $expected_four .',bar,' . $expected_a .'}';
        $expected_two = 'two.limit(2){' . $expected_three .'}';
        $expected_one = 'one{faz,' . $expected_two .'}';
        $expected_edge = 'root{foo,bar,' . $expected_one .'}';

        $this->assertEquals($expected_edge, (string) $edge);
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

        $node = new GraphEdge('one', ['bar', 'baz', $edge_two, $edge_r_two], 1);

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
        $node = new GraphEdge('foo', ['bar', 'baz'], 1);

        $children = $node->getChildEdges();

        $this->assertEquals([['foo']], $children);
    }

    /** @test */
    public function the_node_can_be_converted_to_an_endpoint()
    {
        $node = new GraphEdge('one', ['bar', 'baz'], 1);

        $endpoints = $node->toEndpoints();

        $this->assertEquals(['/one'], $endpoints);
    }

    /** @test */
    public function the_node_can_be_converted_to_an_endpoint_with_embedded_endpoints()
    {
        $edge_four = new GraphEdge('four', ['bla'], 4);
        $edge_three = new GraphEdge('three', ['faz', 'boo', $edge_four], 3);
        $edge_two = new GraphEdge('two', ['faz', 'boo', $edge_three], 2);
        $node = new GraphEdge('one', ['bar', 'baz', $edge_two], 1);

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
        $node = new GraphEdge('one', [$edge_a, 'bar', 'baz', $edge_two, 'foo'], 1);

        $endpoints = $node->toEndpoints();

        $expected = [
            '/one/a/b/c/d',
            '/one/a/b/tags',
            '/one/two/three/four',
        ];

        $this->assertEquals($expected, $endpoints);
    }
}
