<?php
namespace Adrenalads\CommerceApi\Provider;

use Adrenalads\CommerceApi\Offer;
use Adrenalads\CommerceApi\Product;
use Adrenalads\CommerceApi\Rating;
use GuzzleHttp\TransferStats;
use GuzzleHttp;
use Illuminate\Pagination\LengthAwarePaginator;

class Connexity extends Base
{

    protected $_options;

    public function __construct($options)
    {
        $default_options = [
            "api_key" => null,
            "publisher_id" => null,
            "url" => "http://catalog.bizrate.com/services/catalog/v1/us",
            "featuredTerms" => ['electronics'],
            "featuredCount" => 30
        ];
        parent::__construct(array_merge($default_options, $options));
    }

    public function getCategories($args = null)
    {
        $xml = $this->makeQuery('taxonomy', []);
        $categories = [];

        if (!is_array($xml)) {
            $xml_categories = $xml->Taxonomy->Categories->Category->Children->Category;
            $categories = $this->processCategories($xml_categories, function ($c, $xml) {
                $c["children"] = $this->processCategories($xml->Children->Category);
                return $c;
            });

        }

        return $categories;
    }

    protected function makeQuery($endpoint, $params)
    {
        $params = array_merge($this->buildBasicParams(), $params);
        $url = $this->_options["url"] . '/' . $endpoint;

        try {
            $client = new GuzzleHttp\Client();
            $response = $client->get($url, ["query" => $params]);

            $xml = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA);
        } catch (GuzzleHttp\Exception\RequestException $exception) {

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


        return $xml;
    }

    protected function buildBasicParams()
    {
        return [
            "apiKey" => $this->_options['api_key'],
            "publisherId" => $this->_options['publisher_id']
        ];
    }

    protected function processCategories($xml_categories, $callback = null)
    {
        $categories = [];
        foreach ($xml_categories as $c) {
            $category = [
                "id" => (string)$c['id'],
                "title" => (string)$c->name,
                "children" => []
            ];

            if (is_callable($callback)) {
                $category = $callback($category, $c);
            }

            $categories[] = $category;
        }

        return $categories;
    }

    public function getCategory($category, $page = null)
    {
        $id = array_pop($category);
        if (!$id) {
            return $this->getFeaturedProducts();
        } else {
            return $this->makeCategoryQuery([
                                                "categoryId" => $id
                                            ], $page);
        }
    }

    public function getFeaturedProducts($args = null)
    {
        $products = [];


        foreach ($this->_options['featuredTerms'] as $term) {
            $MakeCategoryQuery = $this->makeCategoryQuery([
                                                              "keyword" => $term,
                                                              'imageOnly' => true,
                                                              'freeShipping' => true
                                                          ], 1);


            if ($MakeCategoryQuery) {
                $termProducts = $MakeCategoryQuery->items();
                $products = array_merge($products, $termProducts);
            }
        }

        if (count($products) > 0) {

            shuffle($products);
            $products = array_slice($products, 0, $this->_options['featuredCount']);
            //prevent division by zero in paginator if products is empty
            $perPage = count($products) ?: 1;
            return new LengthAwarePaginator($products, count($products), $perPage, 1);
        }

        return $products;
    }

    protected function makeCategoryQuery($params, $page)
    {
        $total = 0;
        if (!$page) {
            $page = 1;
        }
        $start = ($page - 1) * $this->getRPP();
        $params["results"] = $this->getRPP();
        $params["start"] = $start;
        $params["sort"] = $this->getSortBy();

        $xml = $this->makeQuery('product', $params);
        $products = [];

        if(!is_array($xml)) {
            $total = intval($xml->Products['totalResults']);


            foreach ($xml->Products->Offer as $product) {
                $products[] = $this->processProduct($product, 'offer');
            }

            foreach ($xml->Products->Product as $product) {
                $products[] = $this->processProduct($product, 'product');
            }

            return new LengthAwarePaginator($products, $total, $this->getRPP(), $page);
        }

        return false;
    }

    protected function processProduct($product, $type = "product")
    {
        if (!$product) {
            return null;
        }
        $offers = [];
        if ($type == "offer") {
            $offers = [$this->createOffer($product)];
        }
        if (isset($product->Offers) && isset($product->Offers->Offer)) {
            foreach ($product->Offers->Offer as $offer) {
                $offers[] = $this->createOffer($offer);
            }
        }
        return new Product([
                               "id" => (string)$product['id'],
                               "title" => (string)$product->title,
                               "price" => $this->getPrice($product, $type),
                               "image" => $this->getImage($product->Images->Image),
                               "category" => (integer)$product["categoryId"],
                               "manufacturer" => (string)$product->Brand->name,
                               "offers" => $offers,
                               "rating" => $this->getRating($product->Rating)
                           ]);
    }

    protected function createOffer($offer)
    {
        $price = $this->getPrice($offer, 'offer');
        return new Offer([
                             "url" => (string)$offer->url,
                             "price" => $price,
                             "retailer_logo" => (string)$offer->merchantLogoUrl,
                             "retailer_text" => (string)$offer->merchantName,
                             "available" => (string)$offer->stock,
                             "quality" => (string)$offer->condition,
                             "rating" => $this->getRating($offer->MerchantRating)
                         ]);
    }

    protected function getPrice($source, $type)
    {
        if ($type == "product") {
            $price = $source->PriceSet->minPrice["integral"];
        } else {
            $price = $source->price["integral"];
        }
        $price = intval($price) / 100;

        return $price;
    }

    protected function getRating($src)
    {
        $score = intval($src['value']);
        return new Rating([
                              "raw_score" => $score,
                              "score" => round($score * 2)
                          ]);
    }

    protected function getImage($images)
    {
        if (!$images) {
            return null;
        }
        $to_array = (array)$images;
        return (string)array_pop($to_array);
    }

    public function search($keyword, $page = null)
    {
        return $this->makeCategoryQuery([
                                            "keyword" => $keyword
                                        ], $page);
    }

    public function getProduct($product_id)
    {
        $xml = $this->makeQuery('product', [
            "productId" => $product_id,
            "productIdType" => 'SZPID',
            "reviews" => "aggregate",
            "resultsOffers" => "10"
        ]);

        return $this->processProduct($xml->Products->Product);
    }

    public function getOffer($offer_id)
    {
        $xml = $this->makeQuery('product', [
            "productId" => $offer_id,
            "productIdType" => 'SZOID',
            "reviews" => "aggregate",
            "offersOnly" => "true",
            "resultsOffers" => "10"
        ]);

        return $this->processProduct($xml->Offers->Offer, 'offer');
    }

    public function getSortOptions()
    {
        return [
            'relevancy_desc' => 'Relevancy',
            'price_asc' => 'Price A->Z',
            'price_desc' => 'Price Z->A'
        ];
    }

    public function getDefaultSort()
    {
        return 'relevancy_desc';
    }
}
