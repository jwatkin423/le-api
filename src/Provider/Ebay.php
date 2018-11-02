<?php
namespace Adrenalads\CommerceApi\Provider;

use Adrenalads\CommerceApi\Offer;
use Adrenalads\CommerceApi\Product;
use Adrenalads\CommerceApi\Rating;
use Illuminate\Pagination\LengthAwarePaginator;
use GuzzleHttp\TransferStats;
use GuzzleHttp;
use Log;

class Ebay extends Base
{

    protected $liveApi = "http://api.ebaycommercenetwork.com/publisher/3.0/json";
    protected $testApi = "http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/json";
    protected $apiUrl = '';
    protected $_options = [];
    private $categories;

    public function __construct($options)
    {
        $env = config('app.env', 'production');

        if ($env == 'production') {
            $this->apiUrl = $this->liveApi;
        } else {
            $this->apiUrl = $this->testApi;
        }

        $options['visitorUserAgent'] = request()->header('User-Agent');
        $options['visitorIPAddress'] = request()->ip();

        parent::__construct($options);
    }

    /**
     * Get all categories
     */
    public function getCategories($args = null)
    {
        $categories = $this->sendRequest('CategoryTree', ['showAllDescendants' => true]);
        return $this->processCategories(array_get($categories, 'category.categories.category'));
    }


    protected function processCategories($categories)
    {
        $processed = [];
        if ($categories) {
            foreach ($categories as $category) {
                array_push($processed, [
                    'title' => array_get($category, 'name', ''),
                    'id' => array_get($category, 'id', 0),
                    'children' => $this->processChildCategories($category)
                ]);
            }


        }
        $this->categories = $processed;
        return $processed;
    }

    protected function processChildCategories($category)
    {
        $children = [];
        foreach (array_get($category, 'categories.category', []) as $child) {
            array_push($children, [
                'id' => array_get($child, 'id'),
                'title' => array_get($child, 'name'),
                'children' => $this->processChildCategories($child)
            ]);
        }

        return $children;
    }

    /**
     * Get products from the category
     */
    public function getCategory($category, $page = 1)
    {
        if (is_array($category)) {
            $category = end($category);
        }

        $params = [
            'categoryId' => $category,
            'pageNumber' => $page,
            'showProductOffers' => true,
            'showProductSpecs' => true,
            'showProductReviews' => true,
            'itemsSortType' => $this->getSortBy(),
            'itemsSortOrder' => 'asc'
        ];
        $products = $this->sendRequest('GeneralSearch', $params);
        return $this->paginateProducts(array_get($products, 'categories.category.0.items'), $this->getRPP(), $page);
    }

    /**
     * Get specific product info
     */
    public function getProduct($product_id)
    {
        $params = [
            'showProductOffers' => true,
            'showProductSpecs' => true,
            'showProductReviews' => true,
        ];

        //check if product id matches that of an offer
        if (preg_match("/[a-zA-Z]+/", $product_id)) {
            $params['offerId'] = $product_id;
            $product = $this->sendRequest('GeneralSearch', $params);
            return $this->buildProductFromOffer(array_get($product, 'categories.category.0.items.item.0.offer'));
        } else {
            $params['productId'] = $product_id;
            $product = $this->sendRequest('GeneralSearch', $params);
            if (isset($product['messages'])) {

                return false;
            }
            return $this->buildProduct(array_get($product, 'categories.category.0.items.item.0.product'));
        }
    }


    public function search($keyword, $page = 1)
    {
        $params = [
            'keyword' => $keyword,
            'itemsSortType' => $this->getSortBy(),
            'itemsSortOrder' => 'asc',
            'pageNumber' => $page,

        ];
        $results = $this->sendRequest("GeneralSearch", $params);
        return $this->paginateProducts(array_get($results, 'categories.category.0.items'), $this->getRPP(), $page);
    }


    public function getFeaturedProducts($args = null)
    {
        //just get products from a random category
        $cats = [];

        if ($this->categories) {
            foreach ($this->categories as $c) {
                array_push($cats, $c['id']);
                foreach ($c['children'] as $child) {
                    array_push($cats, $child['id']);
                }
            }

            return $this->getCategory($cats[rand(0, count($cats) - 1)]);
        }

        return [];
    }

    public function paginateProducts($items, $per_page, $current_page)
    {
        $products = [];
        foreach (array_get($items, 'item', []) as $item) {
            $type = key($item);
            if ($type == 'product') {
                array_push($products, $this->buildProduct(array_get($item, 'product', [])));
            } else if ($type == 'offer') {
                array_push($products, $this->buildProductFromOffer(array_get($item, 'offer', [])));
            }
        }

        return new LengthAwarePaginator($products, array_get($items, 'matchedItemCount', 0), $per_page, $current_page);
    }


