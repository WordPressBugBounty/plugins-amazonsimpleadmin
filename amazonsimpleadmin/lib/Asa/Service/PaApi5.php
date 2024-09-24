<?php
/**
 * AmazonSimpleAdmin - Wordpress Plugin
 *
 * @author Timo Reith
 * @copyright Copyright (c) 2007-2011 Timo Reith (http://www.wp-amazon-plugin.com)
 *
 *
 */

use AsaAmazon\ProductAdvertisingAPI\v1\ApiException;
use AsaAmazon\ProductAdvertisingAPI\v1\Configuration;
use AsaAmazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use AsaAmazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use AsaAmazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource;
use AsaAmazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest;
use AsaAmazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource;
use AsaAmazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use AsaAmazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException;

require_once(__DIR__ . '/../../../vendor/autoload.php');

class Asa_Service_PaApi5 implements Asa_Service_Amazon_Interface
{

    /**
     * Amazon associate tag
     * @var string
     */
    protected $_associate_tag;

    /**
     * Amazon webservice access key id
     * @var string
     */
    protected $_access_key_id;

    /**
     * Amazon webservice secret access key
     * @var string
     */
    protected $_secret_access_key;

    /**
     * The api locale
     * @var string
     */
    protected $_locale;

    public static $stores = [
        'AE' => array(
            'host' => 'webservices.amazon.ae',
            'region' => 'eu-west-1',
        ),
        'AU' => array(
            'host' => 'webservices.amazon.com.au',
            'region' => 'us-west-2',
        ),
        'BE' => array(
            'host' => 'webservices.amazon.com.be',
            'region' => 'eu-west-1',
        ),
        'BR' => array(
            'host' => 'webservices.amazon.com.br',
            'region' => 'us-east-1',
        ),
        'CA' => array(
            'host' => 'webservices.amazon.ca',
            'region' => 'us-east-1',
        ),
        'DE' => array(
            'host' => 'webservices.amazon.de',
            'region' => 'eu-west-1',
        ),
        'EG' => array(
            'host' => 'webservices.amazon.eg',
            'region' => 'eu-west-1',
        ),
        'ES' => array(
            'host' => 'webservices.amazon.es',
            'region' => 'eu-west-1',
        ),
        'FR' => array(
            'host' => 'webservices.amazon.fr',
            'region' => 'eu-west-1',
        ),
        'IN' => array(
            'host' => 'webservices.amazon.in',
            'region' => 'eu-west-1',
        ),
        'IT' => array(
            'host' => 'webservices.amazon.it',
            'region' => 'eu-west-1',
        ),
        'JP' => array(
            'host' => 'webservices.amazon.co.jp',
            'region' => 'us-west-2',
        ),
        'MX' => array(
            'host' => 'webservices.amazon.com.mx',
            'region' => 'us-east-1',
        ),
        'NL' => array(
            'host' => 'webservices.amazon.nl',
            'region' => 'eu-west-1',
        ),
        'PL' => array(
            'host' => 'webservices.amazon.pl',
            'region' => 'eu-west-1',
        ),
        'SA' => array(
            'host' => 'webservices.amazon.sa',
            'region' => 'us-west-1',
        ),
        'SG' => array(
            'host' => 'webservices.amazon.sg',
            'region' => 'us-west-2',
        ),
        'SE' => array(
            'host' => 'webservices.amazon.se',
            'region' => 'eu-west-1',
        ),
        'TR' => array(
            'host' => 'webservices.amazon.com.tr',
            'region' => 'eu-west-1',
        ),
        'UK' => array(
            'host' => 'webservices.amazon.co.uk',
            'region' => 'eu-west-1',
        ),
        'US' => array(
            'host' => 'webservices.amazon.com',
            'region' => 'us-east-1',
        )
    ];


    /**
     * @param string $access_key
     * @param string $secret
     * @param string $tag
     * @param string $locale
     * @param null $connection_type
     * @throws Asa_Service_Amazon_Exception
     */
    public function __construct($access_key, $secret, $tag, $locale, $connection_type = null)
    {
        if (empty($access_key)) {
            throw new Asa_Service_Amazon_Exception('Missing access key');
        }
        if (empty($secret)) {
            throw new Asa_Service_Amazon_Exception('Missing secret access key');
        }
        if (empty($tag)) {
            throw new Asa_Service_Amazon_Exception('Missing associate tag');
        }
        if (empty($locale)) {
            throw new Asa_Service_Amazon_Exception('Missing locale');
        }
        if (!$this->isValidLocale($locale)) {
            throw new Asa_Service_Amazon_Exception('Invalid locale');
        }

        $this->setAccessKeyId($access_key);
        $this->setSecretAccessKey($secret);
        $this->setAssociateTag($tag);
        $this->setLocale($locale);
    }

