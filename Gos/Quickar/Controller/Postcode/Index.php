<?php


namespace Gos\Quickar\Controller\Postcode;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_storeManager;
    
    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Store\Model\StoreManagerInterface $storeManager
                                )
    {
        $this->_storeManager = $storeManager;
        return parent::__construct($context);
    }

    public function execute(){

        $postcode = $this->getRequest()->getPost('postcode');
        //$postcode = 2000;
        
        $firstCode = substr($postcode,0,1);


        switch ($firstCode) {
            case 2:
                $path = $this->_storeManager->getStore(7)->getBaseUrl();// 7 is Sydney Store ID
                break;
            case 4:
                $path = $this->_storeManager->getStore(2)->getBaseUrl();// 2 is Brisbane Store ID
                break;                
            default:
                $path = $this->_storeManager->getStore(1)->getBaseUrl();// 1 is Melbourne Store ID
                break;
        }

        //echo $path; die();

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        // Your code
        $resultRedirect->setPath($path."car/finder/");
        return $resultRedirect;       
    }

    
}