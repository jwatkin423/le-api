## laradren-ecomm-api

Version 3.1.2

laradren-ecomm-api is a private repo package that contains API clients for all our e-commerce advertising partners.
  * CNX (connexity)
  * ECN (ebay commerce network)
  * NXT (nextag)

Getting Started
====

1) Add your Personal Access Token to the authentication (see [Private Composer Github Repo](http://github.com/adrenalads-wiki/private-composer-package-github.md))

2) Add this code to the composer.json file:
  ```javascript
  "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:adrenalads/laradren-ecomm-api.git"
        }
    ],
  ```  
 3) Add to the required section:
    ```javascript  
    "require": {
        "php": ">=5.6.4",
        "laravel/framework": "5.4.*",
        "laravel/tinker": "~1.0",
        "laravelcollective/html": "5.4.*",
        "guzzlehttp/guzzle": "^6.0",
        "adrenalads/laradren-ecomm-api": "1.4.*"
    },
    ```
   4) Add to the providers:
        ```php
          'providers' => [
            ...
            Adrenalads\Feed\FeedServiceProvider::class,
            ...
           ]
   5) Add to the aliases:
       ```php
          'aliases' => [
           ...
           'Taxonomy' => Adrenalads\Feed\TaxonomyFacade::class,
           'CategoryOptions' => Adrenalads\Feed\CategoryOptionsFacade::class
           ...
           ]
    
   6) Run the following commands:
       ```bash 
           composer install
           php artisan vendor:publish --provider="Adrenalads\Feed\FeedServiceProvider"
       ```    
   7) Add the facades (controller, model etc): 
       ```php
            use Adrenalads\Feed\Taxonomy;
            use Adrenalads\Feed\CategoryOptions;
            use Adrenalads\Feed\Manager;
       ```
       
  ## Example ##
  ```php
  use Adrenalads\Feed\Taxonomy;
  use Adrenalads\Feed\CategoryOptions;
  use Adrenalads\Feed\Manager;
  
  class Foo extends bar {
  
  public function index() {
        //  Initialize Manager
        $Manager = new Manager();
        $Taxonomy = [];
        
        // Set the provider
        $provider = $Manager->getProvider();
        // Get the categories from the provider
        $Taxonomy = new Taxonomy($provider->getCategories());
        // Get the featured products from the provider
        $products = $provider->getFeaturedProducts();
        
    }
  }   
  ```
