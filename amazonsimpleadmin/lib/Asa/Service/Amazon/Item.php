<?php
abstract class Asa_Service_Amazon_Item
{
    abstract public function getAsin();
    abstract public function getDetailPageURL();

    abstract public function getProductTypeName();
    abstract public function getTitle();
    abstract public function getSalesRank();

    abstract public function getOffersMainPrice();
    abstract public function getOffersLowestNewPrice();
    abstract public function getOffersLowestUsedPrice();
    abstract public function getOffersLowestCollectiblePrice();

    abstract public function getOffersTotalMain();
    abstract public function getOffersTotalNew();
    abstract public function getOffersTotalUsed();
    abstract public function getOffersTotalCollectible();
    abstract public function getOffersTotalRefurbished();
    abstract public function getOffersAmountSaved();
    abstract public function getOffersListPrice();
    abstract public function getOffersSalePrice();
    abstract public function getOfferPercentageSaved();

    abstract public function getTradeInValue();

    /**
     * @return bool
     */
    abstract public function getOfferIsPrime();
    abstract public function getIsAmazonFulfilled();
    abstract public function getIsFreeShippingEligible();
    abstract public function getOfferAvailabilityMessage();

    abstract public function getBrand(); // and to get brand away:
    abstract public function getBinding(); // prost! ;-)

    abstract public function getManufacturer();
    abstract public function getEAN();
    abstract public function getISBN();
    abstract public function getFormat();
    abstract public function getProductGroup();
    abstract public function getPublicationDate();
    abstract public function getReleaseDate();
    abstract public function getSize();
    abstract public function getEditorialReviews();
    abstract public function getFeatures();

    abstract public function getSmallImageURL();
    abstract public function getSmallImageHeight();
    abstract public function getSmallImageWidth();

    abstract public function getMediumImageURL();
    abstract public function getMediumImageHeight();
    abstract public function getMediumImageWidth();

    abstract public function getLargeImageURL();
    abstract public function getLargeImageHeight();
    abstract public function getLargeImageWidth();

    abstract public function getLanguages();
    abstract public function getAuthor();
    abstract public function getCreator();
    abstract public function getEdition();
    abstract public function getDirector();
    abstract public function getActor();
    abstract public function getArtist();
    abstract public function getPagesCount();


    public function getOffersLowestNewPriceAmount()
    {
        return $this->getOffersLowestNewPrice()['amount'];
    }

    public function getOffersLowestNewPriceCurrencyCode()
    {
        return $this->getOffersLowestNewPrice()['currency_code'];
    }

    public function getOffersLowestNewPriceFormattedPrice()
    {
        return $this->getOffersLowestNewPrice()['formatted_price'];
    }

    public function getOffersLowestUsedPriceAmount()
    {
        return $this->getOffersLowestUsedPrice()['amount'];
    }

    public function getOffersLowestUsedPriceCurrencyCode()
    {
        return $this->getOffersLowestUsedPrice()['currency_code'];
    }

    public function getOffersLowestUsedPriceFormattedPrice()
    {
        return $this->getOffersLowestUsedPrice()['formatted_price'];
    }

    public function getOffersLowestCollectiblePriceAmount()
    {
        return $this->getOffersLowestCollectiblePrice()['amount'];
    }

    public function getOffersLowestCollectiblePriceCurrencyCode()
    {
        return $this->getOffersLowestCollectiblePrice()['currency_code'];
    }

    public function getOffersLowestCollectiblePriceFormattedPrice()
    {
        return $this->getOffersLowestCollectiblePrice()['formatted_price'];
    }

    public function getOffersMainPriceAmount()
    {
        return $this->getOffersMainPrice()['amount'];
    }

    public function getOffersMainPriceCurrencyCode()
    {
        return $this->getOffersMainPrice()['currency_code'];
    }

    public function getOffersMainPriceFormattedPrice()
    {
        return $this->getOffersMainPrice()['formatted_price'];
    }

    public function getOffersAmountSavedAmount()
    {
        return $this->getOffersAmountSaved()['amount'];
    }

    public function getOffersAmountSavedCurrencyCode()
    {
        return $this->getOffersAmountSaved()['currency_code'];
    }

    public function getOffersAmountSavedFormattedPrice()
    {
        return $this->getOffersAmountSaved()['formatted_price'];
    }

    public function getOffersListPriceAmount()
    {
        return $this->getOffersListPrice()['amount'];
    }

    public function getOffersListPriceCurrencyCode()
    {
        return $this->getOffersListPrice()['currency_code'];
    }

    public function getOffersListPriceFormattedPrice()
    {
        return $this->getOffersListPrice()['formatted_price'];
    }

    public function getOffersSalePriceAmount()
    {
        return $this->getOffersSalePrice()['amount'];
    }

    public function getOffersSalePriceCurrencyCode()
    {
        return $this->getOffersSalePrice()['currency_code'];
    }

    public function getOffersSalePriceFormattedPrice()
    {
        return $this->getOffersSalePrice()['formatted_price'];
    }

    public function getTradeInValueAmount()
    {
        return $this->getTradeInValue()['amount'];
    }

    public function getTradeInValueCurrencyCode()
    {
        return $this->getTradeInValue()['currency_code'];
    }

    public function getTradeInValueFormattedPrice()
    {
        return $this->getTradeInValue()['formatted_price'];
    }
}
