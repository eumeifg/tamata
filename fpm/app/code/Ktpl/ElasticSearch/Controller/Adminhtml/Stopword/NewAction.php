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

namespace Ktpl\ElasticSearch\Controller\Adminhtml\Stopword;

use Ktpl\ElasticSearch\Controller\Adminhtml\Stopword;

/**
 * Class NewAction
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Stopword
 */
class NewAction extends Stopword
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->resultRedirectFactory->create()
            ->setPath('search/stopword/edit');
    }
}
