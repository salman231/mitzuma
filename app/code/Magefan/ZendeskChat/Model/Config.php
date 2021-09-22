<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\ZendeskChat\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Magefan ZendeskChat Config Model
 */
class Config
{
    const XML_PATH_EXTENSION_ENABLED = 'mfzendeskchat/general/enabled';
    const ZENDESK_WIDGET_SCRIPT = 'mfzendeskchat/general/widget_script';
    const USE_CUSTOMER_DATA = 'mfzendeskchat/general/use_customer_data';
    const SCRIPT_LOAD_DELAY = 'mfzendeskchat/page_speed_optimization/script_load_delay';
    const ENABLE_MOBILE_OPTIMIZATION = 'mfzendeskchat/page_speed_optimization/enable_mobile_optimization';

    const ZOPIM_VERSION = 'zopim';
    const ZENDESK_VERSION = 'zendesk';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve true if zendesk chat module is enabled
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED,
            $storeId
        );
    }

    /**
     * Retrieve true if use customer data is enabled
     *
     * @param null $storeId
     * @return bool
     */
    public function useCustomerData($storeId = null)
    {
        return (bool)$this->getConfig(
            self::USE_CUSTOMER_DATA,
            $storeId
        );
    }

    /**
     * Retrieve script load delay
     *
     * @param null $storeId
     * @return string
     */
    public function getScriptLoadDelay($storeId = null)
    {
        return (string)$this->getConfig(
            self::SCRIPT_LOAD_DELAY,
            $storeId
        );
    }

    /**
     * Retrieve true if mobile optimization is enabled
     *
     * @param null $storeId
     * @return bool
     */
    public function enableMobileOptimization($storeId = null)
    {
        return (bool)$this->getConfig(
            self::ENABLE_MOBILE_OPTIMIZATION,
            $storeId
        );
    }

    /**
     * Retrieve Zendesk Chat widget script
     *
     * @param null $storeId
     * @return string
     */
    public function getZendeskChatScript($storeId = null)
    {
        return (string)$this->getConfig(
            self::ZENDESK_WIDGET_SCRIPT,
            $storeId
        );
    }

    /**
     * Retrieve Zendesk Chat widget script version
     *
     * @param null $storeId
     * @return string
     */
    public function getZendeskChatScriptVersion($storeId = null)
    {
        if (false !== strpos($this->getZendeskChatScript($storeId), 'zopim.com')) {
            return self::ZOPIM_VERSION;
        }

        return self::ZENDESK_VERSION;
    }

    /**
     * Retrieve current locale code
     *
     * @param null $storeId
     * @return string
     */
    public function getLocaleCode($storeId = null)
    {
        $code = (string)$this->getConfig(
            'general/locale/code',
            $storeId
        );
        $code = explode('_', $code);
        return strtoupper($code[0]);
    }

    /**
     * Retrieve widget config
     *
     * @param null $storeId
     * @return string
     */
    public function getZESettings($storeId = null)
    {
        $webWidget = [];


        $webWidget['chat']['locale'] = $this->getLocaleCode($storeId);

        if ($title = (string)$this->getConfig('mfzendeskchat/appearance/top_title', $storeId)) {
            $webWidget['chat']['title']['*'] = $title;
        }

        if ($title = (string)$this->getConfig('mfzendeskchat/appearance/concierge_display_title', $storeId)) {
            $webWidget['chat']['concierge']['name'] = $title ;
        }

        if ($title = (string)$this->getConfig('mfzendeskchat/appearance/concierge_byline', $storeId)) {
            $webWidget['chat']['concierge']['title']['*'] = $title;
        }

        if ($title = (string)$this->getConfig('mfzendeskchat/forms/prechat_greeting', $storeId)) {
             $webWidget['chat']['prechatForm']['greeting']['*'] = $title;
        }

        if ($title = (string)$this->getConfig('mfzendeskchat/forms/offline_greeting', $storeId)) {
            $webWidget['chat']['offlineForm']['greeting']['*'] = $title;
        }

        return ['webWidget' => $webWidget];
    }

    /**
     * Retrieve store config value
     * @param string $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
