<?php
namespace Adrenalads\CommerceApi;

use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

class Manager
{

    public function getProvider()
    {
        $config = $this->getHostConfiguration();

        $provider_options = $config['options'];

        $provider = new $config['client']($provider_options);

        if (@$config['cache']) {
            $provider = new Cached($provider);
        }

        return $provider;
    }

    public function getHostConfiguration()
    {
        return Config::get('commerce.api_clients')[$this->getHost()];
    }

    public function getHost()
    {
        $host = env("ADV_KEY");
        if ($host) {
            return $host;
        }

        $Request = new Request();
        $http_host = $Request->getHttpHost();
        foreach (Config::get('commerce.hosts') as $key => $value) {
            if (strpos($http_host, $key) !== false) {
                return $key;
            }
        }
        return Config::get('commerce.default_host');
    }

    public function getSkin()
    {
        return $this->getHostConfiguration()['skin'];
    }

    public function getLangCode()
    {
        // default language code = US-EN
        $lang_code = 'US-EN';

        if (isset($this->getHostConfiguration()['lang_code'])) {
            $lang_code = $this->getHostConfiguration()['lang_code'];
        }

        return $lang_code;
    }
}
