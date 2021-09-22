<?php declare(strict_types=1);

namespace Mstore\PaymentResolver\Controller\Resolver\Hyperpay;

use Hyperpay\Extension\Helper\Data;
use Hyperpay\Extension\Model\Adapter;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class Sstatus extends Action
{
    /**
     *
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     *
     * @var Adapter
     */
    private $adapter;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     *
     * @var Data
     */
    private $helper;

    /**
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     *
     * @var Session
     */
    protected $checkoutSession;

    /**
     * Sstatus constructor.
     * @param Session $checkoutSession
     * @param PageFactory $pageFactory
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     * @param SerializerInterface $serializer
     * @param Registry $registry
     * @param Adapter $adapter
     * @param Context $context
     */
    public function __construct(
        Session $checkoutSession,
        PageFactory $pageFactory,
        Data $helper,
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer,
        Registry $registry,
        Adapter $adapter,
        Context $context
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->adapter = $adapter;
        $this->pageFactory = $pageFactory;
        $this->coreRegistry = $registry;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            if (!($this->checkoutSession->getLastRealOrderId())) {
                $this->helper->doError('Order id does not found.');
            }

            $order = $this->checkoutSession->getLastRealOrder();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->pageFactory->create();
        }

        if ($order->getStatus() != 'pending') {
            $this->_redirect($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB));
        }

        try {
            $data= $this->getSadadStatus($order);
            $status = $this->adapter->orderStatusSadad($data, $order);
            $this->coreRegistry->register('status', $status);

            return $this->pageFactory->create();
        } catch (\Exception $e) {
            $this->helper->catchExceptionRedirectAndCancelOrder($order, $e);

            return $this->pageFactory->create();
        }
    }

    /**
     * Retrieve payment gateway of sadad payment method response and set id to payment table
     *
     * @param Order $order
     * @return string
     * @throws \Exception
     */
    public function getSadadStatus($order)
    {
        $serviceUrl = $this->adapter->getSadadStatusUrl();

        if (empty($this->_request->getParam('MerchantRefNum'))) {
            $this->helper->doError('Merchant Reference Number does not found.');
        }
        $merchantRefNum = $this->_request->getParam('MerchantRefNum');
        $this->adapter->setInfo($order, $merchantRefNum);
        $merchantId = $this->adapter->getMerchantId($order->getPayment());
        $reqArray = [
            "transaction_no" => $merchantRefNum,
            "merchant_id" => $merchantId
        ];
        $data = $this->serializer->serialize($reqArray);
        $this->helper->setSadadHeaders($data);

        return $this->helper->getCurlReqData($serviceUrl, $data);
    }
}
