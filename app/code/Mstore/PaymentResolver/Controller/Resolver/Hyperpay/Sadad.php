<?php declare(strict_types=1);

namespace Mstore\PaymentResolver\Controller\Resolver\Hyperpay;

use Hyperpay\Extension\Helper\Data;
use Hyperpay\Extension\Model\Adapter;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

class Sadad extends Action
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     *
     * @var Data
     */
    protected $helper;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     *
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     *
     * @var Session
     */
    protected $checkoutSession;

    /**
     * Sadad constructor.
     * @param OrderFactory $orderFactory
     * @param Data $helper
     * @param PageFactory $pageFactory
     * @param Session $checkoutSession
     * @param SerializerInterface $serializer
     * @param Adapter $adapter
     * @param Context $context
     */
    public function __construct(
        OrderFactory $orderFactory,
        Data $helper,
        PageFactory $pageFactory,
        Session $checkoutSession,
        SerializerInterface $serializer,
        Adapter $adapter,
        Context $context
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->pageFactory = $pageFactory;
        $this->serializer = $serializer;
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->adapter = $adapter;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
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
            $urlReq = $this->makeReqUsingSadad($order);
        } catch (\Exception $e) {
            $this->helper->catchExceptionRedirectAndCancelOrder($order, $e);
            return $this->pageFactory->create();
        }

        $this->_redirect($urlReq);
    }

    /**
     * Build data for sadad method and make a request to gateway
     * and return url of redirect form
     *
     * @param Order $order
     * @return string
     */
    public function makeReqUsingSadad($order)
    {
        $payment = $order->getPayment();
        $amount = $order->getBaseGrandTotal();
        $orderId = str_pad($order->getIncrementId(), 20, "0", STR_PAD_LEFT);
        $total = $this->helper->convertPrice($payment, $amount);
        $serviceUrl = $this->adapter->getSadadReqUrl();

        $reqArray = [
            "api_user_name" => $this->adapter->getApiUserName($payment),
            "api_secret" => $this->adapter->getApiSecret($payment),
            "merchant_id" => $this->adapter->getMerchantId($payment),
            "transaction_number" => $orderId,
            "success_url" => $this->adapter->getSadadUrl(),
            "failure_url" => $this->adapter->getSadadUrl(),
            "lang" => 'EN',
            "is_testing" => $this->adapter->getEnv(),
            "amount" => $total
        ];
        $data = $this->serializer->serialize($reqArray);
        $this->helper->setSadadHeaders($data);
        $decodedData = $this->helper->getCurlReqData($serviceUrl, $data);

        // Redirect to PayWare checkout page
        return $this->adapter->getSadadRedirectUrl() . $decodedData;
    }
}
