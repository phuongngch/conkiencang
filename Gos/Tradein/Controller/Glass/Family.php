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

class Family extends \Magento\Framework\App\Action\Action
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

        $glass_make = $this->getRequest()->getPost('car_make_id');
		$glass_year = $this->getRequest()->getPost('car_year');
        
        $glass = $this->_glassFactory->create();

        $modelsCollection = $glass->getCollection();

        $modelsCollection->addFieldToSelect('glass_model');

        $modelsCollection->addFieldToFilter('glass_make',$glass_make);
		$modelsCollection->addFieldToFilter('glass_year',$glass_year);

        $modelsCollection->getSelect()->group('glass_model')->order('glass_model ASC');
        //print_r($years);

		$models = array();
		if ($modelsCollection) {
			foreach ($modelsCollection->getData() as $model) {
                //print_r($year);
                $models[]['glass_model'] = trim($model['glass_model']);
        	}
		}

       // $responseData['status'] = 1;
        //$responseData['content'] = $models;

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($models);
        return $resultJson;

    }

    
}