    /**
     * @param string $asin
     * @param array $options
     * @return mixed
     * @throws ApiException
     */
    public function itemLookup($asin, array $options = array())
    {
        $config = new Configuration();

        $config->setAccessKey($this->getAccessKeyId());
        $config->setSecretKey($this->getSecretAccessKey());

        $countryCode = $this->getLocale();
        $partnerTag = $this->getAssociateTag();

        $config->setHost($this->getHost($countryCode));
        $config->setRegion($this->getRegion($countryCode));

        $apiInstance = self::_getApiInstance($config);

        if (is_string($asin) && strpos($asin, ',') !== false) {
            $asin = explode(',', $asin);
        }
        $itemIds = ifw_var_to_array($asin);

        $resources = array(

            GetItemsResource::OFFERSLISTINGSPRICE,
            GetItemsResource::OFFERSLISTINGSPROGRAM_ELIGIBILITYIS_PRIME_EXCLUSIVE,
            GetItemsResource::OFFERSLISTINGSPROGRAM_ELIGIBILITYIS_PRIME_PANTRY,
            GetItemsResource::OFFERSLISTINGSPROMOTIONS,
            GetItemsResource::OFFERSLISTINGSSAVING_BASIS,
            GetItemsResource::OFFERSSUMMARIESHIGHEST_PRICE,
            GetItemsResource::OFFERSSUMMARIESLOWEST_PRICE,
            GetItemsResource::OFFERSSUMMARIESOFFER_COUNT,
            GetItemsResource::OFFERSLISTINGSIS_BUY_BOX_WINNER,
            GetItemsResource::OFFERSLISTINGSDELIVERY_INFOSHIPPING_CHARGES,
            GetItemsResource::OFFERSLISTINGSDELIVERY_INFOIS_PRIME_ELIGIBLE,
            GetItemsResource::OFFERSLISTINGSDELIVERY_INFOIS_FREE_SHIPPING_ELIGIBLE,
            GetItemsResource::OFFERSLISTINGSDELIVERY_INFOIS_AMAZON_FULFILLED,
            GetItemsResource::OFFERSLISTINGSCONDITION,
            GetItemsResource::OFFERSLISTINGSCONDITIONSUB_CONDITION,

            GetItemsResource::OFFERSLISTINGSAVAILABILITYMAX_ORDER_QUANTITY,
            GetItemsResource::OFFERSLISTINGSAVAILABILITYMESSAGE,
            GetItemsResource::OFFERSLISTINGSAVAILABILITYMIN_ORDER_QUANTITY,
            GetItemsResource::OFFERSLISTINGSAVAILABILITYTYPE,

            GetItemsResource::PARENT_ASIN,
            GetItemsResource::ITEM_INFOTITLE,
            GetItemsResource::ITEM_INFOPRODUCT_INFO,
            GetItemsResource::ITEM_INFOFEATURES,
            GetItemsResource::ITEM_INFOMANUFACTURE_INFO,
            GetItemsResource::ITEM_INFOCONTENT_RATING,
            GetItemsResource::ITEM_INFOCONTENT_INFO,
            GetItemsResource::ITEM_INFOTECHNICAL_INFO,
            GetItemsResource::ITEM_INFOEXTERNAL_IDS,
            GetItemsResource::ITEM_INFOCLASSIFICATIONS,
            GetItemsResource::ITEM_INFOBY_LINE_INFO,

            GetItemsResource::IMAGESPRIMARYSMALL,
            GetItemsResource::IMAGESPRIMARYMEDIUM,
            GetItemsResource::IMAGESPRIMARYLARGE,
            GetItemsResource::IMAGESVARIANTSSMALL,
            GetItemsResource::IMAGESVARIANTSMEDIUM,
            GetItemsResource::IMAGESVARIANTSLARGE,

//            GetItemsResource::BROWSE_NODE_INFOWEBSITE_SALES_RANK,
            GetItemsResource::BROWSE_NODE_INFOBROWSE_NODESSALES_RANK,
        );

        # Forming the request
        $getItemsRequest = new GetItemsRequest();
        $getItemsRequest->setItemIds($itemIds);
        $getItemsRequest->setPartnerTag($partnerTag);
        $getItemsRequest->setPartnerType(PartnerType::ASSOCIATES);
        $getItemsRequest->setResources($resources);

        # Validating request
        $invalidPropertyList = $getItemsRequest->listInvalidProperties();
        $length = count($invalidPropertyList);
        if ($length > 0) {
            $errorMsg = "Error forming the request: ";
            $errorMsg .= implode(', ', $invalidPropertyList);
            throw new ApiException($errorMsg);
        }

        try {


            if (is_array($asin)) {
                $requestToken = md5(implode('', [implode('-',$asin), $countryCode, $partnerTag]));
            } else {
                $requestToken = md5(implode('', [$asin, $countryCode, $partnerTag]));
            }

            if (Asa_Util_Buffer::exists($requestToken, 'item-lookup-response') && Asa_Util_Buffer::exists($requestToken, 'item-lookup-body')) {
                $response = Asa_Util_Buffer::get($requestToken, 'item-lookup-response');
                $body = Asa_Util_Buffer::get($requestToken, 'item-lookup-body');
            } else {
                // get the response
                list($response, $statusCode, $headers, $body) = $apiInstance->getItemsWithHttpInfo($getItemsRequest);
                Asa_Util_Buffer::set($requestToken, $response, 'item-lookup-response');
                Asa_Util_Buffer::set($requestToken, $response, 'item-lookup-body');
            }

        } catch (ApiException $exception) {
            $errorMsg = "Error calling PA-API 5.0!" . PHP_EOL;
            $errorMsg .= "HTTP Status Code: " . $exception->getCode() . PHP_EOL;

            if ($exception->getResponseObject() instanceof ProductAdvertisingAPIClientException) {
                $errors = $exception->getResponseObject()->getErrors();
                foreach ($errors as $error) {
                    $errorMsg .= "Error Type: " . $error->getCode() . PHP_EOL;
                    $errorMsg .= "Error Message: " . $error->getMessage() . PHP_EOL;
                }
            } else {
                $errorMsg .= "Error Message: " . $exception->getMessage() . PHP_EOL;
//                $errorMsg .= "Error response body: " . $exception->getResponseBody() . PHP_EOL;
            }
            throw new ApiException($errorMsg);

        } catch (Exception $exception) {
            $errorMsg = "Error Message: " . $exception->getMessage() . PHP_EOL;
            throw new ApiException($errorMsg);
        }

        require_once __DIR__ . '/Amazon/Item/PaApi5.php';

        if ($response instanceof AsaAmazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResponse && !empty($body)) {
            $result = json_decode($body, true);

            if (isset($result['ItemsResult']['Items']) && !empty($result['ItemsResult']['Items'])) {
                $result = $result['ItemsResult']['Items'][0];
                return new Asa_Service_Amazon_Item_PaApi5($result);
            }
        }
        return null;
    }

