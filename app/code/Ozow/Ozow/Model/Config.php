<?php
/*
 * Copyright (c) 2019 Ozow (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 * 
 * Released under the GNU General Public License
 */

// @codingStandardsIgnoreFile

namespace Ozow\Ozow\Model;

/**
 * Config model that is aware of all \Ozow\Ozow payment methods
 * Works with Ozow-specific system configuration
 * @SuppressWarnings(PHPMD.ExcesivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Config extends AbstractConfig
{

    /**
     * @var \Ozow\Ozow\Model\Ozow this is a model which we will use.
     */
    const METHOD_CODE = 'ozow';

    /**
     * Core
     * data @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $_supportedBuyerCountryCodes = ['ZA'];

    /**
     * Currency codes supported by Ozow methods
     * @var string[]
     */
    protected $_supportedCurrencyCodes = ['ZAR'];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $params
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\View\Asset\Repository
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->_logger = $logger;
        parent::__construct( $scopeConfig );
        $this->directoryHelper = $directoryHelper;
        $this->_storeManager   = $storeManager;
        $this->_assetRepo      = $assetRepo;

        $this->setMethod('ozow');
        $currentStoreId = $this->_storeManager->getStore()->getStoreId();
        $this->setStoreId($currentStoreId);
    }

    /**
     * Check whether method available for checkout or not
     * Logic based on merchant country, methods dependence
     *
     * @param string|null $methodCode
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isMethodAvailable( $methodCode = null )
    {
        return parent::isMethodAvailable( $methodCode );
    }

    /**
     * Return buyer country codes supported by Ozow
     *
     * @return string[]
     */
    public function getSupportedBuyerCountryCodes()
    {
        return $this->_supportedBuyerCountryCodes;
    }

    /**
     * Return merchant country code, use default country if it not specified in General settings
     *
     * @return string
     */
    public function getMerchantCountry()
    {
        return $this->directoryHelper->getDefaultCountry( $this->_storeId );
    }

    /**
     * Check whether method supported for specified country or not
     * Use $_methodCode and merchant country by default
     *
     * @param string|null $method
     * @param string|null $countryCode
     * @return bool
     */
    public function isMethodSupportedForCountry( $method = null, $countryCode = null )
    {
        if ( $method === null ) {
            $method = $this->getMethodCode();
        }

        if ( $countryCode === null ) {
            $countryCode = $this->getMerchantCountry();
        }

        return in_array( $method, $this->getCountryMethods( $countryCode ) );
    }

    /**
     * Return list of allowed methods for specified country iso code
     *
     * @param string|null $countryCode 2-letters iso code
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCountryMethods( $countryCode = null )
    {
        $countryMethods = [
            'other' => [
                self::METHOD_CODE,
            ],

        ];
        if ( $countryCode === null ) {
            return $countryMethods;
        }
        return isset( $countryMethods[$countryCode] ) ? $countryMethods[$countryCode] : $countryMethods['other'];
    }

    /**
     * Get Ozow "mark" image URL
     * TODO - Maybe this can be placed in the config xml
     *
     * @return string
     */
    public function getPaymentMarkImageUrl()
    {
        return $this->_assetRepo->getUrl( 'Ozow_Ozow::images/logo.png' );
    }

    /**
     * Get "What Is Ozow" localized URL
     * Supposed to be used with "mark" as popup window
     *
     * @return string
     */
    public function getPaymentMarkWhatIsOzow()
    {
        return 'Ozow Payment gateway';
    }

    /**
     * Mapper from Ozow-specific payment actions to Magento payment actions
     *
     * @return string|null
     */
    public function getPaymentAction()
    {
        $paymentAction = null;
        $pre           = __METHOD__ . ' : ';

        $action = $this->getValue( 'paymentAction' );

        switch ( $action ) {
            case self::PAYMENT_ACTION_AUTH:
                $paymentAction = \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;
                break;
            case self::PAYMENT_ACTION_SALE:
                $paymentAction = \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
                break;
            case self::PAYMENT_ACTION_ORDER:
                $paymentAction = \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER;
                break;
        }

        return $paymentAction;
    }

    /**
     * Check whether specified currency code is supported
     *
     * @param string $code
     * @return bool
     */
    public function isCurrencyCodeSupported( $code )
    {
        $supported = false;
        $pre       = __METHOD__ . ' : ';

        if ( in_array( $code, $this->_supportedCurrencyCodes ) ) {
            $supported = true;
        }

        return $supported;
    }

    /**
     * Check whether specified locale code is supported. Fallback to en_US
     *
     * @param string|null $localeCode
     * @return string
     */
    protected function _getSupportedLocaleCode( $localeCode = null )
    {
        if ( !$localeCode || !in_array( $localeCode, $this->_supportedImageLocales ) ) {
            return 'en_US';
        }
        return $localeCode;
    }

    /**
     * _mapOzowFieldset
     * Map Ozow config fields
     *
     * @param string $fieldName
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _mapOzowFieldset( $fieldName )
    {
        return "payment/{$this->_methodCode}/{$fieldName}";
    }

    /**
     * Map any supported payment method into a config path by specified field name
     *
     * @param string $fieldName
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getSpecificConfigPath( $fieldName )
    {
        return $this->_mapOzowFieldset( $fieldName );
    }
}