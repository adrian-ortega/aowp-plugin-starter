<?php

namespace AOD\Plugin\Http\Controllers;

use WP_REST_Server as Server;

class ExampleController extends AbstractController
{
    protected $api_routes = [
        'index' => [
            'method' => [
                Server::READABLE
            ],
            'args' => [

            ]
        ]
    ];

    public function index()
    {
        return [
            'data' => [
                'test'
            ]
        ];
    }
}
