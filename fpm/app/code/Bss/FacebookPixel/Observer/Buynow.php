<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_FacebookPixel
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\FacebookPixel\Observer;

use Magento\Framework\Event\ObserverInterface;

class Buynow implements ObserverInterface
{
    /**
     * @var \Bss\FacebookPixel\Model\SessionFactory
     */
    protected $fbPixelSession;

    /**
     * @var \Bss\FacebookPixel\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * AddToCart constructor.
     * @param \Bss\FacebookPixel\Model\SessionFactory $fbPixelSession
     * @param \Bss\FacebookPixel\Helper\Data $helper
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        \Bss\FacebookPixel\Model\SessionFactory $fbPixelSession,
        \Bss\FacebookPixel\Helper\Data $helper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->fbPixelSession = $fbPixelSession;
        $this->helper        = $helper;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $this->checkoutSession->getQuote();
        $items = $quote->getAllItems();
        $typeConfi = \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
        if (!$this->helper->isBuyNow() || !$items) {
            return true;
        }
        $product = [
            'content_ids' => [],
            'value' => 0,
            'currency' => ""
        ];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($items as $item) {
            if ($item->getProduct()->getTypeId() == $typeConfi) {
                continue;
            }
            if ($item->getParentItem()) {
                if ($item->getParentItem()->getProductType() == $typeConfi) {
                    $product['contents'][] = [
                        'id' => $item->getSku(),
                        'name' => $item->getName(),
                        'quantity' => $item->getParentItem()->getQtyToAdd()
                    ];
                    $product['value'] += $item->getProduct()->getFinalPrice() * $item->getParentItem()->getQtyToAdd();
                } else {
                    $product['contents'][] = [
                        'id' => $item->getSku(),
                        'name' => $item->getName(),
                        'quantity' => $item->getData('qty')
                    ];
                }
            } else {
                $product['contents'][] = [
                    'id' => $this->checkBundleSku($item),
                    'name' => $item->getName(),
                    'quantity' => $item->getQtyToAdd()
                ];
                $product['value'] += $item->getProduct()->getFinalPrice() * $item->getQtyToAdd();
            }
            $product['content_ids'][] = $this->checkBundleSku($item);
        }

        $data = [
            'content_type' => 'product',
            'content_ids' => $product['content_ids'],
            'contents' => $product['contents'],
            'currency' => $this->helper->getCurrencyCode(),
            'value' => $product['value']
        ];
        $this->fbPixelSession->create()->setBuyNow($data);

        return true;
    }

    /**
     * @param mixed $item
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function checkBundleSku($item)
    {
        $typeBundle = \Magento\Bundle\Model\Product\Type::TYPE_CODE;
        if ($item->getProductType() == $typeBundle) {
            $skuBundleProduct= $this->productRepository->getById($item->getProductId())->getSku();
            return $skuBundleProduct;
        }
        return $item->getSku();
    }
}
