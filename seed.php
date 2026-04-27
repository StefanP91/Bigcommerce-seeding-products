<?php

// API CONFIGURATION
$store_hash   = 'ljloonmrju';
$access_token = '3mfwte1hx6ipb1ww821a4v3ed6ppy3v';
$category_id  = 26; 

$base_url = "https://api.bigcommerce.com/stores/$store_hash/v3";

function makeRequest($method, $url, $token, $data = null) {
    $ch = curl_init();
    
    $headers = [
        "X-Auth-Token: $token",
        "Content-Type: application/json",
        "Accept: application/json"
    ];

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, true); 

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers_res = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // RATE LIMIT GUARD (429 Too Many Requests)
    if ($http_code == 429) {

        if (preg_match('/X-Rate-Limit-Time-Reset-Ms: (\d+)/', $headers_res, $matches)) {
            $waitTime = (int)$matches[1] * 1000; 
        } else {
            $waitTime = 5000000; 
        }
        
        echo "!!! Rate limit reached. Pausing for " . ($waitTime/1000000) . " seconds...\n";
        usleep($waitTime);
        return makeRequest($method, $url, $token, $data); 
    }

    curl_close($ch);
    return json_decode($body, true);
}

$metafield_value = [
    [
        "group" => "Power & Battery",
        "attributes" => [
            ["label" => "Capacity", "value" => "5000 mAh", "type" => "number"],
            ["label" => "Fast Charging", "value" => true, "type" => "boolean"],
            ["label" => "Port Type", "value" => "USB-C", "type" => "string"]
        ]
    ],
    [
        "group" => "Physical",
        "attributes" => [
            ["label" => "Weight", "value" => 185, "unit" => "grams", "type" => "number"],
            ["label" => "Water Resistance", "value" => "IP68", "type" => "string"]
        ]
    ],
    [
        "group" => "Connectivity",
        "attributes" => [
            ["label" => "Bluetooth", "value" => "5.3", "type" => "string"],
            ["label" => "5G Capable", "value" => true, "type" => "boolean"]
        ]
    ]
];

$product_names = [
    "Smartphone Alpha X", "Pro Tablet 11", "Wireless Buds Gen 2", "Smartwatch V3", 
    "Ultra Laptop Pro", "Bluetooth Speaker Mini", "Gaming Mouse RGB", "Mechanical Keyboard",
    "4K Monitor 27 inch", "Powerbank 20000mAh", "USB-C Hub Multiport", "VR Headset Lite",
    "Smart Home Camera", "eReader PaperTouch", "DLP Projector X1", "Noise Cancelling Headphones",
    "Graphic Tablet Pro", "Compact Point-and-Shoot", "Fitness Tracker Z", "Portable SSD 1TB",
    "Mesh WiFi Router", "Electric Screwdriver", "Action Cam 4K", "Studio Microphone",
    "Smart Plug WiFi", "LED Desk Lamp Pro", "External Sound Card", "Webcam 1080p Plus",
    "Cordless Vacuum Pro", "Digital Photo Frame"
];

echo "--- Starting Catalog Seeding (30 Products) ---\n";

for ($i = 0; $i < 30; $i++) {
    $current_name = $product_names[$i];
    
    $product_payload = [
        "name" => $current_name,
        "type" => "physical",
        "weight" => rand(1, 5),
        "price" => rand(49, 1299),
        "categories" => [$category_id],
        "is_visible" => true,
        "inventory_level" => rand(10, 100),
        "sku" => "CE-" . str_pad($i + 1, 4, '0', STR_PAD_LEFT)
    ];

    $res = makeRequest("POST", "$base_url/catalog/products", $access_token, $product_payload);

    if (isset($res['data']['id'])) {
        $p_id = $res['data']['id'];
        echo "SUCCESS: Created '$current_name' (ID: $p_id). ";

        $meta_payload = [
            "permission_set" => "read_and_sf_access",
            "namespace" => "Technical Data",
            "key" => "technical_specs",
            "value" => json_encode($metafield_value),
            "description" => "Seeded technical specifications JSON"
        ];

        makeRequest("POST", "$base_url/catalog/products/$p_id/metafields", $access_token, $meta_payload);
        echo "Metafield injected.\n";
    } else {
        echo "ERROR: Failed to create product at index $i. Response: " . json_encode($res) . "\n";
    }
}

echo "--- Seeding Complete! 30 Products processed. ---\n";