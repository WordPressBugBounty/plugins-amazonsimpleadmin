<?php
class Asa_Service_Amazon_Item_PaApi5 extends Asa_Service_Amazon_Item
{
    /**
     * @var array
     */
    protected $_data;

    protected $OffersMainPrice = [
        'amount' => 0,
        'currency_code' => '',
        'formatted_price' => ''
    ];

    protected $OffersAmountSaved = [
        'amount' => 0,
        'currency_code' => '',
        'formatted_price' => ''
    ];

    protected $OffersListPrice = [
        'amount' => 0,
        'currency_code' => '',
        'formatted_price' => ''
    ];

    protected $OffersLowestNewPrice = [
        'amount' => 0,
        'currency_code' => '',
        'formatted_price' => ''
    ];

    protected $OffersLowestUsedPrice = [
        'amount' => 0,
        'currency_code' => '',
        'formatted_price' => ''
    ];

    protected $OffersLowestCollectiblePrice = [
        'amount' => 0,
        'currency_code' => '',
        'formatted_price' => ''
    ];

    protected $OffersSalePrice = [
        'amount' => 0,
        'currency_code' => '',
        'formatted_price' => ''
    ];

    protected $TradeInValue = [
        'amount' => 0,
        'currency_code' => '',
        'formatted_price' => ''
    ];

    protected $OffersTotalMain = 0;

    protected $OffersTotalNew = 0;

    protected $OffersTotalUsed = 0;

    protected $OffersTotalCollectible = 0;

    protected $OffersTotalRefurbished = 0;

    protected $OfferPercentageSaved;

    protected $OfferIsPrime = false;
    protected $IsAmazonFulfilled = false;
    protected $IsFreeShippingEligible = false;
    protected $OfferAvailabilityMessage;

    // V2-specific properties
    protected $AvailabilityType = null;           // IN_STOCK, IN_STOCK_SCARCE, etc.
    protected $AvailabilityMaxOrderQuantity = null;
    protected $AvailabilityMinOrderQuantity = null;
    protected $SavingBasisType = null;            // LIST_PRICE, RRP, WAS_PRICE
    protected $ViolatesMAP = false;
    protected $DealBadge = null;
    protected $DealEndDate = null;
    protected $OffersVersionUsed = null;          // 'V1', 'V2', or null (for debugging)

    protected $SmallImageURL;
    protected $SmallImageWidth;
    protected $SmallImageHeight;

    protected $MediumImageURL;
    protected $MediumImageWidth;
    protected $MediumImageHeight;

    protected $LargeImageURL;
    protected $LargeImageWidth;
    protected $LargeImageHeight;

    protected $ImageSets = [];


    /**
     * Asa2_Service_Amazon_Item_ArrayFromPaApi4XmlFromPaApi5Json constructor.
     * @param array $data
     */
    public function __construct($data)
    {
        if (is_string($data)) {
            if (is_serialized($data)) {
                $data = unserialize($data);
            } else {
                $data = json_decode($data, true);
            }
        }

        if (is_array($data)) {
            $this->_data = $data;
            $this->_init();
        }
    }

    protected function _init()
    {
        $this->_initOffers();
        $this->_initImages();
    }


    /**
     * @return string
     */
    public function getDetailPageURL()
    {
        if (!empty($this->_data['DetailPageURL'])) {
            return apply_filters('asa1_detail_page_url', $this->_data['DetailPageURL']);
        }
        return '';
    }

    /**
     * @return mixed
     */
    public function getProductTypeName()
    {
        return isset($this->_data['ItemAttributes']['ProductTypeName']) ? $this->_data['ItemAttributes']['ProductTypeName'] : null;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if (!empty($this->_data['ItemInfo']['Title']['DisplayValue'])) {
            return $this->_data['ItemInfo']['Title']['DisplayValue'];
        }
        return '';
    }

    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    public function offsetGet($offset)
    {
        $result = null;

        if (isset($this->_data[$offset])) {
            $result = $this->_data[$offset];
        }
        return  $result;
    }

