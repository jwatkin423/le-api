<?php
namespace Adrenalads\CommerceApi;

class CategoryOptions
{

    protected $default_rpp = 15;
    protected $rpp;
    protected $sortby;
    protected $q;
    protected $provider;

    public function __construct($provider, $input)
    {
        $this->provider = $provider;

        /* Results per Page */
        if (@in_array($input['rpp'], $this->getRppOptions())) {
            $this->rpp = $input['rpp'];
        } else {
            $this->rpp = $this->default_rpp;
        }
        $provider->setRPP($this->rpp);

        /* SortBy */
        if ($this->hasSortOptions()) {
            if (@array_key_exists($input['sortby'], $this->getSortOptions())) {
                $this->sortby = $input['sortby'];
                $provider->setSortBy($this->sortby);
            } else {
                $this->sortby = $provider->getDefaultSort();
            }
        }

        /* Search Query */
        if (isset($input['q'])) {
            $this->q = $input['q'];
        }
    }

    public function getRppOptions()
    {
        return [15, 25, 50, 75, 100];
    }

    public function hasSortOptions()
    {
        return count($this->provider->getSortOptions()) > 0;
    }

    public function getSortOptions()
    {
        return $this->provider->getSortOptions();
    }

    public function getRpp()
    {
        return $this->rpp;
    }

    public function getQ()
    {
        return $this->q;
    }

    public function getPaginatorParams()
    {
        $params = [];
        if ($this->hasQ()) {
            $params['q'] = $this->q;
        }
        if ($this->rpp != $this->default_rpp) {
            $params['rpp'] = $this->rpp;
        }
        if ($this->sortby != $this->provider->getDefaultSort()) {
            $params['sortby'] = $this->sortby;
        }

        return $params;
    }

    public function hasQ()
    {
        return !is_null($this->q);
    }

    public function getSortby()
    {
        return $this->sortby;
    }

}
