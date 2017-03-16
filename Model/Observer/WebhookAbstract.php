<?php

namespace SweetTooth\Webhook\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Customer
 */
class WebhookAbstract implements ObserverInterface
{
    const MAGENTO_HEADER_EVENT_NAME = 'X-Magento-Topic';
    // previously it was X_MAGENTO_HMAC_SHA256 so it was not passing in header
    // I have changed it to watchtower end as well
    const MAGENTO_HEADER_KEY_NAME = 'X-MAGENTO-HMAC-SHA256';
    const MAGENTO_SHOP_DOMAIN = 'X-Magento-Shop-Domain';

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * Curl Adapter
     */
    protected $_curlAdapter;

    /**
     * Json Helper
     * @var [type]
     */
    protected $_jsonHelper;

    /**
     * Webhook factory
     * @var [type]
     */
    protected $_webhookFactory;

    protected $_secretKey = 'ce4d48d07e7c4c287265bae194e7134fc869ddc435c70daff2803949c1077462';

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\HTTP\Adapter\Curl $curlAdapter,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \SweetTooth\Webhook\Model\WebhookFactory $webhookFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        $this->_logger = $logger;
        $this->_curlAdapter = $curlAdapter;
        $this->_jsonHelper = $jsonHelper;
        $this->_webhookFactory = $webhookFactory;
        $this->_storeManager = $storeManagerInterface;
    }

    /**
     * Set new customer group to all his quotes
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $eventCode = $this->_getWebhookEvent();
        $eventData = $this->_getWebhookData($observer);

        $body = [
            'event' => $eventCode,
            'data'  => $eventData
        ];

        $webhooks = $this->_webhookFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('event', $eventCode)
            ->getItems();

        foreach($webhooks as $webhook)
        {
            $this->_sendWebhook($webhook->getUrl(), $body);
        }
    }

    protected function _sendWebhook($url, $body)
    {
        $this->_logger->debug("Sending webhook for event " . $this->_getWebhookEvent() . " to " . $url);

        $bodyJson = $this->_jsonHelper->jsonEncode($body);

        $magentoKeyNameValue = base64_encode(hash_hmac('sha256', $bodyJson, $this->_secretKey, true));

        $realHostUrl = parse_url($this->_storeManager->getStore()->getBaseUrl());

        /*
         * As on every event it was passing fix name so I have made it dynamic
         * it was like that previously
         * WebhookAbstract::MAGENTO_HEADER_EVENT_NAME.": orders/update"
         */
        $headers = [
            "Content-Type: application/json",
            WebhookAbstract::MAGENTO_HEADER_EVENT_NAME.": ".$this->_getWebhookEvent(),
            WebhookAbstract::MAGENTO_HEADER_KEY_NAME.": ".$magentoKeyNameValue,
            WebhookAbstract::MAGENTO_SHOP_DOMAIN.": ".$realHostUrl['host']
        ];

        $this->_curlAdapter->write('POST', $url, '1.1', $headers, $bodyJson);
        $this->_curlAdapter->read();
        $this->_curlAdapter->close();
    }

    protected function _getWebhookEvent()
    {
        // TODO: Throw here because this is an abstract function
        return false;
    }

    protected function _getWebhookData(Observer $observer)
    {
        // TODO: Throw here because this is an abstract function
        return false;
    }
}
