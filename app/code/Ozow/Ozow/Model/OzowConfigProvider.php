<?php
/*
 * Copyright (c) 2019 Ozow (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 * 
 * Released under the GNU General Public License
 */
namespace Ozow\Ozow\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Ozow\Ozow\Helper\Data as OzowHelper;

class OzowConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var OzowHelper
     */
    protected $ozowHelper;

    /**
     * @var string[]
     */
    protected $methodCodes = [
        Config::METHOD_CODE,
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @param ConfigFactory $configFactory
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param OzowHelper $paymentHelper
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ConfigFactory $configFactory,
        ResolverInterface $localeResolver,
        CurrentCustomer $currentCustomer,
        OzowHelper $ozowHelper,
        PaymentHelper $paymentHelper
    ) {
        $this->_logger = $logger;
        $pre           = __METHOD__ . ' : ';

        $this->localeResolver  = $localeResolver;
        $this->config          = $configFactory->create();
        $this->currentCustomer = $currentCustomer;
        $this->ozowHelper   = $ozowHelper;
        $this->paymentHelper   = $paymentHelper;

        foreach ( $this->methodCodes as $code ) {
            $this->methods[$code] = $this->paymentHelper->getMethodInstance( $code );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $pre = __METHOD__ . ' : ';
        $config = [
            'payment' => [
                'ozow' => [
                    'paymentAcceptanceMarkSrc'  => $this->config->getPaymentMarkImageUrl(),
                    'paymentAcceptanceMarkHref' => $this->config->getPaymentMarkWhatIsOzow(),
                ],
            ],
        ];

        foreach ( $this->methodCodes as $code ) {
            if ( $this->methods[$code]->isAvailable() ) {
                $config['payment']['ozow']['redirectUrl'][$code]          = $this->getMethodRedirectUrl( $code );
                $config['payment']['ozow']['billingAgreementCode'][$code] = $this->getBillingAgreementCode( $code );

            }
        }

        return $config;
    }

    /**
     * Return redirect URL for method
     *
     * @param string $code
     * @return mixed
     */
    protected function getMethodRedirectUrl( $code )
    {
        $pre = __METHOD__ . ' : ';

        $methodUrl = $this->methods[$code]->getCheckoutRedirectUrl();

        return $methodUrl;
    }

    /**
     * Return billing agreement code for method
     *
     * @param string $code
     * @return null|string
     */
    protected function getBillingAgreementCode( $code )
    {

        $pre = __METHOD__ . ' : ';

        $customerId = $this->currentCustomer->getCustomerId();
        $this->config->setMethod( $code );

        // Always return null
        return $this->ozowHelper->shouldAskToCreateBillingAgreement( $this->config, $customerId );
    }
}
