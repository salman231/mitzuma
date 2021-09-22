<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShoppingFeeder\Service\Controller\Stores;
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
        // \Magento\Framework\View\Result\PageFactory $resultPageFactory
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        // $this->resultPageFactory = $resultPageFactory;
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
        if($unauthorized) {
            return $unauthorized;
        }
        /** @var $websiteCollection Mage_Core_Model_Store */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $storeCollection = $storeManager->getStores($withDefault = false);
        $stores = array();
        foreach ($storeCollection as $store) {
            /** @var $store Mage_Core_Model_Store */
            // $store->initConfigCache();

            $stores[$store->getCode()] = $store->getName();
        }
        $result = $this->jsonResultFactory->create();
        $responseData = array(
            'status' => 'success',
            'data' => array(
                'stores' => $stores
            )
        );
        $result->setData($responseData);
        return $result;  
    }
}