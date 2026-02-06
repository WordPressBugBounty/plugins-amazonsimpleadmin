<?php
/**
 * AmazonSimpleAdmin (ASA1)
 *
 * Amazon Creators API Service
 *
 * Implements the Amazon Service Interface for Creators API.
 * Requires PHP 8.1+ and valid Creators API credentials.
 *
 * @author Timo Reith
 */

use Amazon\CreatorsAPI\v1\Configuration;
use Amazon\CreatorsAPI\v1\com\amazon\creators\api\DefaultApi;
use Amazon\CreatorsAPI\v1\com\amazon\creators\model\GetItemsRequestContent;
use Amazon\CreatorsAPI\v1\com\amazon\creators\model\GetItemsResource;
use Amazon\CreatorsAPI\v1\com\amazon\creators\model\SearchItemsRequestContent;
use Amazon\CreatorsAPI\v1\com\amazon\creators\model\SearchItemsResource;
use Amazon\CreatorsAPI\v1\ApiException;

// Ensure Guzzle functions are loaded
require_once dirname(ASA_BASE_FILE) . '/vendor/asaguzzlehttp/guzzle/src/functions_include.php';
require_once dirname(ASA_BASE_FILE) . '/vendor/asaguzzlehttp/psr7/src/functions_include.php';
require_once dirname(ASA_BASE_FILE) . '/vendor/asaguzzlehttp/promises/src/functions_include.php';

require_once __DIR__ . '/CreatorsApi/Credentials.php';

class Asa_Service_CreatorsApi implements Asa_Service_Amazon_Interface
{
    /**
     * Amazon Associate Tag
     * @var string
     */
    protected $_associate_tag;

    /**
     * The API locale (country code)
     * @var string
     */
    protected $_locale;

    /**
     * Credentials instance
     * @var Asa_Service_CreatorsApi_Credentials
     */
    protected $_credentials;

    /**
     * SDK API instance
     * @var DefaultApi|null
     */
    protected $_api;

    /**
     * Creators API marketplace hosts mapped by locale
     * @var array
     */
    public static $marketplaces = [
        'AU' => 'www.amazon.com.au',
        'BE' => 'www.amazon.com.be',
        'BR' => 'www.amazon.com.br',
        'CA' => 'www.amazon.ca',
        'DE' => 'www.amazon.de',
        'EG' => 'www.amazon.eg',
        'ES' => 'www.amazon.es',
        'FR' => 'www.amazon.fr',
        'IN' => 'www.amazon.in',
        'IT' => 'www.amazon.it',
        'JP' => 'www.amazon.co.jp',
        'MX' => 'www.amazon.com.mx',
        'NL' => 'www.amazon.nl',
        'PL' => 'www.amazon.pl',
        'SA' => 'www.amazon.sa',
        'SE' => 'www.amazon.se',
        'SG' => 'www.amazon.sg',
        'TR' => 'www.amazon.com.tr',
        'AE' => 'www.amazon.ae',
        'UK' => 'www.amazon.co.uk',
        'US' => 'www.amazon.com',
    ];

    /**
     * API version constants for different regions
     * Each version uses a different OAuth2 endpoint
     */
    const API_VERSION_AMERICAS = '2.1';  // us-east-1: US, CA, MX, BR
    const API_VERSION_EUROPE = '2.2';    // eu-south-2: DE, UK, FR, IT, ES, NL, PL, SE, BE, TR, EG, SA, AE, IN
    const API_VERSION_FAR_EAST = '2.3';  // us-west-2: JP, AU, SG

    /**
     * Mapping of locale to API version
     * @var array
     */
    protected static $localeVersionMap = [
        // Americas (Version 2.1)
        'US' => self::API_VERSION_AMERICAS,
        'CA' => self::API_VERSION_AMERICAS,
        'MX' => self::API_VERSION_AMERICAS,
        'BR' => self::API_VERSION_AMERICAS,
        // Europe (Version 2.2)
        'DE' => self::API_VERSION_EUROPE,
        'UK' => self::API_VERSION_EUROPE,
        'FR' => self::API_VERSION_EUROPE,
        'IT' => self::API_VERSION_EUROPE,
        'ES' => self::API_VERSION_EUROPE,
        'NL' => self::API_VERSION_EUROPE,
        'PL' => self::API_VERSION_EUROPE,
        'SE' => self::API_VERSION_EUROPE,
        'BE' => self::API_VERSION_EUROPE,
        'TR' => self::API_VERSION_EUROPE,
        'EG' => self::API_VERSION_EUROPE,
        'SA' => self::API_VERSION_EUROPE,
        'AE' => self::API_VERSION_EUROPE,
        'IN' => self::API_VERSION_EUROPE,
        // Far East (Version 2.3)
        'JP' => self::API_VERSION_FAR_EAST,
        'AU' => self::API_VERSION_FAR_EAST,
        'SG' => self::API_VERSION_FAR_EAST,
    ];

