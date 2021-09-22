<?php declare(strict_types=1);

namespace Mstore\PaymentResolver\Controller\Resolver\Hyperpay;

use Hyperpay\Extension\Helper\Data;
use Hyperpay\Extension\Model\Adapter;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Action
{
    /**
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     *
     * @var Data
     */
    protected $helper;

    /**
     *
     * @var RemoteAddress
     */
    protected $remote;

    /**
     *
     * @var Session
     */
    protected $checkoutSession;

    /**
     * Index constructor.
     * @param OrderFactory $orderFactory
     * @param Adapter $adapter
     * @param RemoteAddress $remote
     * @param Session $checkoutSession
     * @param Registry $coreRegistry
     * @param PageFactory $pageFactory
     * @param Data $helper
     * @param Context $context
     */
    public function __construct(
        OrderFactory $orderFactory,
        Adapter $adapter,
        RemoteAddress $remote,
        Session $checkoutSession,
        Registry $coreRegistry,
        PageFactory $pageFactory,
        Data $helper,
        Context $context
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->remote = $remote;
        $this->adapter = $adapter;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $email = $this->getRequest()->getParam('email');
        $order = $this->orderFactory->create();
        $order->load($orderId);
        if (!$order->getId()
            || $order->getStatus() != 'pending'
            //|| $order->getCustomerEmail() != $email
        ) {
            $this->messageManager->addErrorMessage(__('The order is not applicable.'));
            return $this->resultRedirectFactory->create()->setPath('mspayment/resolver/failure');
        }
        /*
         * Init checkout session
         */
        $this->checkoutSession->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());

        $this->adapter->setOrder($order);
        try {
            $urlReq = $this->prepareTheCheckout($order);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('mspayment/resolver/failure');
        }
        $this->coreRegistry->register('formurl', $urlReq);

        return $this->pageFactory->create();
    }

    /**
     * Build data and make a request to hyperpay payment gateway
     * and return url of form
     *
     * @param Order $order
     * @return string
     * @throws \Exception
     */
    public function prepareTheCheckout($order)
    {
        $payment = $order->getPayment();

        //$shippingMethod =$order->getShippingMethod();
        $email = $order->getBillingAddress()->getEmail();
        $orderId = $order->getIncrementId();
        $amount = $order->getBaseGrandTotal();
        $total = $this->helper->convertPrice($payment, $amount);

        if ($this->adapter->getEnv()) {
            $grandTotal = (int) $total;
        } else {
            $grandTotal = number_format($total, 2, '.', '');
        }

        $currency = $this->adapter->getSupportedCurrencyCode($payment);
        $paymentType = $this->adapter->getPaymentType($payment);
        $this->adapter->setPaymentTypeAndCurrency($order, $paymentType, $currency);

        $ip = $this->remote->getRemoteAddress();
        $url = $this->adapter->getUrl() . 'checkouts';
        $data = "authentication.entityId=" . $this->adapter->getEntity($payment) .
            "&amount=" . $grandTotal .
            "&currency=" . $currency .
            "&paymentType=" . $paymentType .
            "&customer.ip=" . $ip .
            "&customer.email=" . $email .
            "&shipping.customer.email=" . $email .
            "&merchantTransactionId=" . $orderId;
        $auth = ['Authorization' => 'Bearer ' . $this->adapter->getAccessToken()];
        $this->helper->setHeaders($auth);
        $data .= $this->helper->getBillingAndShippingAddress($order);
        if (!empty($this->adapter->getRiskChannelId())) {
            $data .= "&risk.channelId=" . $this->adapter->getRiskChannelId() .
                "&risk.serviceId=I" .
                "&risk.amount=" . $grandTotal .
                "&risk.parameters[USER_DATA1]=Mobile";
        }

        $data .= $this->adapter->getModeHyperpay();
        /*&shipping.method=".$shippingMethod*/
        if ($payment->getData('method') == 'SadadNcb') {
            $data .= "&bankAccount.country=SA";
        }
        if ($this->adapter->getEnv() && $payment->getData('method') == 'HyperPay_ApplePay') {
            $data .= "&customParameters[3Dsimulator.forceEnrolled]=true";
        }
        $decodedData = $this->helper->getCurlReqData($url, $data);
        if (!isset($decodedData['id'])) {
            $this->helper->doError('Failed to get response from the payment gateway,Please check your request data and url');
        }
        return $this->adapter->getUrl() . "paymentWidgets.js?checkoutId=" . $decodedData['id'];
    }
}
