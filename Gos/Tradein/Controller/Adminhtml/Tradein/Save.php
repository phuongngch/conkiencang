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
namespace Gos\Tradein\Controller\Adminhtml\Tradein;

class Save extends \Gos\Tradein\Controller\Adminhtml\Tradein
{
    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * constructor
     * 
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Gos\Tradein\Model\TradeinFactory $tradeinFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Gos\Tradein\Model\TradeinFactory $tradeinFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_backendSession = $backendSession;
        parent::__construct($tradeinFactory, $registry, $resultRedirectFactory, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('tradein');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $tradein = $this->_initTradein();
            $tradein->setData($data);
            $this->_eventManager->dispatch(
                'gos_tradein_tradein_prepare_save',
                [
                    'tradein' => $tradein,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $tradein->save();
                $this->messageManager->addSuccess(__('The Tradein has been saved.'));
                $this->_backendSession->setGosTradeinTradeinData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'gos_tradein/*/edit',
                        [
                            'tradein_id' => $tradein->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('gos_tradein/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Tradein.'));
            }
            $this->_getSession()->setGosTradeinTradeinData($data);
            $resultRedirect->setPath(
                'gos_tradein/*/edit',
                [
                    'tradein_id' => $tradein->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('gos_tradein/*/');
        return $resultRedirect;
    }
}
