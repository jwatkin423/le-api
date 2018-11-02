<?php
namespace Adrenalads\CommerceApi\Provider;

use Adrenalads\CommerceApi\Offer;
use Adrenalads\CommerceApi\Product;
use Adrenalads\CommerceApi\Rating;
use Illuminate\Pagination\LengthAwarePaginator;
use GuzzleHttp;

class NexTag extends Base
{

    protected $_options;

    public function __construct($options)
    {
        $default_options = [
            "token" => "AgDVCKsLNnOgL04HpBKp",
            'url' => "http://api.nextag.com/rest/v1/",
        ];

        parent::__construct(array_merge($default_options, $options));
    }

    public function getCategories($args = null)
    {
        $result = $this->makeQuery('products/', [
            'node' => 100,
            'perpage' => 40,
            'prod_sort' => $this->getSortBy()
        ]);

        $categories = array_get($result, 'search-results.categories', []);
        return $this->processCategories($categories, function ($c) {
            return $this->processChildCategories($c);
        });
    }

    protected function makeQuery($url = '', $params = [])
    {
        $url = $this->_options['url'] . $url;
        $params['token'] = $this->_options['token'];

        $client = new GuzzleHttp\Client();
        $results = $client->get($url, ['query' => $params]);

        return json_decode($results->getBody(), true);
    }

    protected function processChildCategories($category)
    {
        $children = [];

        foreach (@$category['children'] ?: [] as $child) {
            $children[] = [
                'id' => $child['value'],
                'title' => $child['display-name'],
                'children' => []
            ];
        }
        $category['children'] = $children;
        return $category;
    }

    protected function processCategories($json_categories, $callback = null)
    {
        $categories = [];
        foreach ($json_categories as $c) {
            $category = [
                'id' => $c['category'][0]['value'],
                'title' => $c['category'][0]['display-name'],
                'children' => @$c['category'][0]['category'] ?: []
            ];

            if (is_callable($callback)) {
                $category = $callback($category);
            }

            $categories[] = $category;
        }
        return $categories;
    }

    public function getCategory($category = 100, $page = 1)
    {
        $params = [
            'perpage' => $this->getRPP(),
            'prod_sort' => $this->getSortBy(),
            //pages start from zero on this api
            'page' => $page - 1,
            'node' => $category
        ];

        if (is_array($category)) {
            $params['node'] = (integer)array_pop($category);
        }

        $results = $this->makeQuery('products/', $params)['search-results'];
        if ($results['product-result-total'] == 0) {
            $results = $this->makeQuery('products/' . urlencode(' '), $params)['search-results'];
        }
        return $this->buildPaginatedResults($results, $this->getRPP(), $page);
    }

    protected function getPrice($source)
    {
        if (!$source) {
            return null;
        }
        $price = (string)$source;
        $price = floatval(str_replace(['$', ','], '', $price));

        return $price;
    }

    protected function getRating($offer)
    {
        return new Rating([
            "url" => @$offer['seller']['seller-review-url'] ?: '',
            "raw_score" => 0,
            "score" => 0,
            "reviews_count" => 0
        ]);
    }

    public function search($keyword, $page = 1)
    {
        $params = [
            'page' => $page - 1,
            'prod_sort' => $this->getSortBy()
        ];
        $results = $this->makeQuery("products/{$keyword}", $params)['search-results'];
        return $this->buildPaginatedResults($results, $this->getRPP(), $page);
    }

    public function getProduct($ptitle_id)
    {
        $results = $this->makeQuery("comparePrices/product/{$ptitle_id}", ['comp' => 'sellers']);
        if (is_null($results)) {
            abort(404);
        }

        $product = $results['product-results'][0]['product'];

        return $this->makeProduct($product);
    }


