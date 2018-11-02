<?php
namespace Adrenalads\CommerceApi;

class Offer
{

    protected $url;
    protected $price;
    protected $retailer_logo;
    protected $retailer_text;
    protected $available;
    protected $quality;
    protected $rating;

    public function __construct($attributes = [])
    {
        foreach ($attributes as $k => $v) {
            $this->$k = $v;
        }
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getFormattedPrice()
    {
        if (!function_exists('money_format')) {
            return "USD " . number_format($this->price);
        }
        return money_format('%n', $this->price);
    }

    public function getRetailerLogo()
    {
        return $this->retailer_logo;
    }

    public function hasRetailerLogo()
    {
        return !!$this->retailer_logo;
    }

    public function getRetailerText()
    {
        return $this->retailer_text;
    }

    public function getAvailable()
    {
        return $this->available;
    }

    public function getQuality()
    {
        return $this->quality;
    }

    public function hasRating()
    {
        return $this->getRating()->hasScore();
    }

    public function getRating()
    {
        return isset($this->rating) ? $this->rating : new Rating();
    }

}
