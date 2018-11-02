<?php
namespace Adrenalads\CommerceApi\Provider;

abstract class Base
{

    protected $_options;

    protected $_user_agent;

    protected $_session_id;

    public function __construct($options)
    {
        $default_options = [
            "rpp" => 25,
            "sortby" => $this->getDefaultSort()
        ];
        $this->_options = array_merge($default_options, $options);
    }

    /**
     * Get default sort option
     */
    public function getDefaultSort()
    {
        return null;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function getUserAgent()
    {
        return $this->_user_agent;
    }

    /**
     * Controller will set the User Agent
     * prior to any usage of the feed provider
     */
    public function setUserAgent($userAgent)
    {
        $this->_user_agent = $userAgent;
    }

    public function getSessionID()
    {
        return $this->_session_id;
    }

    /**
     * Controller will set the SessionID
     * prior to any usage of the feed provider
     */
    public function setSessionID($session_id)
    {
        $this->_session_id = $session_id;
    }

    public function getRPP()
    {
        return $this->_options["rpp"];
    }

    public function setRPP($rpp)
    {
        $this->_options["rpp"] = $rpp;
    }

    public function getSortBy()
    {
        return $this->_options['sortby'];
    }

    public function setSortBy($sortby)
    {
        if (array_key_exists($sortby, $this->getSortOptions())) {
            $this->_options['sortby'] = $sortby;
        }
    }

    /**
     * Get hash of available sorting options
     */
    public function getSortOptions()
    {
        return [];
    }

    /**
     * Get all categories
     */
    abstract public function getCategories($args = null);

    /**
     * Get featured products
     */
    public function getFeaturedProducts($args = null)
    {
        return $this->getCategory([]);
    }

    /**
     * Get products from the category
     */
    abstract public function getCategory($category, $page = null);

    /**
     * Search products by keyword
     */
    abstract public function search($keyword, $page = null);

    /**
     * Get specific product info
     */
    abstract public function getProduct($product_id);

}
