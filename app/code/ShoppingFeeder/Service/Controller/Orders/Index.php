<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShoppingFeeder\Service\Controller\Orders;
use ShoppingFeeder\Service\Model\Orders;
use ShoppingFeeder\Service\ShoppingFeeder_Controller\FrontAuth;
/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;
    protected $jsonResultFactory;
    protected $context;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        $this->context = $context;
        $this->jsonResultFactory = $jsonResultFactory;
        parent::__construct($context);
    }
    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $auth = new FrontAuth($this->context, $this->jsonResultFactory);
        $unauthorized = $auth->checkAuth();
        if($unauthorized){
            return $unauthorized;
        }
        set_time_limit(0);

        $page = $this->getRequest()->getParam('page', null);
        $numPerPage = $this->getRequest()->getParam('num_per_page', 1000);
        $orderId = $this->getRequest()->getParam('order_id', null);
        $store = $this->getRequest()->getParam('store', null);
        
        /**
            * For per-store system
            */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        if (!is_null($store)) {
            $storeManager->setCurrentStore($store);
        } else {
            $defaultStoreCode = $storeManager
                ->getWebsite(true)
                ->getDefaultGroup()
                ->getDefaultStore()
                ->getCode();

            $storeManager->setCurrentStore($defaultStoreCode);
        }

        $ordersModel = new Orders();

        if (is_null($orderId)) {
            $orders = $ordersModel->getItems($page, $numPerPage, $store);
        } else {
            $orders = $ordersModel->getItem($orderId, $store);
        }

        $responseData = array(
            'status' => 'success',
            'data' => array(
                'page' => $page,
                'num_per_page' => $numPerPage,
                'orders' => $orders,
                'store' => $store
            )
        );

        $result = $this->jsonResultFactory->create();
        $result->setData($responseData);
        return $result;
    }
}