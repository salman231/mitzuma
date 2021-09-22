<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShoppingFeeder\Service\Controller\Stock;
use ShoppingFeeder\Service\Model\Offers;
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
        set_time_limit(0);

        $auth = new FrontAuth($this->context, $this->jsonResultFactory);
        $unauthorized = $auth->checkAuth();
        if($unauthorized) {
            return $unauthorized;
        }
        $offerId = $this->getRequest()->getParam('offer_id', null);
        $store = $this->getRequest()->getParam('store', null);

        /** @var ShoppingFeeder_Service_Model_Offers $offersModel */
        $offersModel = new Offers();

        $stockQuantity = $offersModel->getStockQuantity($offerId, $store);

        $responseData = array(
            'status' => 'success',
            'data' => array(
                'stock_quantity' => (int)$stockQuantity
            )
        );

        $result = $this->jsonResultFactory->create();
        
        $result->setData($responseData);
        return $result;  
    }

}