    /**
     * Constructor
     *
     * @param string $tag Associate Tag
     * @param string $locale Locale (country code)
     * @throws Asa_Service_Amazon_Exception
     */
    public function __construct($tag, $locale)
    {
        if (empty($locale)) {
            throw new Asa_Service_Amazon_Exception('Missing locale');
        }
        if (!$this->isValidLocale($locale)) {
            throw new Asa_Service_Amazon_Exception('Invalid locale for Creators API: ' . $locale);
        }

        $this->_locale = $locale;
        $this->_credentials = Asa_Service_CreatorsApi_Credentials::getInstance();

        if (!$this->_credentials->hasValidCredentials()) {
            throw new Asa_Service_Amazon_Exception('Creators API credentials are not configured');
        }

        // Use Creators API tracking ID if configured, otherwise fall back to PA API tag
        if ($this->_credentials->hasTrackingId()) {
            $this->_associate_tag = $this->_credentials->getTrackingId();
        } else {
            if (empty($tag)) {
                throw new Asa_Service_Amazon_Exception('Missing associate tag');
            }
            $this->_associate_tag = $tag;
        }
    }

    /**
     * Get SDK API instance (lazy initialization)
     *
     * @return DefaultApi
     * @throws \RuntimeException If SDK initialization fails (e.g., GuzzleHttp conflict)
     */
    protected function _getApiInstance()
    {
        if ($this->_api === null) {
            try {
                $config = new Configuration();
                $config->setCredentialId($this->_credentials->getCredentialId());
                $config->setCredentialSecret($this->_credentials->getCredentialSecret());
                $config->setVersion($this->getVersionForLocale($this->_locale));

                // Use ASA's prefixed Guzzle client (SDK modified to use AsaGuzzleHttp)
                $client = new \AsaGuzzleHttp\Client();
                $this->_api = new DefaultApi($client, $config);
            } catch (\Throwable $e) {
                // This typically happens when ASA2 is active and loads the original GuzzleHttp
                // which conflicts with our AsaGuzzleHttp prefix
                throw new \RuntimeException(
                    'Creators API SDK initialization failed. This may be caused by a plugin conflict (e.g., ASA2). ' .
                    'Original error: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        return $this->_api;
    }

    /**
     * Get the API version for a specific locale
     *
     * Different locales require different API versions because each version
     * uses a different OAuth2 authentication endpoint:
     * - 2.1 (Americas): us-east-1
     * - 2.2 (Europe): eu-south-2
     * - 2.3 (Far East): us-west-2
     *
     * @param string $locale Country code
     * @return string API version
     */
    public function getVersionForLocale($locale)
    {
        return self::$localeVersionMap[$locale] ?? self::API_VERSION_AMERICAS;
    }

    /**
     * Item Lookup via Creators API
     *
     * @param string $asin ASIN or comma-separated ASINs
     * @param array $options Additional options
     * @return Asa_Service_Amazon_Item_CreatorsApi|null
     * @throws ApiException
     */
    public function itemLookup($asin, array $options = array())
    {
        try {
            $api = $this->_getApiInstance();
        } catch (\RuntimeException $e) {
            // SDK initialization failed (likely ASA2 conflict)
            // Return null to trigger PA API fallback
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('ASA1 Creators API: ' . $e->getMessage());
            }
            return null;
        }
        $marketplace = $this->getMarketplace($this->_locale);

        // Normalize ASIN input
        if (is_string($asin) && strpos($asin, ',') !== false) {
            $asin = explode(',', $asin);
        }
        $itemIds = is_array($asin) ? array_map('trim', $asin) : [trim($asin)];

        // Build request
        $request = new GetItemsRequestContent();
        $request->setItemIds($itemIds);
        $request->setPartnerTag($this->_associate_tag);
        $request->setResources($this->_getItemResources());

        // Check buffer first
        $requestToken = md5(implode('', [implode('-', $itemIds), $this->_locale, $this->_associate_tag, 'creators']));

        if (Asa_Util_Buffer::exists($requestToken, 'creators-item-lookup')) {
            $responseData = Asa_Util_Buffer::get($requestToken, 'creators-item-lookup');
        } else {
            try {
                $response = $api->getItems($marketplace, $request);

                // Convert response to array
                $responseData = json_decode((string)$response, true);
                Asa_Util_Buffer::set($requestToken, $responseData, 'creators-item-lookup');

            } catch (ApiException $e) {
                $errorMsg = "Creators API GetItems Error" . PHP_EOL;
                $errorMsg .= "HTTP Status: " . $e->getCode() . PHP_EOL;
                $errorMsg .= "Message: " . $e->getMessage() . PHP_EOL;
                throw new ApiException($errorMsg, $e->getCode());
            } catch (\Exception $e) {
                throw $e;
            }
        }

        // Parse response and return item
        require_once __DIR__ . '/Amazon/Item/CreatorsApi.php';

        // Creators API uses camelCase (itemsResult/items) instead of PascalCase
        if (!empty($responseData['itemsResult']['items'][0])) {
            // Convert keys from camelCase to PascalCase for PA API 5 compatibility
            $itemData = $this->_convertKeysToPascalCase($responseData['itemsResult']['items'][0]);
            return new Asa_Service_Amazon_Item_CreatorsApi($itemData);
        }

        return null;
    }

    /**
     * Item Search via Creators API
     *
     * @param array $options Search options (Keywords, SearchIndex, etc.)
     * @return mixed
     * @throws ApiException
     */
    public function itemSearch(array $options)
    {
        try {
            $api = $this->_getApiInstance();
        } catch (\RuntimeException $e) {
            // SDK initialization failed (likely ASA2 conflict)
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('ASA1 Creators API: ' . $e->getMessage());
            }
            return null;
        }
        $marketplace = $this->getMarketplace($this->_locale);

        if (empty($options['Keywords'])) {
            throw new Asa_Service_Amazon_Exception('Keywords are required for item search');
        }

        // Build request
        $request = new SearchItemsRequestContent();
        $request->setKeywords(urldecode($options['Keywords']));
        $request->setPartnerTag($this->_associate_tag);
        $request->setResources($this->_getSearchResources());

        // Optional parameters
        if (!empty($options['SearchIndex']) && $options['SearchIndex'] !== 'All') {
            $request->setSearchIndex($options['SearchIndex']);
        }
        if (!empty($options['ItemCount'])) {
            $request->setItemCount((int)$options['ItemCount']);
        }

        try {
            $response = $api->searchItems($marketplace, $request);
            return $response;

        } catch (ApiException $e) {
            $errorMsg = "Creators API SearchItems Error" . PHP_EOL;
            $errorMsg .= "HTTP Status: " . $e->getCode() . PHP_EOL;
            $errorMsg .= "Message: " . $e->getMessage() . PHP_EOL;
            throw new ApiException($errorMsg, $e->getCode());
        }
    }

    /**
     * Test connection to Creators API
     *
     * @throws Asa_Service_Amazon_Exception
     */
    public function testConnection()
    {
        $result = $this->itemSearch(['Keywords' => 'test', 'ItemCount' => 1]);

        // If result is null, SDK initialization failed (likely ASA2 conflict)
        if ($result === null) {
            throw new Asa_Service_Amazon_Exception(
                'Creators API unavailable due to plugin conflict. ' .
                'If ASA2 is active, the Creators API in ASA1 is not needed.'
            );
        }
    }

    /**
     * Get resources for GetItems request
     *
     * Note: Creators API uses OffersV2 structure exclusively
     *
     * @return array
     */
    protected function _getItemResources()
    {
        return [
            GetItemsResource::IMAGES_PRIMARY_SMALL,
            GetItemsResource::IMAGES_PRIMARY_MEDIUM,
            GetItemsResource::IMAGES_PRIMARY_LARGE,
            GetItemsResource::IMAGES_VARIANTS_SMALL,
            GetItemsResource::IMAGES_VARIANTS_MEDIUM,
            GetItemsResource::IMAGES_VARIANTS_LARGE,
            GetItemsResource::ITEM_INFO_BY_LINE_INFO,
            GetItemsResource::ITEM_INFO_CLASSIFICATIONS,
            GetItemsResource::ITEM_INFO_CONTENT_INFO,
            GetItemsResource::ITEM_INFO_CONTENT_RATING,
            GetItemsResource::ITEM_INFO_EXTERNAL_IDS,
            GetItemsResource::ITEM_INFO_FEATURES,
            GetItemsResource::ITEM_INFO_MANUFACTURE_INFO,
            GetItemsResource::ITEM_INFO_PRODUCT_INFO,
            GetItemsResource::ITEM_INFO_TECHNICAL_INFO,
            GetItemsResource::ITEM_INFO_TITLE,
            GetItemsResource::ITEM_INFO_TRADE_IN_INFO,
            // Creators API uses OffersV2 exclusively
            GetItemsResource::OFFERS_V2_LISTINGS_AVAILABILITY,
            GetItemsResource::OFFERS_V2_LISTINGS_CONDITION,
            GetItemsResource::OFFERS_V2_LISTINGS_DEAL_DETAILS,
            GetItemsResource::OFFERS_V2_LISTINGS_IS_BUY_BOX_WINNER,
            GetItemsResource::OFFERS_V2_LISTINGS_MERCHANT_INFO,
            GetItemsResource::OFFERS_V2_LISTINGS_PRICE,
            GetItemsResource::PARENT_ASIN,
            GetItemsResource::BROWSE_NODE_INFO_BROWSE_NODES_SALES_RANK,
            GetItemsResource::CUSTOMER_REVIEWS_COUNT,
            GetItemsResource::CUSTOMER_REVIEWS_STAR_RATING,
        ];
    }

    /**
     * Get resources for SearchItems request
     *
     * Note: Creators API uses OffersV2 structure exclusively
     *
     * @return array
     */
    protected function _getSearchResources()
    {
        return [
            SearchItemsResource::IMAGES_PRIMARY_SMALL,
            SearchItemsResource::IMAGES_PRIMARY_MEDIUM,
            SearchItemsResource::IMAGES_PRIMARY_LARGE,
            SearchItemsResource::ITEM_INFO_TITLE,
            SearchItemsResource::ITEM_INFO_CLASSIFICATIONS,
            // Creators API uses OffersV2 exclusively
            SearchItemsResource::OFFERS_V2_LISTINGS_PRICE,
            SearchItemsResource::OFFERS_V2_LISTINGS_AVAILABILITY,
        ];
    }

    /**
     * Get marketplace URL for locale
     *
     * @param string $locale Country code
     * @return string|null
     */
    public function getMarketplace($locale)
    {
        return self::$marketplaces[$locale] ?? null;
    }

    /**
     * Check if locale is valid for Creators API
     *
     * @param string $locale Country code
     * @return bool
     */
    public function isValidLocale($locale)
    {
        return array_key_exists($locale, self::$marketplaces);
    }

    /**
     * Get Associate Tag
     *
     * @return string
     */
    public function getAssociateTag()
    {
        return $this->_associate_tag;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Convert Creators API response keys from camelCase to PascalCase
     * to be compatible with PA API 5 item wrapper
     *
     * @param array $data
     * @return array
     */
    protected function _convertKeysToPascalCase($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $result = [];
        foreach ($data as $key => $value) {
            // Convert key to PascalCase
            $newKey = $this->_toPascalCase($key);

            // Recursively convert nested arrays
            if (is_array($value)) {
                $value = $this->_convertKeysToPascalCase($value);
            }

            $result[$newKey] = $value;
        }

        return $result;
    }

    /**
     * Convert a camelCase string to PascalCase
     * Special handling for known abbreviations (ASIN, URL, etc.)
     *
     * @param string $str
     * @return string
     */
    protected function _toPascalCase($str)
    {
        // Special cases for known abbreviations
        $abbreviations = [
            'asin' => 'ASIN',
            'url' => 'URL',
            'ean' => 'EAN',
            'isbn' => 'ISBN',
            'upc' => 'UPC',
        ];

        if (isset($abbreviations[strtolower($str)])) {
            return $abbreviations[strtolower($str)];
        }

        // Convert first character to uppercase
        return ucfirst($str);
    }
}
