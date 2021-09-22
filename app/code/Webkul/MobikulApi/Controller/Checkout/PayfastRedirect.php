<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Checkout;

class PayfastRedirect extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;

    protected $storeManager;

    protected $orderFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
    }

    public function execute()
    {
        $incrementId = $this->getRequest()->getParams()['incrementId'] ?? 0;
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
        $this->checkoutSession->setLastQuoteId($order->getQuoteId());
        $this->checkoutSession->setLastOrderId($incrementId);
        $this->checkoutSession->setLastRealOrderId($incrementId);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('payfast/redirect/index');
        return $resultRedirect;
    }
}
