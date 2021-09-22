<?php

namespace Ozow\Ozow\Controller\Redirect;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Cancel extends \Ozow\Ozow\Controller\AbstractOzow implements CsrfAwareActionInterface
{
    protected $resultPageFactory;

    public function execute()
    {
        $pre = __METHOD__ . " : ";

        $this->_paymentMethod->ozowLogger($pre . " bof");

        try {
            $page_object = $this->pageFactory->create();

            $this->_order = $this->_checkoutSession->getLastRealOrder();

            $this->messageManager->addNotice('Transaction has been declined.');

            $this->_order->addStatusHistoryComment('Redirect Response, Transaction has been cancelled')->setIsCustomerNotified(false);

            $this->_order->cancel()->save();

            $this->_checkoutSession->restoreQuote();

            $this->_paymentMethod->ozowLogger($pre . " eof");

            $this->_redirect('checkout/cart');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->error($pre . $e->getMessage());
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
            $this->_redirect('checkout/cart');
        } catch (\Exception $e) {
            $this->_logger->error($pre . $e->getMessage());
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
            $this->_redirect('checkout/cart');
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