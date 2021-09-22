<?php

namespace ShoppingFeeder\Service\Observer;
use Magento\Framework\Event\ObserverInterface;

class CheckCookieObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->_objectManager = $objectManager;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try{
            //here we're going to create the referral cookie for the visitor if they came from ShoppingFeeder
            if (null !== $this->_request->getParam('SFDRREF', null))
            {
                setcookie('SFDRREF', $this->_request->getParam('SFDRREF'), time() + (60*60*24*30), '/');
                $_COOKIE['SFDRREF']= $this->_request->getParam('SFDRREF');
            }
        }
        catch (\Exception $e)
        {

        }

        return $this;
    }
}