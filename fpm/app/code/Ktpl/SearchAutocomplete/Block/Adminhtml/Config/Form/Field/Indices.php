<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchAutocomplete
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchAutocomplete\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Ktpl\SearchAutocomplete\Api\Data\IndexInterface;
use Ktpl\SearchAutocomplete\Api\Repository\IndexRepositoryInterface;

/**
 * Class Indices
 *
 * @package Ktpl\SearchAutocomplete\Block\Adminhtml\Config\Form\Field
 */
class Indices extends Field
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * Indices constructor.
     *
     * @param IndexRepositoryInterface $indexService
     * @param Context $context
     */
    public function __construct(
        IndexRepositoryInterface $indexService,
        Context $context
    )
    {
        $this->indexRepository = $indexService;

        return parent::__construct($context);
    }

    /**
     * Render index element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);

        return $this->_toHtml();
    }

    /**
     * Available indexes
     *
     * @return IndexInterface[]
     */
    public function getIndices()
    {
        $indices = $this->indexRepository->getIndices();

        foreach ($indices as $index) {
            $index->addData([
                'is_active' => $this->getValue($index, 'is_active'),
                'limit' => intval($this->getValue($index, 'limit')),
                'order' => intval($this->getValue($index, 'order')),
            ]);
        }

        usort($indices, function ($a, $b) {
            return (int)$a->getOrder() - (int)$b->getOrder();
        });

        return $indices;
    }

    /**
     * Get identifier values
     *
     * @param IndexInterface $index
     * @param string $item
     * @return string
     */
    public function getValue($index, $item)
    {
        $identifier = $index->getIdentifier();

        if ($this->getElement()->getData('value') && is_array($this->getElement()->getData('value'))) {
            $values = $this->getElement()->getData('value');
            if (isset($values[$identifier]) && isset($values[$identifier][$item])) {
                return $values[$identifier][$item];
            }
        }

        return false;
    }

    /**
     * Index name
     *
     * @param IndexInterface $index
     * @return string
     */
    public function getNamePrefix($index)
    {
        return $this->getElement()->getName() . '[' . $index->getIdentifier() . ']';
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->setTemplate('Ktpl_SearchAutocomplete::config/form/field/indices.phtml');
    }
}