    protected function makeProduct($product)
    {
        $p = [
            'id' => $product['ptitle-id'],
            'title' => $product['name'],
            'price' => $this->getPrice(array_get($product, 'low-price', 0)),
            'image' => array_get($product, 'has-xl-image', array_get($product, 'product-xlarge-image-url', array_get($product, 'product-image-url', ''))),
            'category' => (integer)array_get($product, 'product-category.category.value', 100),
            'sub_category' => (integer)array_get($product, 'product-category.category.category.value', 100),
            'manufacturer' => array_get($product, 'manufacturer-name', 'Not Available'),
            'partnum' => array_get($product, 'mfr-part-no', 'Not Available'),
            'upc' => (string)array_get($product, 'product-details.product-detail.0.value', 'Not Specified'),
            'offers' => []
        ];

        $sellers = array_get($product, 'sellers-found', 0);
        if ($sellers > 0) {
            foreach (array_get($product, 'tags.tag', []) as $tag) {
                array_push($p['offers'], $this->makeOfferFromTag($tag));
            }
        } else {
            array_push($p['offers'], new Offer([
                'url' => array_get($product, 'detail-url', ''),
                'price' => $this->getPrice(array_get($product, 'low-price', null)),
                'retailer_logo' => '',
                'retailer_text' => array_get($product, 'manufacturer-name', ''),
                'rating' => null
            ]));
        }

        return new Product($p);
    }

    protected function makeProductFromSearch($product)
    {
        $p = [
            'id' => $product['ptitle-id'],
            'title' => $product['name'],
            'price' => $this->getPrice(array_get($product, 'low-price', 0)),
            'image' => array_get($product, 'image-large-url', ''),
            'category' => (integer)array_get($product, 'categories.category.0.node-id', 100),
            'sub_category' => (integer)array_get($product, 'categories.category.0.node-id', 100),
            'manufacturer' => array_get($product, 'manufacturer.mfr-name', 'Not Available'),
            'partnum' => array_get($product, 'mfr-part-no', 'Not Available'),
            'upc' => (string)array_get($product, 'product-details.product-detail.0.value', 'Not Specified'),
            'offers' => []
        ];

        $sellers = array_get($product, 'sellers-found', 0);
        if ($sellers > 0) {
            foreach (array_get($product, 'featured-tags.tag', []) as $tag) {
                array_push($p['offers'], $this->makeOfferFromTag($tag));
            }
        } else {
            array_push($p['offers'], new Offer([
                'url' => array_get($product, 'url', ''),
                'price' => $this->getPrice(array_get($product, 'low-price', null)),
                'retailer_logo' => '',
                'retailer_text' => array_get($product, 'manufacturer-name', ''),
                'rating' => null
            ]));
        }

        return new Product($p);
    }


    protected function makeOfferFromTag($tag)
    {
        return new Offer([
            'url' => $tag['click-url'],
            'price' => $this->getPrice($tag['price']),
            'retailer_logo' => array_get($tag, 'seller.logo-url', ''),
            'retailer_text' => array_get($tag, 'seller.seller-name'),
            "available" => ucfirst(array_get($tag, 'is-availabile', '')),
            'rating' => new Rating([
                "url" => array_get($tag, 'seller.seller-review-url', ''),
                "raw_score" => 0,
                "score" => 0,
                "reviews_count" => 0
            ])
        ]);
    }


    public function getOffer($offer_id)
    {
        return null;
    }

    public function getSortOptions()
    {
        return [
            '1' => 'Price Ascending',
            '12' => 'Price Descending',
            '10' => 'Rating',
            '5' => 'Popularity',
            '14' => 'Best Selling',
            '0' => 'Relevance'
        ];
    }

    public function getDefaultSort()
    {
        return '0';
    }

    protected function buildPaginatedResults($results, $perpage, $page)
    {
        $products = [];
        $total = $results['product-result-total'];

        foreach ($results['products']['product'] as $product) {
            array_push($products, $this->makeProductFromSearch($product));
        }

        return new LengthAwarePaginator($products, $total, $perpage, $page);
    }
}