    /**
     * @param array $options
     * @return mixed
     * @throws ApiException
     */
    public function itemSearch(array $options)
    {
        $config = new Configuration();

        $config->setAccessKey($this->getAccessKeyId());
        $config->setSecretKey($this->getSecretAccessKey());

        $countryCode = $this->getLocale();
        $partnerTag = $this->getAssociateTag();

        $config->setHost($this->getHost($countryCode));
        $config->setRegion($this->getRegion($countryCode));

        $apiInstance = self::_getApiInstance($config);

        // todo
        $searchIndex = "All";
        $itemCount = 10;

        $resources = array(
            SearchItemsResource::ITEM_INFOTITLE,
            SearchItemsResource::ITEM_INFOCLASSIFICATIONS,

            SearchItemsResource::OFFERSLISTINGSPRICE,
            SearchItemsResource::OFFERSLISTINGSPROGRAM_ELIGIBILITYIS_PRIME_EXCLUSIVE,
            SearchItemsResource::OFFERSLISTINGSPROGRAM_ELIGIBILITYIS_PRIME_PANTRY,
            SearchItemsResource::OFFERSLISTINGSPROMOTIONS,
            SearchItemsResource::OFFERSLISTINGSSAVING_BASIS,
            SearchItemsResource::OFFERSSUMMARIESHIGHEST_PRICE,
            SearchItemsResource::OFFERSSUMMARIESLOWEST_PRICE,
            SearchItemsResource::OFFERSSUMMARIESOFFER_COUNT,
            SearchItemsResource::OFFERSLISTINGSIS_BUY_BOX_WINNER,
            SearchItemsResource::OFFERSLISTINGSDELIVERY_INFOSHIPPING_CHARGES,
            SearchItemsResource::OFFERSLISTINGSDELIVERY_INFOIS_PRIME_ELIGIBLE,
            SearchItemsResource::OFFERSLISTINGSDELIVERY_INFOIS_FREE_SHIPPING_ELIGIBLE,
            SearchItemsResource::OFFERSLISTINGSDELIVERY_INFOIS_AMAZON_FULFILLED,
            SearchItemsResource::OFFERSLISTINGSCONDITION,
            SearchItemsResource::OFFERSLISTINGSCONDITIONSUB_CONDITION,

            SearchItemsResource::IMAGESPRIMARYSMALL,
            SearchItemsResource::IMAGESPRIMARYMEDIUM,
            SearchItemsResource::IMAGESPRIMARYLARGE,

            SearchItemsResource::BROWSE_NODE_INFOBROWSE_NODESSALES_RANK,
        );

        # Forming the request
        $searchItemsRequest = new SearchItemsRequest();
        $searchItemsRequest->setSearchIndex($searchIndex);
        $searchItemsRequest->setKeywords(urldecode($options['Keywords']));
        $searchItemsRequest->setItemCount($itemCount);
        $searchItemsRequest->setPartnerTag($partnerTag);
        $searchItemsRequest->setPartnerType(PartnerType::ASSOCIATES);
        $searchItemsRequest->setResources($resources);

        # Validating request
        $invalidPropertyList = $searchItemsRequest->listInvalidProperties();
        $length = count($invalidPropertyList);
        if ($length > 0) {
            $errorMsg = "Error forming the request: ";
            $errorMsg .= implode(', ', $invalidPropertyList);
            throw new ApiException($errorMsg);
        }

        try {
            list($response, $statusCode, $headers, $body) = $apiInstance->searchItemsWithHttpInfo($searchItemsRequest);

        } catch (ApiException $exception) {
            $errorMsg = "Error calling PA-API 5.0!" . PHP_EOL;
            $errorMsg .= "HTTP Status Code: " . $exception->getCode() . PHP_EOL;

            if ($exception->getResponseObject() instanceof ProductAdvertisingAPIClientException) {
                $errors = $exception->getResponseObject()->getErrors();
                foreach ($errors as $error) {
                    $errorMsg .= "Error Type: " . $error->getCode() . PHP_EOL;
                    $errorMsg .= "Error Message: " . $error->getMessage() . PHP_EOL;
                }
            } else {
                $errorMsg .= "Error Message: " . $exception->getMessage() . PHP_EOL;
//                $errorMsg .= "Error response body: " . $exception->getResponseBody() . PHP_EOL;
            }
            throw new ApiException($errorMsg);

        } catch (Exception $exception) {
            $errorMsg = "Error Message: " . $exception->getMessage() . PHP_EOL;
            throw new ApiException($errorMsg);
        }

        return $response;
    }

