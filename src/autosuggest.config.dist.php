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
        foreach (['app/bootstrap.php', '../app/bootstrap.php'] as $bootstrapFile) {
            if (\file_exists($bootstrapFile)) {
                require $bootstrapFile;
                break;
            }
        }
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
     * use directory relative to cwd (document root)
     */
    ->withCacheBaseDir(\is_dir('var/cache') ? 'var/cache/integernet_solr' : '../var/cache/integernet_solr')
    ;

