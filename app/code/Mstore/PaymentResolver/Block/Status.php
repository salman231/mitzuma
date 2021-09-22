<?php declare(strict_types=1);

namespace Mstore\PaymentResolver\Block;

use Hyperpay\Extension\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Status extends Template
{
    /**
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     *
     * @var Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Data $helper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
    }
    /**
     * Retrieve true if payment succeed
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->coreRegistry->registry('status');
    }
    /**
     * Retrieve incremental order id
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->helper->getOrderId();
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        $url = $this->getUrl('mspayment/resolver/notify');
        return  $url . '?id=' . $this->getOrderId() . '&status=' . $this->getStatus();
    }
}
