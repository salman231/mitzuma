<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\ZendeskChat\Block\Adminhtml\System\Config\Form;

use Magento\Store\Model\ScopeInterface;

/**
 * GDPR and CCPA text information block
 */
class GdprCcpa extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<div style="padding:10px;background-color:#f8f8f8;border:1px solid #ddd;margin-bottom:7px;">
        Read about
            <a href="https://www.zendesk.com/company/privacy-and-data-protection" 
            target="_blank"
            rel="nofollow noopener">Zendesk Chat Privacy and Data Protection</a>
            to understand how Zendesk responds to legal requests for Service Data.';

        return $html;
    }
}
