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

class Make extends \Magento\Framework\App\Action\Action
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
        
        $glass = $this->_glassFactory->create();

        $makesCollection = $glass->getCollection();

        $makesCollection->addFieldToSelect('glass_make');

        $makesCollection->getSelect()->group('glass_make')->order('glass_make ASC');
        //print_r($years);

        foreach ($makesCollection->getData() as $make) {
                //print_r($year);
                $makes[]['glass_make'] = trim($make['glass_make']);
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($makes);
        return $resultJson;

    }

    
}