<?php
/**
 * AmazonSimpleAdmin (ASA1)
 * For more information see http://www.wp-amazon-plugin.com/
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Prefetcher.php 3038732 2024-02-20 18:56:07Z worschtebrot $
 */
require_once ASA_LIB_DIR . 'Asa/ItemBuffer.php';

class Asa_Prefetcher
{
    const MAX_API_REQUESTS = 5;

    /**
     * @var Asa_Prefetcher
     */
    protected static $_instance;

    /**
     * @var array
     */
    protected $_collectedAsins = array();


    /**
     * @return Asa_Prefetcher
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected function __construct()
    {
    }

    public function init()
    {
        $isDisabled = get_option('_asa_disable_prefetch');

        if (!$isDisabled) {
            $this->_initHooks();
        }
    }

    protected function _initHooks()
    {
        add_filter( 'the_content', array($this, 'filterContent') );
        add_filter( 'widget_text', array($this, 'filterContent') );
        add_filter( 'comment_text', array($this, 'filterContent') );
    }

    /**
     * @param $content
     * @return mixed
     */
    public function filterContent($content)
    {
        global $shortcode_tags;

        $shortcode_tags_backup = $shortcode_tags;

        $shortcode_tags = array('asa' => '');
        $pattern = get_shortcode_regex();
        preg_replace_callback( "/$pattern/s", array($this, 'collectAsinsFromShortcodeAsa'), $content );

        $shortcode_tags = array('asa_collection' => '');
        $pattern = get_shortcode_regex();
        preg_replace_callback( "/$pattern/s", array($this, 'collectAsinsFromShortcodeAsaCollection'), $content );

        // restore global shortcodes
        $shortcode_tags = $shortcode_tags_backup;

        $this->prefetch();
        
        return $content;
    }

    /**
     * Collects ASINs from [asa] shortcodes
     *
     * @param array $matches
     * @return string
     */
    public function collectAsinsFromShortcodeAsa(array $matches)
    {
        if (!empty($matches[5])) {
            $this->addCollectedAsin(trim($matches[5]));
        }

        return '';
    }

    /**
     * @param string $asin
     */
    public function addCollectedAsin($asin)
    {
        global $asa;

        $asin = preg_replace('/[^a-zA-Z0-9_]/', '', $asin);

        if (!empty($asin) && !in_array($asin, $this->_collectedAsins)) {
            if (!$asa->isCache() || ($asa->isCache() && $asa->getCache()->test($asin) === false)) {
                // add asin if cache is not used or asin is not cached
                array_push($this->_collectedAsins, $asin);
            }
        }
    }

    /**
     * Collects ASINs from [asa] shortcodes
     *
     * @param array $matches
     * @return string
     */
    public function collectAsinsFromShortcodeAsaCollection(array $matches)
    {
        global $wpdb, $asa;

        if (isset($matches[5])) {
            require_once(dirname(ASA_BASE_FILE) . '/AsaCollection.php');
            $collLabel = trim($matches[5]);

            $collOptions = trim($matches[3]);
            $limit = 0;
            $random = false;
            $collOptionsParts = explode(' ', $collOptions);

            foreach ($collOptionsParts as $collOptionsPart) {
                if (strstr($collOptionsPart, 'items') || strstr($collOptionsPart, 'limit')) {
                    $limit = (int)filter_var($collOptionsPart, FILTER_SANITIZE_NUMBER_INT);
                }
            }
            if (strstr($collOptions, 'random')) {
                $random = true;
            }

            $collId = AsaCollection::getInstance($wpdb)->getId($collLabel);

            $collItems = AsaCollection::getInstance($wpdb)->getItems($collId, ARRAY_A);

            if ($random && count($collItems) > 10 && $limit > 0 && !$asa->isCache()) {
                // skip if prefetch might produce more API requests than the actual shortcode
                return;
            }

            $counter = 0;
            foreach ($collItems as $collItem) {
                if (!empty($collItem['collection_item_asin'])) {
                    foreach (array_map('trim', explode(',', $collItem['collection_item_asin'])) as $asin) {
                        $this->addCollectedAsin($asin);
                    }
                }
                if (strstr($collOptions, 'latest')) {
                    break;
                }
                $counter++;
                if (!$random && $limit > 0 && $counter >=$limit) {
                    // break if limit is reached and type is not random
                    break;
                }
            }
        }

        return '';
    }

    /**
     * @return int
     */
    public function getApiRequestLimit()
    {
        return self::MAX_API_REQUESTS;
    }

    /**
     * Puts prefetched items in the cache
     */
    public function prefetch()
    {
        global $asa;

        $requestCount = 0;

        foreach (array_chunk($this->_collectedAsins, 10) as $AsinsChunk) {
            if ($requestCount >= $this->getApiRequestLimit()) {
                break;
            }
            try {
                // send API batch request with max 10 items
                $result = $asa->getItemLookup(implode(',', $AsinsChunk));
                $requestCount++;

                if (is_array($result)) {
                    /**
                     * @var AsaZend_Service_Amazon_Item $item
                     */
                    foreach ($result as $item) {

                        if ($item instanceof AsaZend_Service_Amazon_Item) {
                            Asa_ItemBuffer::putItem($item->ASIN, $item);

                            if ($asa->isCache()) {
                                if ($asa->isVariantCacheLifetime()) {
                                    $asa->getCache()->save($item, $item->ASIN, array(), $asa->getVariantCacheLifetime());
                                } else {
                                    $asa->getCache()->save($item, $item->ASIN);
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // fetch prefetching errors
            }
        }

    }
}
