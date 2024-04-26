<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Controller\Adminhtml\Index;

use Ktpl\ElasticSearch\Controller\Adminhtml\Index as ParentIndex;

/**
 * Class NewAction
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Index
 */
class NewAction extends ParentIndex
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->resultForwardFactory->create()->forward('edit');
    }
}
