<?php
/*
 * Copyright (c) 2019 Ozow (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 * 
 * Released under the GNU General Public License
 */
namespace Ozow\Ozow\Controller\Redirect;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Index extends \Ozow\Ozow\Controller\AbstractOzow
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = \Ozow\Ozow\Model\Config::METHOD_CODE;

    /**
     * Execute
     */
    public function execute()
    {
        $pre = __METHOD__ . " : ";

        $this->_paymentMethod->ozowLogger($pre . " bof");

        $page_object = $this->pageFactory->create();

        try {
            $this->_initCheckout();

            $this->_paymentMethod->ozowLogger($pre . " eof");
        } catch ( \Magento\Framework\Exception\LocalizedException $e ) {
            $this->_logger->error( $pre . $e->getMessage() );
            $this->messageManager->addExceptionMessage( $e, $e->getMessage() );
            $this->_redirect( 'checkout/cart' );
        } catch ( \Exception $e ) {
            $this->_logger->error( $pre . $e->getMessage() );
            $this->messageManager->addExceptionMessage( $e, __( 'We can\'t start Ozow Checkout.' ) );
            $this->_redirect( 'checkout/cart' );
        }

        return $page_object;
    }
}
