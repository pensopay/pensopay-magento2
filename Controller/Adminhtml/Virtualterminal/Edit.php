<?php

namespace PensoPay\Gateway\Controller\Adminhtml\Virtualterminal;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /** @var PageFactory $_resultPageFactory */
    protected $_resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $page = $this->_resultPageFactory->create();
        $page->getConfig()->getTitle()->prepend(__('PensoPay Virtualterminal Payment'));
        return $page;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('PensoPay_Gateway::virtualterminal');
    }
}
