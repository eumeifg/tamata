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

namespace Ktpl\ElasticSearch\Ui\ScoreRule\Form\Block;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset as FieldsetRenderer;
use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;
use Ktpl\ElasticSearch\Model\ScoreRule\Rule;

/**
 * Class PostConditions
 *
 * @package Ktpl\ElasticSearch\Ui\ScoreRule\Form\Block
 */
class PostConditions extends Generic implements TabInterface
{
    /**
     * @var FieldsetRenderer
     */
    private $fieldsetRenderer;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var PostConditionsRenderer
     */
    private $postConditionsRenderer;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var string
     */
    private $formName;

    /**
     * PostConditions constructor.
     *
     * @param PostConditionsRenderer $postConditionsRenderer
     * @param FieldsetRenderer $fieldsetRenderer
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     */
    public function __construct(
        PostConditionsRenderer $postConditionsRenderer,
        FieldsetRenderer $fieldsetRenderer,
        Context $context,
        Registry $registry,
        FormFactory $formFactory
    )
    {
        $this->setNameInLayout('post_conditions');

        $this->postConditionsRenderer = $postConditionsRenderer;
        $this->fieldsetRenderer = $fieldsetRenderer;
        $this->formFactory = $formFactory;
        $this->registry = $registry;

        parent::__construct($context, $registry, $formFactory);
    }

    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return '';
    }

    /**
     * Check if tab can show
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check if tab is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form
     *
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $this->formName = Rule::FORM_NAME;

        /** @var ScoreRuleInterface $scoreRule */
        $scoreRule = $this->registry->registry(ScoreRuleInterface::class);
        $rule = $scoreRule->getRule();

        $form = $this->formFactory->create();

        $form->setHtmlIdPrefix('rule_');

        $renderer = $this->fieldsetRenderer
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setData('new_child_url', $this->getUrl('search/scoreRule/newPostConditionHtml', [
                'form' => 'rule_post_conditions_fieldset',
                'form_name' => $this->formName,
            ]));

        $fieldset = $form->addFieldset(
            'post_conditions_fieldset',
            [
                'legend' => __('Apply the rule only when the following conditions are met:'),
                'class' => 'fieldset',
            ]
        )->setRenderer($renderer);

        $rule->getConditions()->setFormName($this->formName);

        $conditionsField = $fieldset->addField('post_conditions', 'text', [
            'name' => 'post_conditions',
            'required' => true,
            'data-form-part' => $this->formName,
        ]);

        $conditionsField->setRule($rule)
            ->setRenderer($this->postConditionsRenderer)
            ->setFormName($this->formName);

        $this->setConditionFormName($rule->getPostConditions(), $this->formName);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Set condition form name
     *
     * @param object $conditions
     * @param string $formName
     * @return void
     */
    private function setConditionFormName($conditions, $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }
}
