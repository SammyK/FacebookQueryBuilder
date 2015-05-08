<?php namespace SammyK\FacebookQueryBuilderTests;

use SammyK\FacebookQueryBuilder\FQB;

class FQBTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function a_node_can_be_instantiated_magically()
    {
        $fqb = new FQB;
        $request = $fqb->node('foo');

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\FQB', $request);
        $this->assertEquals(FQB::BASE_GRAPH_URL.'/foo', $request->asUrl());
        $this->assertEquals('/foo', $request->asEndpoint());
    }

    /** @test */
    public function enabling_beta_mode_returns_the_beta_hostname()
    {
        $fqb = new FQB([
            'enable_beta_mode' => true,
        ]);
        $request = $fqb->node('bar');

        $this->assertEquals(FQB::BASE_GRAPH_URL_BETA.'/bar', $request->asUrl());
        $this->assertEquals('/bar', $request->asEndpoint());
    }

    /** @test */
    public function will_fallback_to_the_default_access_token_if_none_set_explicitly()
    {
        $fqb = new FQB([
            'default_access_token' => 'foo-token',
        ]);
        $request = $fqb->node('bar');

        $this->assertEquals('/bar?access_token=foo-token', $request->asEndpoint());
    }

    /** @test */
    public function the_fallback_access_token_can_be_overwritten()
    {
        $fqb = new FQB([
            'default_access_token' => 'foo-token',
        ]);
        $request = $fqb->node('bar')->accessToken('use-me-instead');

        $this->assertEquals('/bar?access_token=use-me-instead', $request->asEndpoint());
    }

    /** @test */
    public function when_an_app_secret_is_provided_an_app_secret_proof_will_be_generated()
    {
        $fqb = new FQB([
            'app_secret' => 'foo-secret',
            'default_access_token' => 'foo-token',
        ]);
        $request = $fqb->node('foo');

        $expected = '/foo?access_token=foo-token&appsecret_proof=3bf321559c5c870f37d36d9ea270676d1af8830edf3a7ef457b17e416a7848b2';
        $this->assertEquals($expected, $request->asEndpoint());
    }

    /** @test */
    public function the_default_graph_version_will_prefix_the_url()
    {
        $fqb = new FQB([
            'default_graph_version' => 'v133.7',
        ]);
        $request = $fqb->node('foo');

        $this->assertEquals('/v133.7/foo', $request->asEndpoint());
    }

    /** @test */
    public function all_the_alias_methods_fall_back_to_graph_node()
    {
        $fqb = new FQB;
        $request = $fqb
            ->node('foo')
            ->fields(['foo', 'bar'])
            ->modifiers(['west' => 'coast-swing'])
            ->limit(2);

        $this->assertEquals('/foo?west=coast-swing&limit=2&fields=foo,bar', $request->asEndpoint());
    }

    /** @test */
    public function the_fields_method_is_aliased_to_the_node_with_a_key_and_value_as_the_arguments()
    {
        $fqb = new FQB;
        $request = $fqb
            ->node('foo')
            ->fields('foo', 'bar');

        $this->assertEquals('/foo?fields=foo,bar', $request->asEndpoint());
    }

    /** @test */
    public function a_new_edge_can_be_instantiated_magically()
    {
        $fqb = new FQB;
        $edge = $fqb->edge('foo');

        $this->assertInstanceOf('SammyK\FacebookQueryBuilder\GraphEdge', $edge);
    }

    /** @test */
    public function a_new_edge_with_fields_can_be_instantiated_magically()
    {
        $fqb = new FQB;
        $edge = $fqb
            ->edge('foo')
            ->fields(['foo', 'bar']);

        $this->assertEquals('foo{foo,bar}', $edge->asUrl());
    }
}
