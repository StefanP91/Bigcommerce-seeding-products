# BigCommerce Catalog Seeding Script (PHP)

This repository contains a robust PHP-based seeding script designed to programmatically architect and inject a structured product catalog into a **BigCommerce** storefront via the V3 API.

## 🚀 Overview
The script automates the creation of 30 specialized products within a "Consumer Electronics" category. Each product is injected with complex technical specifications stored as **JSON Metafields**, allowing for advanced data structuring beyond standard product attributes.

## ✨ Key Features
* **Automated Bulk Seeding:** Creates 30 unique products with randomized pricing, inventory levels, and SKUs.
* **JSON Metafield Architecture:** Attaches a `technical_specs` metafield to every product, containing structured data for Power, Physical, and Connectivity attributes.
* **Rate Limit Guard (Critical):** Implements an intelligent throttling mechanism. The script monitors the `X-Rate-Limit-Requests-Left` and `X-Rate-Limit-Time-Reset-Ms` headers. If a **429 Too Many Requests** response is detected, it automatically pauses and retries, ensuring 100% completion without API bans.
* **Custom cURL Wrapper:** A flexible request handler for GET, POST, and DELETE operations with built-in OAuth authentication.

## 🛠 Tech Stack
* **Language:** PHP 8.x
* **Library:** Native cURL (no heavy dependencies)
* **API:** BigCommerce Catalog API V3

## ⚙️ Configuration
To use this script, update the following variables in `seed.php`:
```php
$store_hash   = 'your_store_hash';
$access_token = 'your_access_token';
$category_id  = 00; // Your target Category ID
