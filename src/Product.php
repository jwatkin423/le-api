<?php
namespace Adrenalads\CommerceApi;

class Product
{

    protected $id;
    protected $title;
    protected $price;
    protected $image;
    protected $category;
    protected $sub_category;
    protected $manufacturer;
    protected $partnum;
    protected $upc;
    protected $offers;
    protected $rating;

    public function __construct($attributes = [])
    {
        foreach ($attributes as $k => $v) {
            $this->$k = $v;
        }
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getFormattedPrice()
    {
        if (is_null($this->price)) {
            return 'USD ';
        }

        if (!function_exists('money_format')) {
            return "USD " . number_format($this->price);
        }
        return money_format('%n', $this->price);
    }

    public function getImage()
    {
        return $this->image;
    }

    public function hasImage()
    {
        return !!$this->image;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getSubCategory()
    {
        return $this->sub_category;
    }

    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    public function getPartnum()
    {
        return $this->partnum;
    }

    public function getUPC()
    {
        return $this->upc;
    }

    public function getOffers()
    {
        return $this->offers;
    }

    public function setOffers($offers)
    {
        $this->offers = $offers;
    }

    public function hasOffers()
    {
        return count($this->offers) > 0;
    }

    public function getUrl()
    {
        if ($this->isDirectDeal()) {
            return $this->offers[0]->getUrl();
        } else {
            return route('product', $this->getID());
        }
    }

    public function isDirectDeal()
    {
        return count($this->offers) == 1;
    }

    public function getID()
    {
        return $this->id;
    }

    public function getLinkTarget()
    {
        return $this->isDirectDeal() ? "_blank" : "_self";
    }

    public function hasRating()
    {
        return $this->getRating()->hasScore();
    }

    public function getRating()
    {
        return isset($this->rating) ? $this->rating : new Rating();
    }

    public function setRating($rating)
    {
        $this->rating = $rating;
    }

}
