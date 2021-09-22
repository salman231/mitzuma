<?php namespace Payfast\Payfast\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class ItnHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{

    const TXN_ID = 'TXN_ID';

    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger )
    {
        $this->logger = $logger;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     *
     * @return void
     */
    public function handle( array $handlingSubject, array $response )
    {
        if (!isset($handlingSubject['payment'])
                     || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
          ) {
                   throw new \InvalidArgumentException('Payment data object should be provided');
          }

          /** @var PaymentDataObjectInterface $paymentDO */
          $paymentDO = $handlingSubject['payment'];

          $payment = $paymentDO->getPayment();

          /** @var $payment \Magento\Sales\Model\Order\Payment */
          $payment->setTransactionId($response[self::TXN_ID]);
          $payment->setIsTransactionClosed(false);


    }
}