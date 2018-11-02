<?php
namespace Adrenalads\CommerceApi;

use Cache;

class Cached extends Base
{

    protected $_provider;

    public function __construct($provider)
    {
        $this->_provider = $provider;
        parent::__construct([]);
    }

    public function getCategories($args = null)
    {
        $key = $this->cacheKey('getCategories', func_get_args());
        $expiration = $this->getCategoriesExpiration();

        return Cache::remember($key, $expiration, function () use ($args) {
            return $this->_provider->getCategories($args);
        });
    }

    public function cacheKey($method, $args)
    {
        $args_hash = hash('sha1', json_encode($args));
        $options_hash = hash('sha1', json_encode($this->getOptions()));
        return implode('|', [
            get_class($this->_provider),
            $options_hash,
            $method,
            $args_hash
        ]);
    }

    public function getOptions()
    {
        return $this->_provider->getOptions();
    }

    public function getCategoriesExpiration()
    {
        return 60;
    }

    public function getFeaturedProducts($args = null)
    {
        $key = $this->cacheKey('getFeaturedProducts', func_get_args());
        $expiration = $this->getCategoryExpiration();

        return Cache::remember($key, $expiration, function () use ($args) {
            return $this->_provider->getFeaturedProducts($args);
        });
    }

    public function getCategoryExpiration()
    {
        return 30;
    }

    public function getCategory($category, $page = null)
    {
        $key = $this->cacheKey('getCategory', func_get_args());
        $expiration = $this->getCategoryExpiration();

        return Cache::remember($key, $expiration, function () use ($category, $page) {
            return $this->_provider->getCategory($category, $page);
        });
    }

    public function search($keyword, $page = null)
    {
        $key = $this->cacheKey('search', func_get_args());
        $expiration = $this->getCategoryExpiration();

        return Cache::remember($key, $expiration, function () use ($keyword, $page) {
            return $this->_provider->search($keyword, $page);
        });
    }

    public function getProduct($product_id)
    {
        $key = $this->cacheKey('getProduct', $product_id);
        $expiration = $this->getProductExpiration();

        return Cache::remember($key, $expiration, function () use ($product_id) {
            return $this->_provider->getProduct($product_id);
        });
    }

    public function getProductExpiration()
    {
        return 5;
    }

    public function getUserAgent()
    {
        return $this->_provider->getUserAgent();
    }

    public function setUserAgent($userAgent)
    {
        return $this->_provider->setUserAgent($userAgent);
    }

    public function getSessionID()
    {
        return $this->_provider->getSessionID();
    }

    public function setSessionID($session_id)
    {
        return $this->_provider->setSessionID($session_id);
    }

    public function getProvider()
    {
        return $this->_provider;
    }

    public function getRPP()
    {
        return $this->_provider->getRPP();
    }

    public function setRPP($rpp)
    {
        return $this->_provider->setRPP($rpp);
    }

    public function getSortBy()
    {
        return $this->_provider->getSortBy();
    }

    public function setSortBy($sortby)
    {
        return $this->_provider->setSortBy($sortby);
    }

    public function getSortOptions()
    {
        return $this->_provider->getSortOptions();
    }

    public function getDefaultSort()
    {
        return $this->_provider->getDefaultSort();
    }

}
