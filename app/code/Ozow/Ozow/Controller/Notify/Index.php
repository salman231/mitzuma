<?php
/*
 * Copyright (c) 2019 Ozow (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 * 
 * Released under the GNU General Public License
 */
namespace Ozow\Ozow\Controller\Notify;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Index extends \Ozow\Ozow\Controller\AbstractOzow implements CsrfAwareActionInterface
{
    private $storeId;

    /**
     * indexAction
     *
     */
    public function execute()
    {
        $pre = __METHOD__ . " : ";

        $site_code = $this->getConfigData('site_code');
        $private_key = $this->getConfigData('private_key');
        $api_key = $this->getConfigData('api_key');
        $error = '';

        $post_data = $this->getPostData();

        $post_site_code = $post_data['SiteCode'];
        $post_transaction_ref = $post_data['TransactionReference'];
        $post_transaction_id = $post_data['TransactionId'];
        $post_amount = $post_data['Amount'];
        $post_status = $post_data['Status'];
        $post_optional_1 = $post_data['Optional1'];
        $post_optional_5 = $post_data['Optional5'];
        $post_currency_code = $post_data['CurrencyCode'];

        $post_is_test = strtolower($post_data['IsTest']) == 'true' ? 'true' : 'false';

        $post_status_message = $post_data['StatusMessage'];
        $post_hash = $post_data['Hash'];

        $message = 'Site Code: ' . $post_site_code . '
                    i-Pay Transaction ID: ' . $post_transaction_id . '
                    Transaction Reference: ' . $post_transaction_ref . '
                    Amount: ' . $post_amount . '
                    Currency Code: ' . $post_currency_code . '
                    Optional 1: ' . $post_optional_1 . '
                    Optional 5: ' . $post_optional_5 . '
                    Is Test: ' . $post_is_test . '
                    Status: ' . $post_status . '
                    Status Message: ' . $post_status_message . '
		            Hash: ' . $post_hash;

        $hash_check = hash('sha512', strtolower($post_site_code . $post_transaction_id . $post_transaction_ref . $post_amount . $post_status . $post_optional_1 . $post_optional_5 . $post_currency_code . $post_is_test . $post_status_message . $private_key));

        $orderId = $post_transaction_ref;
        $this->_order = $this->_orderFactory->create()->loadByIncrementId($orderId);
        $this->_storeId = $this->_order->getStoreId();

        if ($this->_order->getTotalDue() == 0 && strtolower($post_status) == "complete") {
            $this->_paymentMethod->ozowLogger($pre . "Order already completed and paid - OrderId $orderId" . " eof");
            return;
        }

        if ($this->_order->getStatus() !== \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
            $error = 'Order status not pending payment - ' . $orderId;
            $this->_paymentMethod->ozowLogger($pre . $error . " eof");
            return;
        } else if ($post_hash != $hash_check) {
            $error = 'Hash validation failed.';
            $this->_paymentMethod->ozowLogger($pre . $error . " eof");
            return;
        } else {
            $currency_code = $this->_order->getOrderCurrencyCode();
            $amount = $this->_order->getGrandTotal();

            if ($currency_code != $post_currency_code) {
                $error .= sprintf('Payment currency of %s does not match cart currency of %s.', $post_currency_code, $currency_code);
            } else if (abs((float)$post_amount - (float)$amount) > 0.01) {
                $error .= sprintf('Amount paid was %d instead of the cart total of %d.', $post_amount, $amount);
            } else if (strtolower($post_status) != "complete") {
                $error .= sprintf('Payment %s: %s', $post_status, $post_status_message);
            } else if (!$this->confirmPaymentSuccess($site_code, $api_key, $post_transaction_id, $post_is_test)) {
                $error .= "The payment status could not be verified, please contact the website administrator if your payment was successful.";
            } else if ($post_is_test == 'true') {
                $error .= "The test was successful, however payments made in test mode cannot update the order status to successful.";

                $this->_order->setStatus(\Magento\Sales\Model\Order::STATE_HOLDED);
                $this->_order->save();
                $this->_order->addStatusHistoryComment($error);
            } else {

                $this->updateOrderStatus();

                $this->saveInvoice();
            }
        }

        if (!empty($error)) {
            $message = $error . "\n" . $message;

            $this->_paymentMethod->ozowLogger($pre . $message . " eof");
        }
    }