    protected function buildProduct($product)
    {
        $p = new Product([
                             'id' => $product['id'],
                             'title' => $product['name'],
                             'price' => array_get($product, 'minPrice.value', ''),
                             'upc' => array_get($product, 'upcs.upc.0', ''),
                             'image' => array_get($product, 'images.image.0.sourceURL', ''),
                             'category' => array_get($product, 'categoryId', ''),
                             'sub_category' => array_get($product, 'categoryId', ''),
                             'manufacturer' => array_get($product, 'manufacturer', 'Not Available')
                         ]);

        $offers = [];
        foreach (array_get($product, 'offers.offer', []) as $offer) {
            array_push($offers, $this->buildOffer($offer));
        }

        $p->setOffers($offers);

        return $p;
    }

    protected function buildProductFromOffer($offer)
    {
        $p = new Product([
                             'id' => $offer['id'],
                             'title' => $offer['name'],
                             'price' => array_get($offer, 'basePrice.value', ''),
                             'upc' => array_get($offer, 'upc', ''),
                             'image' => $this->getOfferImage($offer),
                             'category' => $offer['categoryId'],
                             'sub_category' => $offer['categoryId'],
                             'manufacturer' => array_get($offer, 'manufacturer', 'Not Available'),
                             "rating" => new Rating([
                                                        "url" => array_get($offer, 'store.ratingInfo.reviewURL', ''),
                                                        "raw_score" => array_get($offer, 'store.ratingInfo.rating', ''),
                                                        "score" => array_get($offer, 'store.ratingInfo.rating', ''),
                                                        "reviews_count" => array_get($offer, 'store.ratingInfo.reviewCount', ''),
                                                    ])
                         ]);

        $p->setOffers([$this->buildOffer($offer)]);

        return $p;
    }

    protected function buildOffer($offer)
    {
        return new Offer([
                             "url" => array_get($offer, 'offerURL', ''),
                             "price" => array_get($offer, 'originalPrice.value', ''),
                             "retailer_logo" => array_get($offer, 'store.logo.sourceURL', ''),
                             "retailer_text" => array_get($offer, 'store.name', ''),
                             "available" => array_get($offer, 'stockStatus', '') == 'in-stock' ? 'Yes' : 'No',
                             "rating" => new Rating([
                                                        "url" => array_get($offer, 'store.ratingInfo.reviewURL', ''),
                                                        "raw_score" => array_get($offer, 'store.ratingInfo.rating', ''),
                                                        "score" => array_get($offer, 'store.ratingInfo.rating', ''),
                                                        "reviews_count" => array_get($offer, 'store.ratingInfo.reviewCount', ''),
                                                    ])
                         ]);
    }

    protected function getOfferImage($offer)
    {
        $images = array_get($offer, 'imageList.image', []);
        return array_get($images[0], 'sourceURL', '');
    }


    protected function sendRequest($url = '', $params = [])
    {
        $url = $this->makeUrl($url);
        $params['numItems'] = $this->getRPP();


        try {

            $client = new GuzzleHttp\Client();
            $response = $client->get($url, [
                'query' => array_merge($this->getOptions(), $params),
                'on_stats' => function(TransferStats $stats) use (&$url) {
                    Log::info($stats->getEffectiveUri());
                }
            ]);
        } catch (RequestException $exception) {
            $code = $exception->getCode();
            $message = $exception->getMessage();
            \Log::info ($message);

            return ['code' => $code, 'message' => $message];
        } catch (GuzzleHttp\Exception\ClientException $exception) {
            $code = $exception->getCode();
            $message = $exception->getMessage();
            \Log::info ($message);

            return ['code' => $code, 'message' => $message];
        }


        RETURN json_decode($response->getBody(), true);
    }

    protected function makeUrl($url)
    {
        $url = trim($url);
        if (substr($url, 0, 1) == '/') {
            return $this->apiUrl . $url;
        }

        return $this->apiUrl . '/' . $url;
    }


    public function getSortOptions()
    {
        return [
            'price' => 'Price',
            'store-name' => 'Store Name',
            'store-rating' => 'Store Rating',
            'product-rating' => 'Product Rating',
            'relevance' => 'Relevance',
            'yield' => 'Yield',
            'publisher-revenue' => 'Publisher Revenue',
            'sdc-revenue' => 'SDC Revenue'
        ];
    }

    public function getDefaultSort()
    {
        return 'yield';
    }
}
