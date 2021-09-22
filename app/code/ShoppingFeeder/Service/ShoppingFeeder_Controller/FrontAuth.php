<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShoppingFeeder\Service\ShoppingFeeder_Controller;
use ShoppingFeeder\Service\Model\Auth;
/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class FrontAuth extends \Magento\Framework\App\Action\Action
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
     }
    public function checkAuth()
    {
        $store = $this->getRequest()->getParam('store', null);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
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
            
        if (!function_exists('getallheaders'))
        {
            function getallheaders()
            {
                $headers = '';
                foreach ($_SERVER as $name => $value)
                {
                    if (substr($name, 0, 5) == 'HTTP_')
                    {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
                return $headers;
            }
        }

        $headers = getallheaders();

        // return $this->resultPageFactory->create();
        $result = $this->jsonResultFactory->create();

        $data = array();
        $scheme = $this->getRequest()->getScheme();
        $method =  $this->getRequest()->getMethod();
        
        $authModel = new Auth();
        $authResult = $authModel->auth(
            $headers,
            $this->getRequest()->getScheme(),
            $this->getRequest()->getMethod()
        );

        if ($authResult !== true)
        {
            $responseData = array(
                'status' => 'fail',
                'data' => array (
                    'message' => $authResult
                )
            );

            $result->setData($responseData);
            return $result;
        }
        else{
            return false;
        }
    }

}