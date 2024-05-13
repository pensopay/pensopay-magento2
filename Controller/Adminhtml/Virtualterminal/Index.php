<?php

namespace PensoPay\Gateway\Controller\Adminhtml\Virtualterminal;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
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
        //eda05a709b950ca9d78b2622d613bfd2068459ece3e64eb1efe97bceb791cfc0
        $page = $this->_resultPageFactory->create();
        $page->getConfig()->getTitle()->prepend(__('PensoPay Virtualterminal'));
        return $page;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('PensoPay_Gateway::virtualterminal');
    }
}
