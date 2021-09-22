<?php
/*
 * Copyright (c) 2019 Ozow (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 * 
 * Released under the GNU General Public License
 */
namespace Ozow\Ozow\Block\Ozow;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ozow\Ozow\Model\Config;
use Ozow\Ozow\Model\Ozow\Checkout;

class Form extends \Magento\Payment\Block\Form
{
    /**
     * @var string Payment method code
     */
    protected $_methodCode = Config::METHOD_CODE;

    /**
     * @var \Ozow\Ozow\Helper\Data
     */
    protected $_ozowData;

    /**
     * @var \Ozow\Ozow\Model\ConfigFactory
     */
    protected $ozowConfigFactory;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Ozow\Ozow\Model\Config
     */
    protected $_config;

    /**
     * @var bool
     */
    protected $_isScopePrivate;

    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @param Context $context
     * @param \Ozow\Ozow\Model\ConfigFactory $ozowConfigFactory
     * @param ResolverInterface $localeResolver
     * @param \Ozow\Ozow\Helper\Data $ozowData
     * @param CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Ozow\Ozow\Model\ConfigFactory $ozowConfigFactory,
        ResolverInterface $localeResolver,
        \Ozow\Ozow\Helper\Data $ozowData,
        CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        $pre = __METHOD__ . " : ";
        $this->_ozowData            = $ozowData;
        $this->ozowConfigFactory    = $ozowConfigFactory;
        $this->_localeResolver      = $localeResolver;
        $this->_config              = null;
        $this->_isScopePrivate      = true;
        $this->currentCustomer      = $currentCustomer;
        parent::__construct( $context, $data );
    }

    /**
     * Set template and redirect message
     *
     * @return null
     */
    protected function _construct()
    {
        $pre = __METHOD__ . " : ";
        $this->_config = $this->ozowConfigFactory->create()->setMethod( $this->getMethodCode() );
        parent::_construct();
    }

    /**
     * Payment method code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
        $pre = __METHOD__ . " : ";

        return $this->_methodCode;
    }
}
