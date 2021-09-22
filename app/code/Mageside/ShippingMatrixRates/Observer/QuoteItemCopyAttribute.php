<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\ShippingMatrixRates\Observer;

use Magento\Framework\Event\ObserverInterface;

class QuoteItemCopyAttribute implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($observer->getProduct()->getShippingGroup()) {
            $observer->getQuoteItem()->setShippingGroup($observer->getProduct()->getShippingGroup());
        }

        return $this;
    }
}
