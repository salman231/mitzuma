<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\ZendeskChat\Block;

use Magento\Framework\View\Element\Template;
use Magefan\ZendeskChat\Model\Config;

/**
 * Live Chat Widget Block
 */
class LiveChat extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * LiveChat constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->config->isEnabled();
    }

    /**
     * @return mixed
     */
    public function useCustomerData()
    {
        return $this->config->useCustomerData();
    }

    /**
     * @return int
     */
    public function getScriptLoadDelay()
    {
        $delay = 0;
        $configDelay = (int)$this->config->getScriptLoadDelay();
        if ($configDelay) {
            $delay = $configDelay * 1000;
        }
        return $delay;
    }

    /**
     * @return mixed
     */
    public function enableMobileOptimization()
    {
        return $this->config->enableMobileOptimization()
            && $this->config->getZendeskChatScriptVersion() == Config::ZOPIM_VERSION;
    }

    /**
     * @return string
     */
    public function getZendeskChatScript()
    {
        $script = $this->config->getZendeskChatScript();
        $version = $this->config->getZendeskChatScriptVersion();

        if (Config::ZOPIM_VERSION == $version) {
            return trim(strip_tags($script));
        }

        $matches = [];
        preg_match('/src=(["\'])(.*?)\1/', $script, $matches, PREG_OFFSET_CAPTURE);
        
        $result = '';
        if (isset($matches[2][0])) {
            $result = '(function(w, d, s) {
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s);
                j.async = true;
                j.src =
                    "'.$matches[2][0].'";
                j.id = "ze-snippet";
                f.parentNode.insertBefore(j, f);
            })(window, document, "script");';
        }
        
        return $result;
    }

    /**
     * @return false|string
     */
    public function getKey()
    {
        $script = $this->getZendeskChatScript();

        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $script, $match);

        $url = $match[0][0];

        $startPos = strpos($url, '?');
        $endPos = strpos($url, ';');
        $length = $endPos - $startPos;
        return substr($url, $startPos + 1, $length - 2);
    }

    /**
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->config->getLocaleCode();
    }

    /**
     * @return string
     */
    public function getZESettings()
    {
        return json_encode($this->config->getZESettings());
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ("printInvoice" == $this->getRequest()->getActionName() && "print" == $this->getRequest()->getActionName()) {
            return '';
        }

        if ($this->config->isEnabled() && $this->getZendeskChatScript()) {
            return parent::_toHtml();
        }

        return '';
    }
}
