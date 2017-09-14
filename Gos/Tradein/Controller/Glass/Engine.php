<?php
/**
 * Gos_Tradein extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  Gos
 *                     @package   Gos_Tradein
 *                     @copyright Copyright (c) 2017
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Gos\Tradein\Controller\Glass;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Engine extends \Magento\Framework\App\Action\Action
{
    /**
     * Glass Factory
     * 
     * @var \Gos\Tradein\Model\GlassFactory
     */
    protected $_glassFactory;
    protected $resultJsonFactory;

    /**
     * Helper Factory
     * 
     * @var \Gos\Tradein\Model\GlassFactory
     */
    protected $_tradeinHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Gos\Tradein\Helper\Api $tradeinHelper,
        \Gos\Tradein\Model\GlassFactory $glassFactory
    ) {
        parent::__construct($context);
        $this->_glassFactory        = $glassFactory;
        $this->_tradeinHelper        = $tradeinHelper;
    }

    public function execute()
    {
        
        $glass_year = $this->getRequest()->getPost('car_year');
		$glass_make = $this->getRequest()->getPost('car_make');
		$glass_model = $this->getRequest()->getPost('car_model_id');

        $glass = $this->_glassFactory->create();

        $variantCollection = $glass->getCollection();

        $variantCollection->addFieldToSelect('glass_engine');

        $variantCollection->addFieldToFilter('glass_year',$glass_year);
		$variantCollection->addFieldToFilter('glass_make',$glass_make);
		$variantCollection->addFieldToFilter('glass_model',$glass_model);

        $variantCollection->getSelect()->group('glass_engine')->order('glass_engine ASC');
        //print_r($years);

		$variants = array();
		if ($variantCollection) {

       		foreach ($variantCollection->getData() as $variant) {
                //print_r($year);
                $variants[]['glass_engine'] = trim($variant['glass_engine']);
        	}

		}

       // $responseData['status'] = 1;
        //$responseData['content'] = $variants;

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($variants);
        return $resultJson;

    }

    
}