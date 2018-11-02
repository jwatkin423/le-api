<?php
namespace Adrenalads\CommerceApi;

use Illuminate\Support\Facades\Facade;

class TaxonomyFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'taxonomy';
    }

}
