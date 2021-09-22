<?php
/*
 * Copyright (c) 2019 Ozow (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 *
 * Released under the GNU General Public License
 */
namespace Ozow\Ozow\Controller\Redirect;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Success extends \Ozow\Ozow\Controller\AbstractOzow implements CsrfAwareActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $_messageManager;

    /**
     * Execute
     */
    public function execute()
    {
        $pre = __METHOD__ . " : ";

        $this->_paymentMethod->ozowLogger($pre . " bof");

        try
        {
            $page_object = $this->pageFactory->create();

            $this->_paymentMethod->ozowLogger($pre . " eof");

            $this->_redirect('checkout/onepage/success');

        } catch ( \Magento\Framework\Exception\LocalizedException $e ) {
            $this->_logger->error( $pre . $e->getMessage() );
            $this->messageManager->addExceptionMessage( $e, $e->getMessage() );
            $this->_redirect( 'checkout/cart' );
        } catch ( \Exception $e ) {
            $this->_logger->error( $pre . $e->getMessage() );
            $this->messageManager->addExceptionMessage( $e, __( 'We can\'t start Ozow Checkout.' ) );
            $this->_redirect( 'checkout/cart' );
        }

        return '';
    }

    public function createCsrfValidationException( RequestInterface $request ): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf( RequestInterface $request ): ?bool
    {
        return true;
    }
}
