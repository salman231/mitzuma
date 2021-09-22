<?php declare(strict_types=1);

namespace Mstore\PaymentResolver\Controller\Resolver\Hyperpay;

use Hyperpay\Extension\Helper\Data;
use Hyperpay\Extension\Model\Adapter;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order as OrderStatus;
use Magento\Store\Model\StoreManagerInterface;

class Status extends Action
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
    protected $adapter;
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
     *
     * @var Http
     */
    protected $request;
    /**
     *
     * @var Session
     */
    protected $checkoutSession;

    /**
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Adapter $adapter
     * @param Registry $coreRegistry
     * @param Data $helper
     * @param PageFactory $pageFactory
     * @param Http $request
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Adapter $adapter,
        Registry $coreRegistry,
        Data $helper,
        PageFactory $pageFactory,
        Http $request,
        Session $checkoutSession,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->adapter = $adapter;
    }

    public function execute()
    {
        try {
            if (!($this->checkoutSession->getLastRealOrderId())) {
                $this->helper->doError('Order id does not found');
            }
            $order = $this->checkoutSession->getLastRealOrder();

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->pageFactory->create();
        }

        if ($order->getStatus() != 'pending') {
            $this->_redirect($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB));

            return;
        }

        try {
            $data = $this->getHyperpayStatus($order);
            $status = $this->adapter->orderStatus($data, $order);
            $this->coreRegistry->register('status', $status);
        } catch (\Exception $e) {
            $order->setState(OrderStatus::STATE_HOLDED);
            $order->addCommentToStatusHistory('Exception message: ' . $e->getMessage(), OrderStatus::STATE_HOLDED);
            $order->save();
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->pageFactory->create();
    }

    /**
     * Retrieve payment gateway response and set id to payment table
     *
     * @param Order $order
     * @return string
     * @throws \Exception
     */
    public function getHyperpayStatus($order)
    {
        $payment = $order->getPayment();
        if (empty($this->_request->getParam('id'))) {
            $this->helper->doError('Checkout id does not found.');
        }

        $id = $this->_request->getParam('id');
        $url = $this->adapter->getUrl() . "checkouts/" . $id . "/payment";
        $url .= "?authentication.entityId=" . $this->adapter->getEntity($payment);
        $auth = ['Authorization' => 'Bearer ' . $this->adapter->getAccessToken()];
        $this->helper->setHeaders($auth);
        $decodedData = $this->helper->getCurlRespData($url);

        if (!isset($decodedData)) {
            $this->helper->doError('No response data found.');
        }
        if (!isset($decodedData['id'])) {
            $this->helper->doError('Failed to get response from the payment gateway, please check your request data and url.');
        }
        $this->adapter->setInfo($order, $decodedData['id']);

        return $decodedData;
    }
}
