<?php
/**
 * AmazonSimpleAdmin (ASA1)
 *
 * Amazon Creators API Item Wrapper
 *
 * Wraps item data from Creators API responses.
 * The Creators API response structure is compatible with PA API 5,
 * so we extend the PA API 5 item wrapper and add any Creators-specific enhancements.
 *
 * @author Timo Reith
 */

require_once __DIR__ . '/PaApi5.php';

class Asa_Service_Amazon_Item_CreatorsApi extends Asa_Service_Amazon_Item_PaApi5
{
    /**
     * Source identifier for debugging
     * @var string
     */
    protected $_source = 'creators_api';

    /**
     * Constructor
     *
     * @param array|string $data Response data from Creators API
     */
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * Get the data source identifier
     *
     * @return string 'creators_api'
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Check if this item was loaded via Creators API
     *
     * @return bool
     */
    public function isFromCreatorsApi()
    {
        return true;
    }

    /**
     * Override detail page URL if needed for Creators API specifics
     *
     * @return string
     */
    public function getDetailPageURL()
    {
        if (!empty($this->_data['DetailPageURL'])) {
            return apply_filters('asa1_detail_page_url', $this->_data['DetailPageURL']);
        }
        return '';
    }
}
