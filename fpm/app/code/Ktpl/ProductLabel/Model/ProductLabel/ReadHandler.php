<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   Ktpl_ProductLabel
  * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */

namespace Ktpl\ProductLabel\Model\ProductLabel;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Ktpl\ProductLabel\Helper\Data as DataHelper;

class ReadHandler implements ExtensionInterface
{

    protected $dataHelper;

    protected $_state;

    /**
     * ReadHandler constructor.
     * @param DataHelper $dataHelper
     * @param \Ktpl\ProductLabel\Block\ProductLabel\ProductLabel $productLabel
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
      DataHelper $dataHelper,
      \Ktpl\ProductLabel\Block\ProductLabel\ProductLabel $productLabel,
      \Magento\Framework\App\State $state
    ) {
        $this->dataHelper = $dataHelper;
        $this->productLabel = $productLabel;
        $this->_state = $state;
    }

    public function execute($product, $arguments = [])
    {
        $extension = $product->getExtensionAttributes();

        if ($extension->getProductLabels() !== null) {
            return $product;
        }
        if ($this->_state->getAreaCode() == "frontend") {
          $this->productLabel->setProduct($product);
        }
        /*$lables = $this->productLabel->getProductLabels(true);*/
        $productLabels = $this->dataHelper->getProductPLabels($product);
        $extension->setProductLabels($productLabels);
        $product->setExtensionAttributes($extension);
        return $product;
    }
}
