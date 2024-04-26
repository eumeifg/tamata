<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Observer;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\ObserverInterface;

class SetProductPriceObserver implements ObserverInterface
{
    protected $vendorProductData;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cacheManager;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $vendorProductFactory;

    /**
     *
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Framework\App\CacheInterface $cacheManager
     */
    public function __construct(
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Framework\App\CacheInterface $cacheManager
    ) {
        $this->vendorProductFactory = $vendorProductFactory->create();
        $this->vendorRepository = $vendorRepository;
        $this->response = $response;
        $this->redirect = $redirect;
        $this->helper = $helper;
        $this->_cacheManager = $cacheManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magedelight\Catalog\Observer\SetProductPriceObserver
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnabled()) {
            $event = $observer->getEvent();
            $productId = $event->getProduct()->getId();

            if ($event->getProduct()->getTypeId() == Configurable::TYPE_CODE) {
                return $this;
            }

            $vendorId = ($event->getControllerAction()->getRequest()->getParam('v', false)) ?:
                $this->helper->getDefaultVendorId($productId);

            if ($vendorId) {
                $this->_cacheManager->clean('catalog_product_' . $productId);
                $this->vendorProductData = $this->helper->getVendorProduct($vendorId, $productId);
                if ($this->vendorProductData) {
                    $event->getProduct()->setPrice($this->helper->getVendorPrice($vendorId, $productId));

                    $isSalable = ($this->vendorProductData->getQty() > 0) ? true : false;

                    $event->getProduct()->setIsSalable($isSalable);

                    if (!($this->vendorProductData->getSpecialPrice() === null)) {
                        $event->getProduct()->setSpecialFromDate($this->vendorProductData->getSpecialFromDate());
                        $event->getProduct()->setSpecialToDate($this->vendorProductData->getSpecialToDate());
                        $event->getProduct()->setSpecialPrice(
                            $this->helper->getVendorSpecialPrice($vendorId, $productId)
                        );

                        $event->getProduct()->setVendorSpecialPrice(
                            $this->helper->getVendorSpecialPrice($vendorId, $productId)
                        );
                        $event->getProduct()->setVendorSpecialFromDate($this->vendorProductData->getSpecialFromDate());
                        $event->getProduct()->setVendorSpecialToDate($this->vendorProductData->getSpecialToDate());
                    } else {
                        $event->getProduct()->setSpecialFromDate(null);
                        $event->getProduct()->setSpecialToDate(null);
                        $event->getProduct()->setSpecialPrice(null);
                    }

                    $event->getProduct()->setVendorId($vendorId);

                    $vendorName = $this->vendorRepository->getById($vendorId)->getName();

                    $event->getProduct()->setVendorName($vendorName);
                } else {
                    $defaultVendor = $this->vendorProductFactory->getProductDefaultVendor($vendorId, $productId);
                    if (!$defaultVendor) {
                        if ($this->helper->getDefaultVendorId($productId)) {
                            /** redirect product url with default vendor */
                            $url = $event->getProduct()->getProductUrl() . '?v='
                                    . $this->helper->getDefaultVendorId($productId);
                            $this->response->setRedirect($url)->sendResponse();
                            return $this;
                        } else {
                            $event->getProduct()->setIsSalable(false);
                            /* $event->getProduct()->setPrice(NULL);
                            $event->getProduct()->setFinalPrice(NULL);
                            $event->getProduct()->setSpecialPrice(NULL); */
                        }
                    }
                }
            } else {
                if ($event->getProduct()->getTypeId() !== Configurable::TYPE_CODE) {
                    $event->getProduct()->setIsSalable(false);
                    /** exclude configurable product */
                }
            }
        }
    }
}