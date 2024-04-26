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

namespace Ktpl\ProductLabel\Block\Adminhtml\ProductLabel\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Ktpl\ProductLabel\Api\ProductLabelRepositoryInterface;

abstract class AbstractButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ProductLabelRepositoryInterface
     */
    protected $repository;

    public function __construct(
        Context $context,
        ProductLabelRepositoryInterface $repository
    ) {
        $this->context    = $context;
        $this->repository = $repository;
    }

    /**
     * @return mixed
     */
    abstract public function getButtonData();

    /**
     * Return object ID.
     *
     * @return int|null
     */
    public function getObjectId()
    {
        try {
            $modelId = (int) $this->context->getRequest()->getParam('product_label_id');
            $model = $this->repository->getById($modelId);

            return $model->getProductLabelId();
        } catch (NoSuchEntityException $e) {
        }

        return null;
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
