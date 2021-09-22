<?php
namespace ShoppingFeeder\Service\Model;
class Auth extends \Magento\Framework\Model\AbstractModel
{
    const RANDOM_STRING = 'The mice all ate cheese together.';
    const MAX_TIMEOUT = 300; //5 minutes

    public function __construct()
    {
        // $this->_init('shoppingfeeder_service/auth');
    }

    public static function auth(array $headers, $incomingScheme, $incomingMethod)
    {
        //check the API key
        $incomingApiKey = '';
        $incomingAuthTimestamp = strtotime('1970-00-00');
        $incomingSignature = '';
        foreach ($headers as $key => $value) {
            if (strtolower('X-SFApiKey') == strtolower($key)) {
                $incomingApiKey = $value;
            }
            if (strtolower('X-SFTimestamp') == strtolower($key)) {
                $incomingAuthTimestamp = $value;
            }
            if (strtolower('X-SFSignature') == strtolower($key)) {
                $incomingSignature = $value;
            }
        }

        //$sslInFront = Mage::getStoreConfig('web/secure/use_in_frontend');
        //$useSsl = ($sslInFront == null) ? false : boolval($sslInFront);
        $useSsl = false;

        $apiKeys = self::getApiKeys();

        if (!$useSsl || ($useSsl && $incomingScheme == 'https')) {
            //check the timestamp
            if (true || time() - $incomingAuthTimestamp <= self::MAX_TIMEOUT) {
                $localApiKey = $apiKeys['api_key'];
                if ($localApiKey == $incomingApiKey) {
                    $localApiSecret = $apiKeys['api_secret'];

                    $stringToSign = $incomingMethod . "\n" .
                                    $incomingAuthTimestamp . "\n" .
                                    self::RANDOM_STRING;

                    if (function_exists('hash_hmac')) {
                        $signature = hash_hmac('sha256', $stringToSign, $localApiSecret);
                    } elseif (function_exists('mhash')) {
                        $signature = bin2hex(mhash(MHASH_SHA256, $stringToSign, $localApiSecret));
                    } else {
                        return 'Authentication failed: Appropriate hashing function does not exist.';
                    }

                    if ($incomingSignature == $signature) {
                        return true;
                    } else {
                        return 'Authentication failed: invalid credentials.';
                    }
                } else {
                    return 'Authentication failed: invalid API key.';
                }
            } else {
                return 'Authentication failed: timeout exceeded.';
            }
        } else {
            return 'Authentication failed: non-secure connection';
        }
    }

    public static function getApiKeys()
    {
        // $localApiKey = Mage::getStoreConfig('shoppingfeeder/service/apikey');
        // $localApiSecret = Mage::getStoreConfig('shoppingfeeder/service/apisecret');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $localApiKey = $scopeConfig->getValue('shoppingfeeder/service/apikey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $localApiSecret = $scopeConfig->getValue('shoppingfeeder/service/apisecret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        // $localApiKey = $scopeConfig->getValue('helloworld/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        // $localApiSecret = $scopeConfig->getValue('helloworld/general/display_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return array(
            'api_key' => $localApiKey,
            'api_secret' => $localApiSecret
        );
    }
}