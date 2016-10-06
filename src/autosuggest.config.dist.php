<?php
/**
 * integer_net Magento Module
 *
 * DO NOT CHANGE THIS FILE! Copy it to autosuggest.config.php if you want to change the configuration
 *
 * @category   IntegerNet
 * @package    IntegerNet_SolrSuggest
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\SolrSuggest\Plain\AppConfig;

return AppConfig::defaultConfig()
    /*
     * Callback that returns the application specific SolrSuggest Factory implementation
     *
     * Used to initialize cache on the fly
     */
    ->withLoadApplicationCallback(function()
    {
        require  '../app/bootstrap.php';
        $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);

        $om = $bootstrap->getObjectManager();
        $om->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');

        return $om->create(\IntegerNet\Solr\Model\Bridge\AppFactory::class);
    })
    /*
     * Base directory for cache.
     *
     * This is only used if you use the default file based cache.
     *
     * use directory relative to cwd (Magento root)
     */
    ->withCacheBaseDir('../var/cache/integernet_solr')
    ;

