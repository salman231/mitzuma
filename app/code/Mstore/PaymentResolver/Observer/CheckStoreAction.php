<?php declare(strict_types=1);

namespace Mstore\PaymentResolver\Observer;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;

class CheckStoreAction implements ObserverInterface
{
    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * CheckStoreAction constructor.
     * @param HttpContext $httpContext
     * @param OrderFactory $orderFactory
     * @param ManagerInterface $messageManager
     * @param Store $store
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param RedirectInterface $redirect
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        HttpContext $httpContext,
        OrderFactory $orderFactory,
        ManagerInterface $messageManager,
        Store $store,
        StoreCookieManagerInterface $storeCookieManager,
        RedirectInterface $redirect,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->store = $store;
        $this->httpContext = $httpContext;
        $this->storeCookieManager = $storeCookieManager;
        $this->storeRepository = $storeRepository;
        $this->orderFactory = $orderFactory;
    }

    public function execute(Observer $observer)
    {
        /** @var RequestInterface $request */
        $request = $observer->getEvent()->getRequest();
        $actionName = $request->getActionName();
        $routerName = $request->getRouteName();
        $paymentController = [
            'index',
            'sadad'
        ];
        if (!$this->store->isUseStoreInUrl()
            && $routerName == 'mspayment'
            && in_array($actionName, $paymentController)
        ) {
            $orderId = $request->getParam('order_id');
            $order = $this->orderFactory->create()->load($orderId);
            if (!$order->getId()) {
                $this->messageManager->addErrorMessage(__('The order is not applicable.'));
                $this->redirect->redirect($observer->getControllerAction()
                    ->getResponse(), 'mspayment/resolver/failure');
            }
            $order->getStore()->getCode();
            $storeCode = $order->getStore()->getCode();
            if (!empty($storeCode)) {
                $store = $this->storeRepository->getActiveStoreByCode($storeCode);
                $this->httpContext->setValue(Store::ENTITY, $store, false);
                $this->storeCookieManager->setStoreCookie($store);
            } else {
                $this->redirect->redirect($observer->getControllerAction()
                    ->getResponse(), 'mspayment/resolver/failure');
            }
        }
        return $this;
    }
}
