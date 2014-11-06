<?php

use Mockery as m;
use SammyK\FacebookQueryBuilder\FQB;

class FQBTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \SammyK\FacebookQueryBuilder\FQB
     */
    protected $fqb;

    public function setUp()
    {
        $this->fqb = new FQB([
            'app_id' => 'foo',
            'app_secret' => 'bar',
        ]);
    }

    public function tearDown()
    {
        m::close();
    }

    /** @test */
    public function a_node_can_be_instantiated_magically()
    {
        $fqb = $this->fqb->node('foo');

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\FQB', $fqb);
        $this->assertEquals('/foo', $fqb->asUrl());
    }

    /** @test */
    public function a_node_name_and_fields_and_limit_can_be_set_properly()
    {
        $fqb = $this->fqb
            ->node('foo')
            ->fields(['foo', 'bar'])
            ->limit(2);

        $this->assertEquals('/foo?limit=2&fields=foo,bar', $fqb->asUrl());
    }

    /** @test */
    public function the_fields_method_is_aliased_to_the_node_with_a_key_and_value_as_the_arguments()
    {
        $fqb = $this->fqb
            ->node('foo')
            ->fields('foo', 'bar');

        $this->assertEquals('/foo?fields=foo,bar', $fqb->asUrl());
    }

    /** @test */
    public function a_new_edge_can_be_instantiated_magically()
    {
        $edge = $this->fqb->edge('foo');

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\GraphEdge', $edge);
    }

    /** @test */
    public function a_new_edge_with_fields_can_be_instantiated_magically()
    {
        $edge = $this->fqb
            ->edge('foo')
            ->fields(['foo', 'bar']);

        $this->assertEquals('foo{foo,bar}', $edge->asUrl());
    }

    /** @test */
    public function search_method_will_return_a_search_instance()
    {
        $fqb = $this->fqb->search('foo search');
        $fqb->prepareGraphNodeForGetRequest();
        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\FQB', $fqb);
        $this->assertEquals('/search?q=foo+search', $fqb->asUrl());

        $fqb2 = $this->fqb->search('foo search', 'bar');
        $fqb2->prepareGraphNodeForGetRequest();
        $this->assertEquals('/search?q=foo+search&type=bar', $fqb2->asUrl());
    }
}