    // Retrieve post data
    public function getPostData()
    {
        // Posted variables from ITN
        $postData = $_POST;

        // Strip any slashes in data
        foreach ( $postData as $key => $val ) {
            $nData[$key] = stripslashes( $val );
        }

        // Return "false" if no data was received
        if ( sizeof( $postData ) == 0 ) {
            return ( false );
        } else {
            return ( $postData );
        }
    }

    /**
     * saveInvoice
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveInvoice()
    {
        $pre = __METHOD__ . " : ";

        $this->_paymentMethod->ozowLogger($pre . " bof");

        // Check for mail msg
        $invoice = $this->_order->prepareInvoice();

        $invoice->register()->capture();

        /**
         * @var \Magento\Framework\DB\Transaction $transaction
         */
        $transaction = $this->_transactionFactory->create();
        $transaction->addObject( $invoice )
            ->addObject( $invoice->getOrder() )
            ->save();

        $this->_order->addStatusHistoryComment( __( 'Notified customer about invoice #%1.', $invoice->getIncrementId() ) );
        $this->_order->setIsCustomerNotified( true );
        $this->_order->save();

        $this->_paymentMethod->ozowLogger($pre . " eof");
    }

    protected function confirmPaymentSuccess($site_code, $api_key, $transaction_id, $is_test)
    {
        $pre = __METHOD__ . " : ";

        $this->_paymentMethod->ozowLogger($pre . " bof");

        try {
            $headers = array(
                "ApiKey: " . $api_key,
                "Accept: application/json"
            );

            $api_url = sprintf('https://api.ozow.com/GetTransaction?siteCode=%s&transactionId=%s&IsTest=%s', $site_code, $transaction_id, $is_test);
            $curl = curl_init($api_url);

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $curl_response = curl_exec($curl);

            if ($curl_response === false) {
                $info = curl_getinfo($curl);

                $this->_paymentMethod->ozowLogger($pre . "Curl error - $info");

                curl_close($curl);

                return false;
            }

            curl_close($curl);

            $decoded = json_decode($curl_response);

            if (isset($decoded->status) && strtolower($decoded->status) == 'error') {
                $this->_paymentMethod->ozowLogger($pre . "Error confirming the payment");
            }

            $this->_paymentMethod->ozowLogger($pre . " eof");

            return strtolower($decoded->status) == "complete";

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->error($pre . $e->getMessage());
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->error($pre . $e->getMessage());
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }
    }

    protected function updateOrderStatus()
    {
        $pre = __METHOD__ . " : ";
        $this->_paymentMethod->ozowLogger($pre . 'bof');

        $status = \Magento\Sales\Model\Order::STATE_PROCESSING;

        if (!empty($this->getConfigData('successful_order_status'))) {
            $status = $this->getConfigData('successful_order_status');
        }

        $this->_order->setStatus($status);
        $this->_order->setState($status)->save();
        $this->_order->save();
        $order = $this->_order;

        $order_successful_email = $this->getConfigData('order_email');

        if ($order_successful_email != '0') {
            $this->OrderSender->send($order);
            $order->addStatusHistoryComment(__('Notified customer about order #%1.', $order->getId()))->setIsCustomerNotified(true)->save();
        }

        $invoice = $this->_invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
        $invoice->register();

        $transaction = $this->_objectManager->create('Magento\Framework\DB\Transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());

        $transaction->save();

        $send_invoice_email = $this->getConfigData('invoice_email');

        if ($send_invoice_email != '0') {
            $this->invoiceSender->send($invoice);
            $order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))->setIsCustomerNotified(true)->save();
        }

        $this->_paymentMethod->ozowLogger($pre . 'eof');
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
