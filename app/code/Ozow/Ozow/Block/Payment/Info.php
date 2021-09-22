<?php
/*
 * Copyright (c) 2019 Ozow (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 * 
 * Released under the GNU General Public License
 */
namespace Ozow\Ozow\Block\Payment;

/**
 * Ozow common payment info block
 * Uses default templates
 */
class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var \Ozow\Ozow\Model\InfoFactory
     */
    protected $_OzowInfoFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Ozow\Ozow\Model\InfoFactory $OzowInfoFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Ozow\Ozow\Model\InfoFactory $OzowInfoFactory,
        array $data = []
    ) {
        $this->_OzowInfoFactory = $OzowInfoFactory;
        parent::__construct( $context, $data );
    }
}
