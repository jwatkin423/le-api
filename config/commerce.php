<?php

// Path to the commerce API providers
$client_path = "\\Adrenalads\\CommerceApi\\Provider";

return [

    "api_clients" => [
        "localhost" => [
            "skin" => "hyfind",
            "cache" => false,
            "client" => "{$client_path}\\Dummy",
            "options" => []
        ],
        "ecn_de" => [
            "skin" => "hyfind",
            "client" => "{$client_path}\\Ebay",
            "cache" => false,
            "options" => [
                "apiKey" => "59a591f9-110b-41f2-8c5e-849769c821c4",
                "trackingId" => "8100475"
            ]
        ],
        "ecn_us" => [
            "skin" => "hyfind",
            "client" => "{$client_path}\\Ebay",
            "cache" => false,
            "options" => [
                "apiKey" => "7450452f-ee95-4589-bdc6-eda4ed7772e6",
                "trackingId" => "8099539"
            ]

        ],
        "ecn_fr" => [
            "skin" => "hyfind",
            "client" => "{$client_path}\\Ebay",
            "cache" => false,
            "options" => [
                "apiKey" => "1a096245-b336-428f-afd7-419021f4ebe4",
                "trackingId" => "8111156"
            ]
        ],
        "ecn_gb" => [
            "skin" => "hyfind",
            "client" => "{$client_path}\\Ebay",
            "cache" => false,
            "options" => [
                    "apiKey" => "4c3f0659-7157-463f-be76-e81b4e3b8a86",
                    "trackingId" => "8111138"
            ]
        ],
        "ecn2_us" => [
            "skin" => "hyfind",
            "client" => "{$client_path}\\Ebay",
            "cache" => false,
            "options" => [
                "apiKey" => "003a0542-837b-420a-a928-f0a715e1730c",
                "trackingId" => "8099115"
            ]

        ],
        "ecn3_us" => [
            "skin" => "hyfind",
            "client" => "{$client_path}\\Ebay",
            "cache" => false,
            "options" => [
                "apiKey" => "af64481e-3775-4d3a-8062-54ee5c77a02f",
                "trackingId" => "8113839"
            ]

        ],
        "ecn3_fr" => [
            "skin" => "hyfind",
            "client" => "{$client_path}\\Ebay",
            "cache" => false,
            "options" => [
                "apiKey" => "1a096245-b336-428f-afd7-419021f4ebe4",
                "trackingId" => "8111156"
            ]

        ],
        "ecn3_de" => [
            "skin" => "hyfind",
            "client" => "{$client_path}\\Ebay",
            "cache" => false,
            "options" => [
                "apiKey" => "59a591f9-110b-41f2-8c5e-849769c821c4",
                "trackingId" => "8100475"
            ]

        ],
        "nxt_us" => [
            "skin" => "ealeo",
            "client" => "{$client_path}\\NexTag",
            "cache" => false,
            "options" => [
                "token" => "AgDVCKsLNnOgL04HpBKp"
            ]

        ],
        "cnx_us" => [
            "skin" => "ealeo",
            "client" => "{$client_path}\\Connexity",
            "cache" => false,
            "options" => [
                "api_key" => "fd729cde9bd1878c608e4fc0692245fc",
                "publisher_id" => "607405",
                "featuredTerms" => ['electronics', 'office', 'footwear', 'kitchen']
            ]
        ]
    ],

    "default_host" => env('ADV_KEY', 'cnx_us')

];
