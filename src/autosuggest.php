<?php
/**
 * integer_net Magento Module
 *
 * SYMLINK THIS FILE INTO /pub/
 *
 * @category   IntegerNet
 * @package    IntegerNet_SolrSuggest
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\SolrSuggest\Plain;
/*
 * NO USE STATEMENTS BEFORE AUTOLOADER IS INITIALIZED!
 */

/**
 * Class used for customization in autosuggest.config.php
 */
class AppConfig
{
    /** @var string */
    private $cacheBaseDir;
    /** @var  string */
    private $libBaseDir;
    /** @var \Closure */
    private $loadApplicationCallback;

    /**
     * @param string $cacheBaseDir
     * @param $loadApplicationCallback
     */
    private function __construct($cacheBaseDir, $loadApplicationCallback)
    {
        $this->cacheBaseDir = $cacheBaseDir;
        $this->loadApplicationCallback = $loadApplicationCallback;
    }

    public static function defaultConfig()
    {
        $cacheBaseDir = '/tmp/integernet_solr';
        $loadApplicationCallback = function () {
            throw new \BadMethodCallException('Application cannot be instantiated');
        };
        return new self($cacheBaseDir, $loadApplicationCallback);
    }

    /**
     * @param string $cacheBaseDir
     * @return AppConfig
     */
    public function withCacheBaseDir($cacheBaseDir)
    {
        $config = clone $this;
        $config->cacheBaseDir = $cacheBaseDir;
        return $config;
    }

    /**
     * @param \Closure $callback
     * @return AppConfig
     */
    public function withLoadApplicationCallback(\Closure $callback)
    {
        $config = clone $this;
        $config->loadApplicationCallback = $callback;
        return $config;
    }

    /**
     * @return \IntegerNet\SolrSuggest\Plain\Cache\CacheStorage
     */
    public function getCache()
    {
        //TODO allow defining custom callback for cache instantiation
        return new \IntegerNet\SolrSuggest\Plain\Cache\PsrCache(
            new \IntegerNet\SolrSuggest\CacheBackend\File\CacheItemPool($this->cacheBaseDir)
        );
    }

    /**
     * @return \Closure
     */
    public function getLoadApplicationCallback()
    {
        return $this->loadApplicationCallback;
    }

}

class Bootstrap
{
    /**
     * @var \IntegerNet\SolrSuggest\Plain\Http\AutosuggestRequest
     */
    private $request;
    /**
     * @var AppConfig
     */
    private $config;

    public function __construct()
    {
        $this->initAppConfig();
        $this->initAutoload();
        $this->initRequest();
    }

    private function initAppConfig()
    {
        if (\file_exists(__DIR__ . '/autosuggest.config.php')) {
            $this->config = include __DIR__ . '/autosuggest.config.php';
            if ($this->config instanceof AppConfig) {
                return;
            }
        }
        $this->config = include __DIR__ . '/autosuggest.config.dist.php';
    }

    private function initAutoload()
    {
        require_once '../vendor/autoload.php';
    }

    private function initRequest()
    {
        $this->request = \IntegerNet\SolrSuggest\Plain\Http\AutosuggestRequest::fromGet($_GET);
    }

    public function run()
    {
        try {
            $factory = new \IntegerNet\SolrSuggest\Plain\Factory(
                $this->request,
                $this->config->getCache(),
                $this->config->getLoadApplicationCallback()
            );
            $response = $factory->getAutosuggestController()->process($this->request);
        } catch (\Exception $e) {
            // controller could not be initialized, need to craft own error response
            throw $e;
            $response = new \IntegerNet\SolrSuggest\Plain\Http\AutosuggestResponse(500, 'Internal Server Error');
        }

        if (\function_exists('http_response_code')) {
            \http_response_code($response->getStatus());
        }
        echo $response->getBody();
    }

}

\call_user_func(function() {
    $bootstrap = new Bootstrap();
    $bootstrap->run();
});