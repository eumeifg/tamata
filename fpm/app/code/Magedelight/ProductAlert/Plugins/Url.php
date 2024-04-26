<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Plugins;

class Url
{
    protected $data;

    protected $_registry;

    protected $_productId;

    protected $_urlHash;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magedelight\ProductAlert\Model\UrlHash $urlHash
    ) {
        $this->_registry = $registry;
        $this->_urlHash = $urlHash;
        $this->data = $registry->registry('md_data');
    }

    protected function getType($subject)
    {
        $type = null;
        if ($subject instanceof \Magento\ProductAlert\Block\Email\Price) {
            $type = 'price';
        }
        if ($subject instanceof \Magento\ProductAlert\Block\Email\Stock) {
            $type = 'stock';
        }
        return $type;
    }

    public function beforeGetProductUnsubscribeUrl($subject, $productId)
    {
        $this->_productId = $productId;
    }

    public function afterGetProductUnsubscribeUrl($subject, $url)
    {
        if ($this->data['guest'] && $this->data['email']) {
            if ($type = $this->getType($subject)) {
                $hash = $this->_urlHash->getHash(
                    $this->_productId,
                    $this->data['email']
                );
                $url .= "?product_id={$this->_productId}&email={$this->data['email']}&hash={$hash}&type={$type}";
            }
        }
        return $url;
    }

    public function afterGetUnsubscribeUrl($subject, $url)
    {
        if ($this->data['guest'] && $this->data['email']) {
            if ($type = $this->getType($subject)) {
                $hash = $this->_urlHash->getHash(
                    \Magedelight\ProductAlert\Model\ResourceModel\Unsubscribe\AlertProvider::REMOVE_ALL,
                    $this->data['email']
                );
                $url .= "?product_id="
                    . \Magedelight\ProductAlert\Model\ResourceModel\Unsubscribe\AlertProvider::REMOVE_ALL
                    . "&email={$this->data['email']}&hash={$hash}&type={$type}";
            }
        }
        return $url;
    }
}
