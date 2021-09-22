<?php
/*
 * Copyright (c) 2019 Ozow (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 *
 * Released under the GNU General Public License
 */
namespace Ozow\Ozow\Controller;

use Magento\Checkout\Controller\Express\RedirectLoginInterface;
use Magento\Framework\App\Action\Action as AppAction;
use Magento\Framework\App\ActionInterface;
use Ozow\Ozow\Model\Ozow;

/**
 * Checkout Controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractOzow extends AppAction implements RedirectLoginInterface, ActionInterface
{
    /**
     * Internal cache of checkout models
     *
     * @var array
     */
    protected $_checkoutTypes = [];

    /**
     * @var \Ozow\Ozow\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote = false;

    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = 'Ozow\Ozow\Model\Config';

    /** Config method type @var string */
    protected $_configMethod = \Ozow\Ozow\Model\Config::METHOD_CODE;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session $_checkoutSession
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $ozowSession;

    /**
     * @var \Magento\Framework\Url\Helper
     */
    protected $_urlHelper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var  \Magento\Sales\Model\Order $_order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Ozow\Ozow\Model\Ozow $_paymentMethod
     */
    protected $_paymentMethod;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Session\Generic $ozowSession
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Ozow\Ozow\Model\Ozow $paymentMethod
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Session\Generic $ozowSession,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Customer\Model\Url $customerUrl,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Ozow\Ozow\Model\Ozow $paymentMethod,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $OrderSender,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        // CsrfAwareAction Magento2.3 compatibility
        if (interface_exists("\Magento\Framework\App\CsrfAwareActionInterface")) {
            $request = $this->getRequest();
            if ($request instanceof HttpRequest && $request->isPost() && empty($request->getParam('form_key'))) {
                $formKey = $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class);
                $request->setParam('form_key', $formKey->getFormKey());
            }
        }
        
        $pre = __METHOD__ . " : ";

        $this->_logger = $logger;

        $this->_customerSession    = $customerSession;
        $this->_checkoutSession    = $checkoutSession;
        $this->_orderFactory       = $orderFactory;
        $this->ozowSession         = $ozowSession;
        $this->_urlHelper          = $urlHelper;
        $this->_customerUrl        = $customerUrl;
        $this->pageFactory         = $pageFactory;
        $this->_invoiceService     = $invoiceService;
        $this->invoiceSender       = $invoiceSender;
        $this->OrderSender         = $OrderSender;
        $this->_transactionFactory = $transactionFactory;
        $this->_paymentMethod      = $paymentMethod;
        $this->_date               = $date;

        parent::__construct( $context );

        $parameters    = ['params' => [$this->_configMethod]];
        $this->_config = $this->_objectManager->create( $this->_configType, $parameters );
    }

    /**
     * Custom getter for payment configuration
     *
     * @param string $field   i.e Ozow transaction id, test_mode
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConfigData( $field )
    {
        return $this->_paymentMethod->getConfigData( $field );
    }

    /**
     * Instantiate
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initCheckout()
    {
        $pre = __METHOD__ . " : ";
        $this->_paymentMethod->ozowLogger($pre . 'bof');

        $this->_order = $this->_checkoutSession->getLastRealOrder();

        if ( !$this->_order->getId() ) {
            $this->getResponse()->setStatusHeader( 404, '1.1', 'Not found' );
            throw new \Magento\Framework\Exception\LocalizedException( __( 'We could not find "Order" for processing' ) );
        }

        if ( $this->_order->getState() != \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT ) {
            $this->_order->setState(
                \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT
            )->save();
        }

        if ( $this->_order->getQuoteId() ) {
            $this->_checkoutSession->setOzowQuoteId( $this->_checkoutSession->getQuoteId() );
            $this->_checkoutSession->setOzowSuccessQuoteId( $this->_checkoutSession->getLastSuccessQuoteId() );
            $this->_checkoutSession->setOzowRealOrderId( $this->_checkoutSession->getLastRealOrderId() );
            $this->_checkoutSession->getQuote()->setIsActive( false )->save();
        }

        $this->_paymentMethod->ozowLogger($pre . 'eof');
    }

    /**
     * Ozow session instance getter
     *
     * @return \Magento\Framework\Session\Generic
     */
    protected function _getSession()
    {
        return $this->ozowSession;
    }

    /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Return checkout quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote()
    {
        if ( !$this->_quote ) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }

        return $this->_quote;
    }

    /**
     * Returns before_auth_url redirect parameter for customer session
     * @return null
     */
    public function getCustomerBeforeAuthUrl()
    {
        return;
    }

    /**
     * Returns a list of action flags [flag_key] => boolean
     * @return array
     */
    public function getActionFlagList()
    {
        return [];
    }

    /**
     * Returns login url parameter for redirect
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_customerUrl->getLoginUrl();
    }

    /**
     * Returns action name which requires redirect
     * @return string
     */
    public function getRedirectActionName()
    {
        return 'index';
    }

    /**
     * Redirect to login page
     *
     * @return void
     */
    public function redirectLogin()
    {
        $this->_actionFlag->set( '', 'no-dispatch', true );
        $this->_customerSession->setBeforeAuthUrl( $this->_redirect->getRefererUrl() );
        $this->getResponse()->setRedirect(
            $this->_urlHelper->addRequestParam( $this->_customerUrl->getLoginUrl(), ['context' => 'checkout'] )
        );
    }
}
