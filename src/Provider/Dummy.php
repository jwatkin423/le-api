<?php
namespace Adrenalads\CommerceApi\Provider;

namespace Adrenalads\CommerceApi\Offer;
namespace Adrenalads\CommerceApi\Product;
namespace Adrenalads\CommerceApi\Rating;
use Illuminate\Pagination\LengthAwarePaginator;

class Dummy extends Base
{

    protected $_products;

    protected $_categories;

    public function __construct()
    {
        parent::__construct(["rpp" => 2]);

        $this->_categories = [
            [
                "id" => 1,
                "title" => "Category 1",
                "children" => [
                    ["id" => 11, "title" => "Subcategory 1"],
                    ["id" => 12, "title" => "Subcategory 2"],
                    ["id" => 13, "title" => "Subcategory 3"]
                ]
            ],
            [
                "id" => 2,
                "title" => "Category 2",
                "children" => [
                    ["id" => 21, "title" => "Subcategory 1"],
                    ["id" => 22, "title" => "Subcategory 2"],
                ]
            ]
        ];
        $this->_products = [
            new Product([
                "id" => 1,
                "title" => "Product 1",
                "image" => "http://d3vv6xw699rjh3.cloudfront.net/964abd-751269583_1_160.jpg",
                "price" => 300,
                "category" => 1,
                "sub_category" => 11,
                "manufacturer" => "Apple",
                "upc" => 12312312524,
                "offers" => [
                    new Offer([
                        "retailer_logo" => "http://cfsi.pgcdn.com/images/retbutton_1636.gif",
                        "retailer_text" => "DrsFosterSmith.com",
                        "retailer_url" => "http://drsfostersmith.com",
                        "price" => 300.5,
                        "availability" => true,
                        "quality" => "New",
                        "url" => "http://drsfostersmith.com/product/url",
                        "rating" => new Rating([
                            "reviews_count" => 75,
                            "raw_score" => 4.6,
                            "score" => 9,
                            "url" => "http://foobar.com/reviews"
                        ])
                    ]),
                    new Offer([
                        "retailer_text" => "LuckyVitamin.com",
                        "retailer_url" => "http://luckyvitamin.com",
                        "price" => 304.2,
                        "availability" => true,
                        "quality" => "New",
                        "url" => "http://luckyvitamin.com/product/url"
                    ])
                ]
            ]),
            new Product([
                "id" => 2,
                "title" => "Product 2",
                "price" => 20.5,
                "category" => 1,
                "sub_category" => 12
            ]),
            new Product([
                "id" => 3,
                "title" => "Product 3",
                "image" => "http://d3vv6xw699rjh3.cloudfront.net/6194ec-914000578_1_160.jpg",
                "price" => 26.5,
                "category" => 2,
                "sub_category" => 21
            ]),
            new Product([
                "id" => 4,
                "title" => "Product 4",
                "price" => 490.1,
                "category" => 1,
                "sub_category" => 12
            ]),
            new Product([
                "id" => 5,
                "title" => "Product 5",
                "price" => 790.5,
                "category" => 2,
                "sub_category" => 21
            ])
        ];

    }

    public function getCategories($args = null)
    {
        return $this->_categories;
    }

    public function getCategory($category, $page = null)
    {
        $products = $this->_products;
        if (isset($category[0])) {
            $products = array_filter($products, function ($p) use ($category) {
                return $p->getCategory() == $category[0];
            });
        }
        if (isset($category[1])) {
            $products = array_filter($products, function ($p) use ($category) {
                return $p->getSubCategory() == $category[1];
            });
        }

        return $this->returnProducts($products, $page);
    }

    public function returnProducts($products, $page)
    {
        if (!$page) {
            $page = 1;
        }
        $products = array_values($products);
        $total = count($products);
        $items = array_slice($products, ($this->getRPP() * ($page - 1)), $this->getRPP());
        return new LengthAwarePaginator($items, $total, $this->getRPP(), $page);
    }

    public function search($keyword, $page = null)
    {
        $products = array_filter($this->_products, function ($p) use ($keyword) {
            return strstr($p->getTitle(), $keyword);
        });
        return $this->returnProducts($products, $page);
    }

    public function getProduct($product_id)
    {
        for ($i = 0; $i < count($this->_products); $i++) {
            if ($this->_products[$i]->getID() == $product_id) {
                return $this->_products[$i];
            }
        }
    }

}
