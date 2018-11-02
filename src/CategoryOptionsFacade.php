<?php
namespace Adrenalads\CommerceApi;

use Illuminate\Support\Facades\Facade;

class CategoryOptionsFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'categoryoptions';
    }

}
