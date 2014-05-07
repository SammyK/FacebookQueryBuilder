<?php

use SammyK\FacebookQueryBuilder\Edge;

class EdgeTest extends PHPUnit_Framework_TestCase
{
    public function testOnlyNeedsEdgeNameToInstantiate()
    {
        $edge = new Edge('foo');

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\Edge', $edge);
    }

    public function testLimitSetter()
    {
        $edge = new Edge('foo');
        $edge->limit(5);

        $this->assertEquals(5, $edge->limit);
    }

    public function testFieldsSetterFromArray()
    {
        $edge = new Edge('foo');
        $edge->fields(['bar', 'baz']);

        $this->assertEquals(['bar', 'baz'], $edge->fields);
    }

    public function testFieldsSetterFromArguments()
    {
        $edge = new Edge('foo');
        $edge->fields('bar', 'baz');

        $this->assertEquals(['bar', 'baz'], $edge->fields);
    }

    public function testMergesExistingFields()
    {
        $edge = new Edge('foo', ['foo', 'bar']);
        $edge->fields('baz');

        $this->assertEquals(['foo', 'bar', 'baz'], $edge->fields);
    }

    public function testConvertsSubEdgeToString()
    {
        $edge = new Edge('foo');

        $this->assertEquals('foo', (string) $edge);
    }

    public function testConvertsSubEdgeWithFieldsToString()
    {
        $edge_one = new Edge('foo', ['bar']);
        $edge_two = new Edge('foo', ['bar', 'baz']);

        $this->assertEquals('foo.fields(bar)', (string) $edge_one);
        $this->assertEquals('foo.fields(bar,baz)', (string) $edge_two);
    }

    public function testConvertsSubEdgeWithFieldsAndLimitToString()
    {
        $edge = new Edge('foo', ['bar', 'baz'], 3);

        $this->assertEquals('foo.limit(3).fields(bar,baz)', (string) $edge);
    }

    public function testCanNestOtherEdges()
    {
        $edge_to_embed = new Edge('embeds', ['faz', 'boo'], 6);
        $edge = new Edge('foo', ['bar', 'baz', $edge_to_embed], 3);

        $this->assertEquals('foo.limit(3).fields(bar,baz,embeds.limit(6).fields(faz,boo))', (string) $edge);
    }

    public function testCanDeeplyNestOtherEdges()
    {
        $edge_level_one = new Edge('level_one', ['one', 'foo'], 1);
        $edge_level_two = new Edge('level_two', ['two', 'bar', $edge_level_one], 2);
        $edge_level_three = new Edge('level_three', ['three', 'baz', $edge_level_two], 3);
        $edge_level_four = new Edge('level_four', ['four', 'faz', $edge_level_three], 4);
        $edge = new Edge('root', ['foo', 'bar', $edge_level_four], 5);

        $expected_one = 'level_one.limit(1).fields(one,foo)';
        $expected_two = 'level_two.limit(2).fields(two,bar,' . $expected_one .')';
        $expected_three = 'level_three.limit(3).fields(three,baz,' . $expected_two .')';
        $expected_four = 'level_four.limit(4).fields(four,faz,' . $expected_three .')';
        $expected_edge = 'root.limit(5).fields(foo,bar,' . $expected_four .')';

        $this->assertEquals($expected_edge, (string) $edge);
    }

    public function testCanDeeplyNestMultipleOtherEdges()
    {
        $edge_tags = new Edge('tags', [], 2);

        $edge_d = new Edge('d');
        $edge_c = new Edge('c', [$edge_d]);
        $edge_b = new Edge('b', [$edge_c, $edge_tags]);
        $edge_a = new Edge('a', [$edge_b]);

        $edge_four = new Edge('four', ['one', 'foo'], 4);
        $edge_three = new Edge('three', [$edge_four, 'bar', $edge_a], 3);
        $edge_two = new Edge('two', [$edge_three], 2);
        $edge_one = new Edge('one', ['faz', $edge_two]);
        $edge = new Edge('root', ['foo', 'bar', $edge_one]);

        // Expected output
        $expected_tags = 'tags.limit(2)';

        $expected_d = 'd';
        $expected_c = 'c.fields(' . $expected_d . ')';
        $expected_b = 'b.fields(' . $expected_c . ',' . $expected_tags . ')';
        $expected_a = 'a.fields(' . $expected_b . ')';

        $expected_four = 'four.limit(4).fields(one,foo)';
        $expected_three = 'three.limit(3).fields(' . $expected_four .',bar,' . $expected_a .')';
        $expected_two = 'two.limit(2).fields(' . $expected_three .')';
        $expected_one = 'one.fields(faz,' . $expected_two .')';
        $expected_edge = 'root.fields(foo,bar,' . $expected_one .')';

        $this->assertEquals($expected_edge, (string) $edge);
    }
}
