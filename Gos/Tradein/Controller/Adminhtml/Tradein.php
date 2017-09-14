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
namespace Gos\Tradein\Controller\Adminhtml;

abstract class Tradein extends \Magento\Backend\App\Action
{
    /**
     * Tradein Factory
     * 
     * @var \Gos\Tradein\Model\TradeinFactory
     */
    protected $_tradeinFactory;

    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Result redirect factory
     * 
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $_resultRedirectFactory;

    /**
     * constructor
     * 
     * @param \Gos\Tradein\Model\TradeinFactory $tradeinFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Gos\Tradein\Model\TradeinFactory $tradeinFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_tradeinFactory        = $tradeinFactory;
        $this->_coreRegistry          = $coreRegistry;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Init Tradein
     *
     * @return \Gos\Tradein\Model\Tradein
     */
    protected function _initTradein()
    {
        $tradeinId  = (int) $this->getRequest()->getParam('tradein_id');
        /** @var \Gos\Tradein\Model\Tradein $tradein */
        $tradein    = $this->_tradeinFactory->create();
        if ($tradeinId) {
            $tradein->load($tradeinId);
        }
        $this->_coreRegistry->register('gos_tradein_tradein', $tradein);
        return $tradein;
    }
}
