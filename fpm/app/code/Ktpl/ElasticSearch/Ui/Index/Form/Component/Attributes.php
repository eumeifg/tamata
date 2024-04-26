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

namespace Ktpl\ElasticSearch\Ui\Index\Form\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;

/**
 * Class Attributes
 *
 * @package Ktpl\ElasticSearch\Ui\Index\Form\Component
 */
class Attributes extends AbstractComponent
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * Attributes constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        ContextInterface $context,
        $components = [],
        array $data = []
    ) {
        $this->indexRepository = $indexRepository;

        parent::__construct($context, $components, $data);
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return 'attributes';
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $config = $this->getData('config');
        $config['instances'] = [];

        foreach ($this->indexRepository->getList() as $instance) {
            $config['instances'][$instance->getIdentifier()] = $instance->getAttributes();
        }

        $this->setData('config', $config);

        parent::prepare();
    }
}
