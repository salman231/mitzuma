<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\ZendeskChat\Ui\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customer data for the logged_as_customer section
 */
class Customer implements SectionSourceInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session $customerSession,
        StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve private customer data for the zendesk widget section
     *
     * @return array
     * @throws LocalizedException
     */
    public function getSectionData(): array
    {
        if (!$this->customerSession->getCustomerId()) {
            return [];
        }
        $customer = $this->customerSession->getCustomer();
        $address = $customer->getPrimaryBillingAddress();
        
        return [
            'customerName' => $customer->getName(),
            'customerEmail' => $customer->getEmail(),
            'customerPhone' => $address ? $address->getTelephone() : ''
        ];
    }
}
