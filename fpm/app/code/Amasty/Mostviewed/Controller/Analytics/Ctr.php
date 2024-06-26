<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Controller\Analytics;

use Amasty\Mostviewed\Api\ViewRepositoryInterface;
use Amasty\Mostviewed\Api\ClickRepositoryInterface;
use Exception;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Amasty\Mostviewed\Model\Analytics\ViewFactory;
use Amasty\Mostviewed\Model\Analytics\ClickFactory;
use Psr\Log\LoggerInterface;

class Ctr extends Action
{
    /**
     * @var ViewFactory|ClickFactory
     */
    private $tempFactory;

    /**
     * @var ViewRepositoryInterface|ClickRepositoryInterface
     */
    private $dataRepository;

    /**
     * @var array
     */
    private $visitorData;

    /**
     * @var string
     */
    private $subjectName = '';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    public function __construct(
        $tempFactory,
        $dataRepository,
        SessionManagerInterface $session,
        $subjectName,
        LoggerInterface $logger,
        Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->tempFactory = $tempFactory;
        $this->dataRepository = $dataRepository;
        $this->visitorData = $session->getVisitorData();
        $this->subjectName = $subjectName;
        $this->logger = $logger;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            return;
        }

        $data = ['error' => true];
        if ($this->formKeyValidator->validate($this->getRequest())) {
            try {
                $this->updateCounter();
                $data = ['success' => true];
            } catch (Exception $exception) {
                $this->logger->log(
                    \Monolog\Logger::ERROR,
                    'Cannot save mostviewed rules statistics. Error: ' . $exception->getMessage()
                );
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);

        return $resultJson;
    }

    /**
     * {@inheritDoc}
     */
    private function updateCounter()
    {
        $param = $this->getRequest()->getParam($this->subjectName, false);
        if ($param) {
            /** @var \Amasty\Mostviewed\Model\Analytics\View|\Amasty\Mostviewed\Model\Analytics\Click $object */
            $object = $this->tempFactory->create();
            $object->setData($this->subjectName, $param);
            $object->setBlockId($this->getRequest()->getParam('block_id'));
            if (isset($this->visitorData['visitor_id'])) {
                $object->setVisitorId($this->visitorData['visitor_id']);
            }

            $this->dataRepository->save($object);
        }
    }
}
