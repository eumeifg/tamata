<?php

namespace MDC\Custom\Controller\Adminhtml\Category\Mobilecategroybanner;

use Magento\Framework\Controller\ResultFactory;

class Upload extends \Magento\Backend\App\Action
{
    protected $baseTmpPath;
      /**
       * Image uploader
       *
       * @var \Magento\Catalog\Model\ImageUploader
       */
    protected $imageUploader;

    /**
     * Upload constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\ImageUploader $imageUploader
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ImageUploader $imageUploader
    ) {
        $this->imageUploader = $imageUploader;
        parent::__construct($context);
    }
    public function execute()
    {
        try {
            $result = $this->imageUploader->saveFileToTmpDir('mobile_category_banner');
            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
         return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
