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

namespace Ktpl\ElasticSearch\Controller\Adminhtml\ScoreRule;

use Magento\Rule\Model\Condition\AbstractCondition;
use Ktpl\ElasticSearch\Controller\Adminhtml\ScoreRule;
use Ktpl\ElasticSearch\Model\ScoreRule\Rule;

/**
 * Class NewPostConditionHtml
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\ScoreRule
 */
class NewPostConditionHtml extends ScoreRule
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));

        $attribute = false;

        $class = $typeArr[0];

        if (count($typeArr) == 2) {
            $attribute = $typeArr[1];
        }

        $model = $this->_objectManager->get($class);

        $model->setId($id)
            ->setType($class)
            ->setRule($this->_objectManager->create(Rule::class))
            ->setPrefix('post_conditions')
            ->setFormName($this->getRequest()->getParam('form_name', Rule::FORM_NAME));

        if ($attribute) {
            $model->setAttribute($attribute);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Check is condition allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