    public function testConnection()
    {
        $this->itemSearch(array('SearchIndex' => 'Books', 'Keywords' => 'php'));
    }

    /**
     * @param $config
     * @return DefaultApi
     */
    protected static function _getApiInstance($config)
    {
        $funcInc = [
            'AsaGuzzleHttp\choose_handler' => 'vendor/asaguzzlehttp/guzzle/src/functions_include.php',
            'AsaGuzzleHttp\Psr7\build_query' => 'vendor/asaguzzlehttp/psr7/src/functions.php',
            'AsaGuzzleHttp\Promise\promise_for' => 'vendor/asaguzzlehttp/promises/src/functions.php',
            'Promise\promise_for' => 'vendor/asaguzzlehttp/promises/src/functions.php',
        ];

        foreach ($funcInc as $function => $incPath) {
            if (!function_exists($function)) {
                $includePath = dirname(ASA_BASE_FILE) . DIRECTORY_SEPARATOR . $incPath;
                if (file_exists($includePath)) {
                    require_once $includePath;
                }
            }
        }

        return new DefaultApi(new AsaGuzzleHttp\Client(), $config);
    }

    /**
     * @return string
     */
    public function getAssociateTag()
    {
        return $this->_associate_tag;
    }

    /**
     * @param string  $associate_tag
     */
    public function setAssociateTag($associate_tag)
    {
        $this->_associate_tag = $associate_tag;
    }

    /**
     * @return string
     */
    public function getAccessKeyId()
    {
        return $this->_access_key_id;
    }

    /**
     * @param string $access_key_id
     */
    public function setAccessKeyId($access_key_id)
    {
        $this->_access_key_id = (string)$access_key_id;
    }

    /**
     * @return string
     */
    public function getSecretAccessKey()
    {
        return $this->_secret_access_key;
    }

    /**
     * @param string $secret_access_key
     */
    public function setSecretAccessKey($secret_access_key)
    {
        $this->_secret_access_key = (string)$secret_access_key;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->_locale = (string)$locale;
    }

    /**
     * @param $locale
     * @return bool
     */
    public function isValidLocale($locale)
    {
        return array_key_exists($locale, self::$stores);
    }

    /**
     * @param $locale
     * @return string|null
     */
    public function getHost($locale)
    {
        if (array_key_exists($locale, self::$stores)) {
            return self::$stores[$locale]['host'];
        }
        return null;
    }

    /**
     * @param $locale
     * @return string|null
     */
    public function getRegion($locale)
    {
        if (array_key_exists($locale, self::$stores)) {
            return self::$stores[$locale]['region'];
        }
        return null;
    }

}
