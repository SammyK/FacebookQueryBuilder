<?php

use SammyK\FacebookQueryBuilder\Response;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    protected $data_success_single = [
        'id' => '100',
        'name' => 'Foo Barman',
        'email' => 'foo@bar.com',
    ];

    protected $data_success_multi = [
        'data' => [
            [
                'id' => '123',
                'name' => 'Foo Dobler',
            ],
            [
                'id' => '456',
                'name' => 'Bill Gant',
            ],
            [
                'id' => '789',
                'name' => 'Joe Bar',
            ],
        ],
        'paging' => [
            'previous' => 'http://foo_previous/',
            'next' => 'http://foo_next/',
        ],
    ];

    public function testGetRawResponseData()
    {
        $res = new Response($this->data_success_single);

        $this->assertEquals($this->data_success_single, $res->getRawResponse());
    }

    public function testParsesSuccessfulSingleResponse()
    {
        $res = new Response($this->data_success_single);
        $res = $res->getResponse();

        $this->assertEquals('100', $res['id']);
        $this->assertEquals('Foo Barman', $res['name']);
    }

    public function testParsesSuccessfulMultiResponse()
    {
        $res = new Response($this->data_success_multi);
        $res = $res->getResponse();

        $this->assertEquals('123', $res[0]['id']);
        $this->assertEquals('Bill Gant', $res[1]['name']);
    }

    /*
    public function testGetsNextSetOfDataFromFacebook()
    {
        $res = new Response($this->data_success_multi);

        $data = $res->next();

        $this->assertEquals('foo_data', $data);
    }
    */
}
