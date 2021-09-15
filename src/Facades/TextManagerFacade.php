<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class TextManagerFacade extends Facade {
    protected static function getFacadeAccessor() { 

        return 'textmanager'; 
    }
}