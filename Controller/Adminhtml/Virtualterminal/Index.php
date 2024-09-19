<?php

namespace Pensopay\Gateway\Controller\Adminhtml\Virtualterminal;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected PageFactory $_resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory    $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $page = $this->_resultPageFactory->create();
        $page->getConfig()->getTitle()->prepend(__('pensopay Virtualterminal'));
        return $page;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Pensopay_Gateway::virtualterminal');
    }
}
