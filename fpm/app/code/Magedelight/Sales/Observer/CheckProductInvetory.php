<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckProductInvetory implements ObserverInterface
{
    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/check_inventory/template';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    private $vendorRepository;
    /**
     * @var \Magedelight\Catalog\Model\Product
     */
    private $product;
    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Reports\Product\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\Catalog\Model\Product $product,
        \Magedelight\Catalog\Model\ResourceModel\Reports\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->product = $product;
        $this->collectionFactory = $collectionFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->vendorRepository = $vendorRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->getValue('emailconfiguration/check_inventory/enabled')) {
            $this->logger->debug('Testing product enventory new');
            $vendorId = $observer->getEvent()->getVendorId();
            $productId = $observer->getEvent()->getProductId();
            $vendorProduct = $this->product->getVendorProduct($vendorId, $productId);
            $vendorDetail = $this->vendorRepository->getById($vendorId);

            if ($vendorProduct && ($vendorProduct->getQty() == $vendorProduct->getReorderLevel() ||
                    $vendorProduct->getQty() < $vendorProduct->getReorderLevel())) {
                $this->_sendNotification(
                    $vendorDetail->getEmail(),
                    $vendorDetail->getName(),
                    $vendorProduct->getName()
                );
            }
        }
    }

    protected function _sendNotification($vendorEmail, $vendorName, $vendorProductName)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $store_email = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);

        $templateVars = [
            'vendorEmail'  => $vendorName,
            'vendorProductName' => $vendorProductName
        ];

        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFromByScope('general')
            ->addTo($vendorEmail)
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }
}
