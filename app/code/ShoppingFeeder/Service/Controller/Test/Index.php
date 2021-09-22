<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShoppingFeeder\Service\Controller\Test;
use ShoppingFeeder\Service\Model\Offers;
use ShoppingFeeder\Service\Model\Auth;
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

        $result = $this->jsonResultFactory->create();
        $data = array();

        $store = $this->getRequest()->getParam('store', null);

        /**
         * For per-store system
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        // $storeRootCategoryId = $storeManager->getStore()->getRootCategoryId();
        if (!is_null($store))
        {
            $storeManager->setCurrentStore($store);
        }
        else
        {
            $defaultStoreCode = $storeManager
                ->getWebsite(true)
                ->getDefaultGroup()
                ->getDefaultStore()
                ->getCode();

            $storeManager->setCurrentStore($defaultStoreCode);
        }

        $requiresSsl = false;

        try {

            if (!is_null($store)) {
                // \Magento\Store\Model\Store
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $storeCollection = $storeManager->getStores($withDefault = false);

                $stores = array();
                foreach ($storeCollection as $internalStore) {

                    $stores[$internalStore->getCode()] = $internalStore->getName();
                }

                if (!isset($stores[$store])) {
                    throw new \Exception('Multi-store store code is not valid');
                }
            }

            // /** @var ShoppingFeeder_Service_Model_Auth $authModel */
            // $authModel = Mage::getModel('shoppingfeeder_service/auth');
            // //check if this setup requires SSL
            // $sslInFront = Mage::getStoreConfig('web/secure/use_in_frontend');

            $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
            // $localApiKey = $scopeConfig->getValue('shoppingfeeder/service/apikey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            // $localApiSecret = $scopeConfig->getValue('shoppingfeeder/service/apisecret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $sslInFront = $scopeConfig->getValue('web/secure/use_in_frontend', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $requiresSsl = ($sslInFront == null) ? false : $sslInFront;
            $authModel = new Auth();
            $apiKeys = $authModel->getApiKeys();

            if (!isset($apiKeys['api_key']) || empty($apiKeys['api_key'])) {
                throw new \Exception('API key not setup.');
            }

            if (!isset($apiKeys['api_secret']) || empty($apiKeys['api_secret'])) {
                throw new \Exception('API secret not setup.');
            }

            $auth = new FrontAuth($this->context, $this->jsonResultFactory);
            $unauthorized = $auth->checkAuth();
            if ($unauthorized) {
                return $unauthorized;
            }

            set_time_limit(0);

            $responseData = array(
                'status' => 'success',
                'data' => array(
                    'message' => 'Authorization OK',
                    'requires_ssl' => $requiresSsl
                )
            );

        } catch (\Exception $e) {
            $responseData = array(
                'status' => 'fail',
                'data' => array (
                    'message' => $e->getMessage(),
                    'requires_ssl' => $requiresSsl
                )
            );
        }

        $result->setData($responseData);
        return $result;
    }

    public function orders(){
        $result = $this->jsonResultFactory->create();
        $data = array();
        $result->setData($data);
        return $result; 
    }
}