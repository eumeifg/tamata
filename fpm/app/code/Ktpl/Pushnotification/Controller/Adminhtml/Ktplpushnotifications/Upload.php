<?php

namespace Ktpl\Pushnotification\Controller\Adminhtml\Ktplpushnotifications;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class to upload banner images
 */
class Upload extends \Magento\Backend\App\Action
{

    protected $filter;
    protected $storeManager;
    protected $mediaDirectory;
    protected $resultJsonFactory;
    protected $collectionFactory;
    protected $resultPageFactory;
    protected $fileUploaderFactory;
    protected $coreRegistry = null;
    protected $resultForwardFactory;
    protected $bannerimageRepository;
    protected $bannerimageDataFactory;
    protected $productRepositoryInterface;
    protected $categoryRepositoryInterface;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\App\Action\Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager                = $storeManager;
        $this->fileUploaderFactory         = $fileUploaderFactory;
        $this->mediaDirectory              = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Execute function for class Upload
     */
    public function execute()
    {
        $result = [];
        if ($this->getRequest()->isPost()) {
            try {
                $fields             = $this->getRequest()->getParams();
                $files              = $this->getRequest()->getFiles();

                $mobikulDirPath     = $this->mediaDirectory->getAbsolutePath("pushnotification");
                $bannerimageDirPath = $this->mediaDirectory->getAbsolutePath("pushnotification/images");
                if (!file_exists($mobikulDirPath)) {
                    mkdir($mobikulDirPath, 0777, true);
                }
                if (!file_exists($bannerimageDirPath)) {
                    mkdir($bannerimageDirPath, 0777, true);
                }
                $baseTmpPath  = "pushnotification/images/";
                $target       = $this->mediaDirectory->getAbsolutePath($baseTmpPath);
                try {
                    $uploader = $this->fileUploaderFactory->create(["fileId" => "image_url"]);
                    $fileName = $files["image_url"]["name"];
                    $ext      = substr($fileName, strrpos($fileName, ".") + 1);
                    $editedFileName = "file-".time().".".$ext;

                    $uploader->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                    $uploader->setAllowRenameFiles(true);
                    $result   = $uploader->save($target, $editedFileName);
                    if (!$result) {
                        $result = [
                            "error" => __("File can not be saved to the destination folder."),
                            "errorcode" => ""
                        ];
                    }
                    if (isset($result["file"])) {
                        try {
                            $result["tmp_name"] = str_replace("\\", "/", $result["tmp_name"]);
                            $result["path"]     = str_replace("\\", "/", $result["path"]);
                            $result["id"]       = str_replace("\\", "/", $result["path"]);
                            $result["url"]      = $this->storeManager
                                ->getStore()
                                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$this->getFilePath($baseTmpPath, $result["file"]);
                            $result["name"]     = $result["file"];
                        } catch (\Exception $e) {
                            $result = ["error"=>$e->getMessage(), "errorcode"=>$e->getCode()];
                        }
                    }
                    $result["cookie"] = [
                        "name"     => $this->_getSession()->getName(),
                        "path"     => $this->_getSession()->getCookiePath(),
                        "value"    => $this->_getSession()->getSessionId(),
                        "domain"   => $this->_getSession()->getCookieDomain(),
                        "lifetime" => $this->_getSession()->getCookieLifetime()
                    ];
                } catch (\Exception $e) {
                    $result = ["error"=>$e->getMessage(), "errorcode"=>$e->getCode()];
                }
            } catch (\Exception $e) {
                $result = ["error"=>$e->getMessage(), "errorcode"=>$e->getCode()];
            }
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * Function to get fulepath
     *
     * @return string
     */
    public function getFilePath($path, $imageName)
    {
        return rtrim($path, "/") . "/" . ltrim($imageName, "/");
    }
}