    public function offsetSet($offset, $value)
    {
        if (!is_null($offset)) {
            $this->_data[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        // not supported
    }

    public function rewind() {
        reset($this->_data);
    }

    public function current() {
        return current($this->_data);
    }

    public function key() {
        return key($this->_data);
    }

    public function next() {
        next($this->_data);
    }

    public function valid() {
        return key($this->_data) !== null;
    }

    /**
     * @return array
     */
    public function getSalesRank()
    {
        $result = [];
        if (!empty($this->_data['BrowseNodeInfo']['BrowseNodes']) && is_array($this->_data['BrowseNodeInfo']['BrowseNodes'])) {
            foreach ($this->_data['BrowseNodeInfo']['BrowseNodes'] as $row) {
                if (isset($row['SalesRank'])) {
                    array_push($result, $row);
                }
            }
        }
        return $result;
    }

    protected function _initImages()
    {
        if (isset($this->_data['Images']['Primary'])) {

            if (isset($this->_data['Images']['Primary']['Small']['URL'])) {
                $this->SmallImageURL = $this->_data['Images']['Primary']['Small']['URL'];
            }
            if (isset($this->_data['Images']['Primary']['Small']['Height'])) {
                $this->SmallImageHeight = (int)$this->_data['Images']['Primary']['Small']['Height'];
            }
            if (isset($this->_data['Images']['Primary']['Small']['Width'])) {
                $this->SmallImageWidth = (int)$this->_data['Images']['Primary']['Small']['Width'];
            }


            if (isset($this->_data['Images']['Primary']['Medium']['URL'])) {
                $this->MediumImageURL = $this->_data['Images']['Primary']['Medium']['URL'];
            }
            if (isset($this->_data['Images']['Primary']['Medium']['Height'])) {
                $this->MediumImageHeight = (int)$this->_data['Images']['Primary']['Medium']['Height'];
            }
            if (isset($this->_data['Images']['Primary']['Medium']['Width'])) {
                $this->MediumImageWidth = (int)$this->_data['Images']['Primary']['Medium']['Width'];
            }


            if (isset($this->_data['Images']['Primary']['Large']['URL'])) {
                $this->LargeImageURL = $this->_data['Images']['Primary']['Large']['URL'];
            }
            if (isset($this->_data['Images']['Primary']['Large']['Height'])) {
                $this->LargeImageHeight = (int)$this->_data['Images']['Primary']['Large']['Height'];
            }
            if (isset($this->_data['Images']['Primary']['Large']['Width'])) {
                $this->LargeImageWidth = (int)$this->_data['Images']['Primary']['Large']['Width'];
            }

        }

    }

    /**
     * Check if Offers V2 debug logging is enabled
     * To enable, add this to wp-config.php:
     * define('ASA1_DEBUG_OFFERS_V2', true);
     *
     * @return bool
     */
    private function _isOffersV2DebugEnabled()
    {
        return defined('ASA1_DEBUG_OFFERS_V2') && ASA1_DEBUG_OFFERS_V2 === true;
    }

    protected function _initOffers()
    {
        // V2-First Strategy: Check if OffersV2 data exists
        $hasV2Data = !empty($this->_data['OffersV2']['Listings']) &&
                     is_array($this->_data['OffersV2']['Listings']);

        if ($hasV2Data) {
            // V2 is available - use it exclusively
            $this->OffersVersionUsed = 'V2';
            $this->_processV2Offers();

            // Debug logging
            if ($this->_isOffersV2DebugEnabled()) {
                error_log('[ASA1 OffersV2] Using Offers V2 data (new structure) for ASIN: ' . $this->getAsin());
            }
        } else {
            // Fallback to V1 for older responses or when V2 is not available
            $hasV1Data = !empty($this->_data['Offers']['Listings']) &&
                         is_array($this->_data['Offers']['Listings']);
            $this->OffersVersionUsed = $hasV1Data ? 'V1' : null;
            $this->_processV1Offers();

            // Debug logging
            if ($this->_isOffersV2DebugEnabled()) {
                if ($hasV1Data) {
                    error_log('[ASA1 OffersV2] Using Offers V1 data (fallback) for ASIN: ' . $this->getAsin());
                } else {
                    error_log('[ASA1 OffersV2] No offers data available for ASIN: ' . $this->getAsin());
                }
            }
        }
    }

    /**
     * Process Offers V1 structure (fallback for backward compatibility)
     */
    protected function _processV1Offers()
    {
        if (!empty($this->_data['Offers']['Listings']) && is_array($this->_data['Offers']['Listings'])) {

            foreach ($this->_data['Offers']['Listings'] as $listing) {
                if (!empty($listing['IsBuyBoxWinner']) && (int)$listing['IsBuyBoxWinner'] === 1) {
                    $listing1 = $listing;
                    break;
                }
            }

            if (!empty($listing1['Price'])) {
                $this->OffersTotalMain = 1;
                $this->OffersMainPrice = [
                    'amount' => $this->_getAmount($listing1['Price']['Amount']),
                    'currency_code' => $listing1['Price']['Currency'],
                    'formatted_price' => $listing1['Price']['DisplayAmount']
                ];
            }

            if (!empty($listing1['SavingBasis'])) {
                $this->OffersListPrice = [
                    'amount' => $this->_getAmount($listing1['SavingBasis']['Amount']),
                    'currency_code' => $listing1['SavingBasis']['Currency'],
                    'formatted_price' => $listing1['SavingBasis']['DisplayAmount']
                ];
            }

            if (!empty($listing1['Price']['Savings'])) {
                $this->OffersAmountSaved = [
                    'amount' => $this->_getAmount($listing1['Price']['Savings']['Amount']),
                    'currency_code' => $listing1['Price']['Savings']['Currency'],
                    'formatted_price' => $listing1['Price']['Savings']['DisplayAmount']
                ];
                if (!empty($listing1['Price']['Savings']['Percentage'])) {
                    $this->OfferPercentageSaved = $listing1['Price']['Savings']['Percentage'];
                }
            }

            if (!empty($listing1['Availability'])) {
                if (!empty($listing1['Availability']['Message'])) {
                    $this->OfferAvailabilityMessage = $listing1['Availability']['Message'];
                }
            }

            if (!empty($listing1['DeliveryInfo'])) {
                if (!empty($listing1['DeliveryInfo']['IsPrimeEligible']) && (int)$listing1['DeliveryInfo']['IsPrimeEligible'] === 1) {
                    $this->OfferIsPrime = true;
                }
                if (!empty($listing1['DeliveryInfo']['IsAmazonFulfilled']) && (int)$listing1['DeliveryInfo']['IsAmazonFulfilled'] === 1) {
                    $this->IsAmazonFulfilled = true;
                }
                if (!empty($listing1['DeliveryInfo']['IsFreeShippingEligible']) && (int)$listing1['DeliveryInfo']['IsFreeShippingEligible'] === 1) {
                    $this->IsFreeShippingEligible = true;
                }
            }
        }

        if (!empty($this->_data['Offers']['Summaries']) && is_array($this->_data['Offers']['Summaries'])) {

            foreach ($this->_data['Offers']['Summaries'] as $row) {

                if (isset($row['Condition']) && strtolower($row['Condition']['Value']) === 'new') {
                    $this->OffersLowestNewPrice = [
                        'amount' => $this->_getAmount($row['LowestPrice']['Amount']),
                        'currency_code' => $row['LowestPrice']['Currency'],
                        'formatted_price' => $row['LowestPrice']['DisplayAmount']
                    ];
                    $this->OffersTotalNew = (int)$row['OfferCount'];
                }

                if (isset($row['Condition']) && strtolower($row['Condition']['Value']) === 'used') {
                    $this->OffersLowestUsedPrice = [
                        'amount' => $this->_getAmount($row['LowestPrice']['Amount']),
                        'currency_code' => $row['LowestPrice']['Currency'],
                        'formatted_price' => $row['LowestPrice']['DisplayAmount']
                    ];
                    $this->OffersTotalUsed = (int)$row['OfferCount'];
                }

                if (isset($row['Condition']) && strtolower($row['Condition']['Value']) === 'collectible') {
                    $this->OffersLowestCollectiblePrice = [
                        'amount' => $this->_getAmount($row['LowestPrice']['Amount']),
                        'currency_code' => $row['LowestPrice']['Currency'],
                        'formatted_price' => $row['LowestPrice']['DisplayAmount']
                    ];
                    $this->OffersTotalCollectible = (int)$row['OfferCount'];
                }

                if (isset($row['Condition']) && strtolower($row['Condition']['Value']) === 'refurbished') {
                    $this->OffersTotalRefurbished = (int)$row['OfferCount'];
                }
            }
        }
    }

    /**
     * Process Offers V2 structure (new, preferred when available)
     */
    protected function _processV2Offers()
    {
        if (empty($this->_data['OffersV2']['Listings'])) {
            return;
        }

        // Find the Buy Box winner listing
        $listing1 = null;
        foreach ($this->_data['OffersV2']['Listings'] as $listing) {
            if (!empty($listing['IsBuyBoxWinner']) && (int)$listing['IsBuyBoxWinner'] === 1) {
                $listing1 = $listing;
                break;
            }
        }

        // If no Buy Box winner, take the first listing
        if (empty($listing1)) {
            $listing1 = $this->_data['OffersV2']['Listings'][0];
        }

        // Price (nested in Money object in V2)
        if (!empty($listing1['Price']['Money'])) {
            $this->OffersTotalMain = 1;
            $price = $listing1['Price']['Money'];
            $this->OffersMainPrice = [
                'amount' => $this->_getAmount($price['Amount']),
                'currency_code' => $price['Currency'],
                'formatted_price' => $price['DisplayAmount']
            ];
        }

        // SavingBasis with type information (V2 enhancement)
        // IMPORTANT: In V2, SavingBasis is INSIDE Price object!
        if (!empty($listing1['Price']['SavingBasis']['Money'])) {
            $savingBasis = $listing1['Price']['SavingBasis'];
            $this->OffersListPrice = [
                'amount' => $this->_getAmount($savingBasis['Money']['Amount']),
                'currency_code' => $savingBasis['Money']['Currency'],
                'formatted_price' => $savingBasis['Money']['DisplayAmount']
            ];

            // NEW: Type information (LIST_PRICE, RRP, WAS_PRICE, etc.)
            if (isset($savingBasis['SavingBasisType'])) {
                $this->SavingBasisType = $savingBasis['SavingBasisType'];
            }
        }

        // Savings (still available in V2)
        if (!empty($listing1['Price']['Savings']['Money'])) {
            $savings = $listing1['Price']['Savings'];
            $this->OffersAmountSaved = [
                'amount' => $this->_getAmount($savings['Money']['Amount']),
                'currency_code' => $savings['Money']['Currency'],
                'formatted_price' => $savings['Money']['DisplayAmount']
            ];
            if (!empty($savings['Percentage'])) {
                $this->OfferPercentageSaved = $savings['Percentage'];
            }
        }

        // V2-specific: Enhanced availability details
        if (!empty($listing1['Availability'])) {
            $availability = $listing1['Availability'];

            // NEW: Structured availability type
            if (isset($availability['Type'])) {
                $this->AvailabilityType = $availability['Type'];
            }

            // NEW: Order quantity constraints
            if (isset($availability['MaxOrderQuantity'])) {
                $this->AvailabilityMaxOrderQuantity = (int)$availability['MaxOrderQuantity'];
            }
            if (isset($availability['MinOrderQuantity'])) {
                $this->AvailabilityMinOrderQuantity = (int)$availability['MinOrderQuantity'];
            }

            // Message still available
            if (isset($availability['Message'])) {
                $this->OfferAvailabilityMessage = $availability['Message'];
            }
        }

        // DeliveryInfo (same structure as V1)
        if (!empty($listing1['DeliveryInfo'])) {
            if (!empty($listing1['DeliveryInfo']['IsPrimeEligible']) && (int)$listing1['DeliveryInfo']['IsPrimeEligible'] === 1) {
                $this->OfferIsPrime = true;
            }
            if (!empty($listing1['DeliveryInfo']['IsAmazonFulfilled']) && (int)$listing1['DeliveryInfo']['IsAmazonFulfilled'] === 1) {
                $this->IsAmazonFulfilled = true;
            }
            if (!empty($listing1['DeliveryInfo']['IsFreeShippingEligible']) && (int)$listing1['DeliveryInfo']['IsFreeShippingEligible'] === 1) {
                $this->IsFreeShippingEligible = true;
            }
        }

        // NEW: MAP violation flag
        if (isset($listing1['ViolatesMAP'])) {
            $this->ViolatesMAP = (bool)$listing1['ViolatesMAP'];
        }

        // NEW: Deal details (optional, for future use)
        if (!empty($listing1['DealDetails'])) {
            if (isset($listing1['DealDetails']['DealBadge'])) {
                $this->DealBadge = $listing1['DealDetails']['DealBadge'];
            }
            if (isset($listing1['DealDetails']['DealEndDate'])) {
                $this->DealEndDate = $listing1['DealDetails']['DealEndDate'];
            }
        }

        // Aggregate offers by condition from V2 listings
        // Creators API does NOT provide Offers.Summaries, so we must aggregate manually
        $conditionOffers = $this->_aggregateV2OffersByCondition($this->_data['OffersV2']['Listings']);
        $this->_processV2ConditionPrices($conditionOffers);

        // Fallback: Also check Offers.Summaries if available (for PA API 5 with V2 resources)
        if (!empty($this->_data['Offers']['Summaries']) && is_array($this->_data['Offers']['Summaries'])) {

            foreach ($this->_data['Offers']['Summaries'] as $row) {

                if (isset($row['Condition']) && strtolower($row['Condition']['Value']) === 'new') {
                    // Only overwrite if not already set from V2 aggregation
                    if ($this->OffersLowestNewPrice['amount'] === 0) {
                        $this->OffersLowestNewPrice = [
                            'amount' => $this->_getAmount($row['LowestPrice']['Amount']),
                            'currency_code' => $row['LowestPrice']['Currency'],
                            'formatted_price' => $row['LowestPrice']['DisplayAmount']
                        ];
                    }
                    if ($this->OffersTotalNew === 0) {
                        $this->OffersTotalNew = (int)$row['OfferCount'];
                    }
                }

                if (isset($row['Condition']) && strtolower($row['Condition']['Value']) === 'used') {
                    if ($this->OffersLowestUsedPrice['amount'] === 0) {
                        $this->OffersLowestUsedPrice = [
                            'amount' => $this->_getAmount($row['LowestPrice']['Amount']),
                            'currency_code' => $row['LowestPrice']['Currency'],
                            'formatted_price' => $row['LowestPrice']['DisplayAmount']
                        ];
                    }
                    if ($this->OffersTotalUsed === 0) {
                        $this->OffersTotalUsed = (int)$row['OfferCount'];
                    }
                }

                if (isset($row['Condition']) && strtolower($row['Condition']['Value']) === 'collectible') {
                    if ($this->OffersLowestCollectiblePrice['amount'] === 0) {
                        $this->OffersLowestCollectiblePrice = [
                            'amount' => $this->_getAmount($row['LowestPrice']['Amount']),
                            'currency_code' => $row['LowestPrice']['Currency'],
                            'formatted_price' => $row['LowestPrice']['DisplayAmount']
                        ];
                    }
                    if ($this->OffersTotalCollectible === 0) {
                        $this->OffersTotalCollectible = (int)$row['OfferCount'];
                    }
                }

                if (isset($row['Condition']) && strtolower($row['Condition']['Value']) === 'refurbished') {
                    if ($this->OffersTotalRefurbished === 0) {
                        $this->OffersTotalRefurbished = (int)$row['OfferCount'];
                    }
                }
            }
        }
    }

    /**
     * Aggregate OffersV2 listings by condition
     *
     * Creators API returns individual listings that must be aggregated manually.
     * PA API 5 Offers.Summaries provides pre-aggregated data, but Creators API does not.
     *
     * @param array $listings The OffersV2.Listings array from API response
     * @return array Aggregated offer data per condition
     */
    protected function _aggregateV2OffersByCondition(array $listings)
    {
        $conditionOffers = [
            'new' => ['count' => 0, 'lowest_price' => null, 'lowest_listing' => null],
            'used' => ['count' => 0, 'lowest_price' => null, 'lowest_listing' => null],
            'collectible' => ['count' => 0, 'lowest_price' => null, 'lowest_listing' => null],
            'refurbished' => ['count' => 0, 'lowest_price' => null, 'lowest_listing' => null],
        ];

        foreach ($listings as $listing) {
            // Get condition (normalize to lowercase)
            $condition = strtolower($listing['Condition']['Value'] ?? '');

            if (!isset($conditionOffers[$condition])) {
                continue;
            }

            // Get price amount from V2 structure (Price.Money.Amount)
            $priceAmount = null;
            if (!empty($listing['Price']['Money']['Amount'])) {
                $priceAmount = (float) $listing['Price']['Money']['Amount'];
            }

            if ($priceAmount === null) {
                continue;
            }

            // Increment count
            $conditionOffers[$condition]['count']++;

            // Track lowest price
            if ($conditionOffers[$condition]['lowest_price'] === null ||
                $priceAmount < $conditionOffers[$condition]['lowest_price']) {
                $conditionOffers[$condition]['lowest_price'] = $priceAmount;
                $conditionOffers[$condition]['lowest_listing'] = $listing;
            }
        }

        return $conditionOffers;
    }

    /**
     * Convert aggregated V2 offers to ASA1 item format
     *
     * @param array $conditionOffers Aggregated offers from _aggregateV2OffersByCondition
     */
    protected function _processV2ConditionPrices(array $conditionOffers)
    {
        // New condition
        if ($conditionOffers['new']['count'] > 0 && $conditionOffers['new']['lowest_listing']) {
            $listing = $conditionOffers['new']['lowest_listing'];
            $this->OffersLowestNewPrice = [
                'amount' => $this->_getAmount($conditionOffers['new']['lowest_price']),
                'currency_code' => $listing['Price']['Money']['Currency'] ?? '',
                'formatted_price' => $listing['Price']['Money']['DisplayAmount'] ?? '',
            ];
            $this->OffersTotalNew = $conditionOffers['new']['count'];
        }

        // Used condition
        if ($conditionOffers['used']['count'] > 0 && $conditionOffers['used']['lowest_listing']) {
            $listing = $conditionOffers['used']['lowest_listing'];
            $this->OffersLowestUsedPrice = [
                'amount' => $this->_getAmount($conditionOffers['used']['lowest_price']),
                'currency_code' => $listing['Price']['Money']['Currency'] ?? '',
                'formatted_price' => $listing['Price']['Money']['DisplayAmount'] ?? '',
            ];
            $this->OffersTotalUsed = $conditionOffers['used']['count'];
        }

        // Collectible condition
        if ($conditionOffers['collectible']['count'] > 0 && $conditionOffers['collectible']['lowest_listing']) {
            $listing = $conditionOffers['collectible']['lowest_listing'];
            $this->OffersLowestCollectiblePrice = [
                'amount' => $this->_getAmount($conditionOffers['collectible']['lowest_price']),
                'currency_code' => $listing['Price']['Money']['Currency'] ?? '',
                'formatted_price' => $listing['Price']['Money']['DisplayAmount'] ?? '',
            ];
            $this->OffersTotalCollectible = $conditionOffers['collectible']['count'];
        }

        // Refurbished condition
        if ($conditionOffers['refurbished']['count'] > 0 && $conditionOffers['refurbished']['lowest_listing']) {
            $listing = $conditionOffers['refurbished']['lowest_listing'];
            // Note: ASA1 doesn't have OffersLowestRefurbishedPrice property, only count
            $this->OffersTotalRefurbished = $conditionOffers['refurbished']['count'];
        }
    }

    /**
     * @return array
     */
    public function getOffersLowestNewPrice()
    {
        return $this->OffersLowestNewPrice;
    }

    /**
     * @return array
     */
    public function getOffersLowestUsedPrice()
    {
        return $this->OffersLowestUsedPrice;
    }

    /**
     * @return array
     */
    public function getOffersLowestCollectiblePrice()
    {
        return $this->OffersLowestCollectiblePrice;
    }

    /**
     * @return int
     */
    public function getOffersTotalNew()
    {
        return $this->OffersTotalNew;
    }

    /**
     * @return int
     */
    public function getOffersTotalUsed()
    {
        return $this->OffersTotalUsed;
    }

    /**
     * @return int
     */
    public function getOffersTotalCollectible()
    {
        return $this->OffersTotalCollectible;
    }

    public function getOffersTotalRefurbished()
    {
        return $this->OffersTotalRefurbished;
    }

    /**
     * @return int
     */
    public function getOffersTotalMain()
    {
        return $this->OffersTotalMain;
    }

    public function getOffersMainPrice()
    {
        return $this->OffersMainPrice;
    }

    public function getOffersAmountSaved()
    {
        return $this->OffersAmountSaved;
    }

    public function getOffersListPrice()
    {
        return $this->OffersListPrice;
    }

    protected function _getAmount($n)
    {
        return (int)str_replace([',','.'], '', number_format($n, 2, '.', ''));
    }

    /**
     * @todo
     * @return array
     */
    public function getOffersSalePrice()
    {
        return $this->OffersSalePrice;
    }

    public function getOfferPercentageSaved()
    {
        return $this->OfferPercentageSaved;
    }

    public function getOfferIsPrime()
    {
        return $this->OfferIsPrime;
    }

    public function getIsAmazonFulfilled()
    {
        return $this->IsAmazonFulfilled;
    }

    public function getIsFreeShippingEligible()
    {
        return $this->IsFreeShippingEligible;
    }

    public function getOfferAvailabilityMessage()
    {

    }

    public function getBinding()
    {
        if (isset($this->_data['ItemInfo']['Classifications']['Binding'])) {
            return $this->_data['ItemInfo']['Classifications']['Binding']['DisplayValue'];
        }
        return null;
    }

    public function getBrand()
    {
        if (isset($this->_data['ItemInfo']['ByLineInfo']['Brand'])) {
            return $this->_data['ItemInfo']['ByLineInfo']['Brand']['DisplayValue'];
        }
        return null;
    }

    public function getManufacturer()
    {
        if (isset($this->_data['ItemInfo']['ByLineInfo']['Manufacturer'])) {
            return $this->_data['ItemInfo']['ByLineInfo']['Manufacturer']['DisplayValue'];
        }
        return null;
    }

    public function getEAN()
    {
        if (isset($this->_data['ItemInfo']['ExternalIds']['EANs']['DisplayValues'])) {
            if (is_array($this->_data['ItemInfo']['ExternalIds']['EANs']['DisplayValues'])) {
                return implode(', ', $this->_data['ItemInfo']['ExternalIds']['EANs']['DisplayValues']);
            }
        }
        return null;
    }

    public function getFormat()
    {
        if (isset($this->_data['ItemInfo']['TechnicalInfo']['Formats']['DisplayValues'])) {
            if (is_array($this->_data['ItemInfo']['TechnicalInfo']['Formats']['DisplayValues'])) {
                return implode(', ', $this->_data['ItemInfo']['TechnicalInfo']['Formats']['DisplayValues']);
            }
        }
        return null;
    }

    public function getProductGroup()
    {
        if (isset($this->_data['ItemInfo']['Classifications']['ProductGroup']['DisplayValue'])) {
            return $this->_data['ItemInfo']['Classifications']['ProductGroup']['DisplayValue'];
        }
        return null;
    }

    public function getPublicationDate()
    {
        if (isset($this->_data['ItemInfo']['ContentInfo']['PublicationDate']['DisplayValue'])) {
            return $this->_data['ItemInfo']['ContentInfo']['PublicationDate']['DisplayValue'];
        }
        return null;
    }

    public function getReleaseDate()
    {
        if (isset($this->_data['ItemInfo']['ProductInfo']['ReleaseDate']['DisplayValue'])) {
            return $this->_data['ItemInfo']['ProductInfo']['ReleaseDate']['DisplayValue'];
        }
        return null;
    }

    public function getSize()
    {
        if (isset($this->_data['ItemInfo']['ProductInfo']['Size']['DisplayValue'])) {
            return $this->_data['ItemInfo']['ProductInfo']['Size']['DisplayValue'];
        }
        return null;
    }

    /**
     * @return array
     */
    public function getTradeInValue()
    {
        if (isset($this->_data['ItemInfo']['TradeInInfo']) &&
            isset($this->_data['ItemInfo']['TradeInInfo']['IsEligibleForTradeIn']) &&
            boolval($this->_data['ItemInfo']['TradeInInfo']['IsEligibleForTradeIn']) === true
        ) {
            $this->TradeInValue = [
                'amount' => $this->_getAmount($this->_data['ItemInfo']['TradeInInfo']['Price']['Amount']),
                'currency_code' => $this->_data['ItemInfo']['TradeInInfo']['Price']['Currency'],
                'formatted_price' => $this->_data['ItemInfo']['TradeInInfo']['Price']['DisplayAmount']
            ];
        }

        return $this->TradeInValue;
    }

    /**
     * currently not supported by PA API 5
     * @return |null
     */
    public function getEditorialReviews()
    {
        return null;
    }

    public function getFeatures()
    {
        if (isset($this->_data['ItemInfo']['Features']['DisplayValues'])) {
            return $this->_data['ItemInfo']['Features']['DisplayValues'];
        }
        return null;
    }

    public function getSmallImageURL()
    {
        return $this->SmallImageURL;
    }

    public function getSmallImageHeight()
    {
        return $this->SmallImageHeight;
    }

    public function getSmallImageWidth()
    {
        return $this->SmallImageWidth;
    }

    public function getMediumImageURL()
    {
        return $this->MediumImageURL;
    }

    public function getMediumImageHeight()
    {
        return $this->MediumImageHeight;
    }

    public function getMediumImageWidth()
    {
        return $this->MediumImageWidth;
    }

    public function getLargeImageURL()
    {
        return $this->LargeImageURL;
    }

    public function getLargeImageHeight()
    {
        return $this->LargeImageHeight;
    }

    public function getLargeImageWidth()
    {
        return $this->LargeImageWidth;
    }

    public function getLanguages()
    {
        $result = [];

        if (isset($this->_data['ItemInfo']['ContentInfo']['Languages']['DisplayValues'])) {
            $data = $this->_data['ItemInfo']['ContentInfo']['Languages']['DisplayValues'];
            if (is_array($data)) {
                foreach ($data as $lang) {
                    if (isset($lang['DisplayValue'])) {
                        $newLang = [
                            'Name' => $lang['DisplayValue']
                        ];
                        if (isset($lang['Type'])) {
                            $newLang['Type'] = $lang['Type'];
                        }
                        array_push($result, $newLang);
                    }
                }
            }
        }

        return $result;
    }

    public function addCustomValue($key, $value, $overwrite = false)
    {
        if (!$this->hasCustomValue($key) || $overwrite) {
            $this->_data['Asa2Custom'][$key] = $value;
        }
    }

    public function hasCustomValue($key)
    {
        return isset($this->_data['Asa2Custom'][$key]);
    }

    public function getCustomValue($key)
    {
        if ($this->hasCustomValue($key)) {
            return $this->_data['Asa2Custom'][$key];
        }
        return null;
    }

    public function hasCustomValues()
    {
        $values = $this->getCustomValues();
        return count($values) > 0;
    }

    /**
     * @return array
     */
    public function getCustomValues()
    {
        if (isset($this->_data['Asa2Custom'])) {
            return $this->_data['Asa2Custom'];
        }
        return [];
    }

    public function __toString()
    {
        if ($this->hasCustomValues()) {
            $dynVal = [];
            foreach ($this->getCustomValues() as $k => $v) {
                $dynVal[$k] = esc_attr($v);
            }
            $this->_data['DynamicValues'] = $dynVal;
        }

        return json_encode($this->_data);
    }


    public function getAsin()
    {
        if (!empty($this->_data['ASIN'])) {
            return $this->_data['ASIN'];
        }
        return '';
    }

    /**
     * @return array|null
     */
    public function getAuthor()
    {
        if (isset($this->_data['ItemInfo']['ByLineInfo']['Contributors']) &&
            is_array($this->_data['ItemInfo']['ByLineInfo']['Contributors'])) {

            $result = [];
            foreach ($this->_data['ItemInfo']['ByLineInfo']['Contributors'] as $contributor) {
                array_push($result, sprintf('%s (%s)', $contributor['Name'], $contributor['Role']));
            }
            return $result;
        }
        return null;
    }

    /**
     * @return string
     */
    public function getCreator()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getEdition()
    {
        if (isset($this->_data['ItemInfo']['ContentInfo']['Edition']['DisplayValue'])) {
            $result = $this->_data['ItemInfo']['ContentInfo']['Edition']['DisplayValue'];
            if (isset($this->_data['ItemInfo']['ContentInfo']['Edition']['Label'])) {
                $result .= ' ' . $this->_data['ItemInfo']['ContentInfo']['Edition']['Label'];
            }
            return $result;
        }
        return null;
    }

    /**
     * @return string
     */
    public function getDirector()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getActor()
    {
        return $this->getAuthor();
    }

    /**
     * @return string
     */
    public function getArtist()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getPagesCount()
    {
        if (isset($this->_data['ItemInfo']['ContentInfo']['PagesCount']['DisplayValue'])) {
            return $this->_data['ItemInfo']['ContentInfo']['PagesCount']['DisplayValue'];
        }
        return null;
    }

    /**
     * @return string
     */
    public function getISBN()
    {
        if (isset($this->_data['ItemInfo']['ExternalIds']['ISBNs']['DisplayValues'])) {
            if (is_array($this->_data['ItemInfo']['ExternalIds']['ISBNs']['DisplayValues'])) {
                return implode(', ', $this->_data['ItemInfo']['ExternalIds']['EANs']['DisplayValues']);
            }
        }
        return null;
    }

    /**
     * V2-specific getters
     */

    /**
     * Get the availability type (V2 field)
     * @return string|null IN_STOCK, IN_STOCK_SCARCE, OUT_OF_STOCK, etc.
     */
    public function getAvailabilityType()
    {
        return $this->AvailabilityType;
    }

    /**
     * Get maximum order quantity (V2 field)
     * @return int|null
     */
    public function getAvailabilityMaxOrderQuantity()
    {
        return $this->AvailabilityMaxOrderQuantity;
    }

    /**
     * Get minimum order quantity (V2 field)
     * @return int|null
     */
    public function getAvailabilityMinOrderQuantity()
    {
        return $this->AvailabilityMinOrderQuantity;
    }

    /**
     * Get saving basis type (V2 field)
     * @return string|null LIST_PRICE, RRP, WAS_PRICE, etc.
     */
    public function getSavingBasisType()
    {
        return $this->SavingBasisType;
    }

    /**
     * Check if price violates Minimum Advertised Price (V2 field)
     * @return bool
     */
    public function getViolatesMAP()
    {
        return $this->ViolatesMAP;
    }

    /**
     * Get deal badge (V2 field)
     * @return string|null LIMITED_TIME_DEAL, LIGHTNING_DEAL, etc.
     */
    public function getDealBadge()
    {
        return $this->DealBadge;
    }

    /**
     * Get deal end date (V2 field)
     * @return string|null ISO 8601 date string
     */
    public function getDealEndDate()
    {
        return $this->DealEndDate;
    }

    /**
     * Get which offers version was used for processing (for debugging)
     * @return string|null 'V1', 'V2', or null
     */
    public function getOffersVersionUsed()
    {
        return $this->OffersVersionUsed;
    }


}
