<?php

namespace Pensopay\Gateway\Test\Unit\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Pensopay\Gateway\Controller\Payment\Redirect;
use Pensopay\Gateway\Helper\Checkout as PensopayCheckoutHelper;
use Pensopay\Gateway\V2\Response\PaymentLinkHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RedirectTest extends TestCase
{
    private const PAYMENT_LINK = 'https://pay.pensopay.com/payment/abc123';

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var Session|MockObject */
    private $session;

    /** @var Payment|MockObject */
    private $payment;

    /** @var ManagerInterface|MockObject */
    private $messageManager;

    /** @var RedirectResult|MockObject */
    private $redirect;

    private Redirect $controller;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);

        $this->payment = $this->createMock(Payment::class);
        $order = $this->createMock(Order::class);
        $order->method('getPayment')->willReturn($this->payment);
        $order->method('getIncrementId')->willReturn('000000123');

        $this->session = $this->createMock(Session::class);
        $this->session->method('getLastRealOrder')->willReturn($order);

        $checkoutHelper = $this->createMock(PensopayCheckoutHelper::class);
        $checkoutHelper->method('getCheckoutSession')->willReturn($this->session);

        $this->redirect = $this->createMock(RedirectResult::class);
        $redirectFactory = $this->createMock(RedirectFactory::class);
        $redirectFactory->method('create')->willReturn($this->redirect);

        $this->controller = new Redirect(
            $this->logger,
            $checkoutHelper,
            $redirectFactory,
            $this->messageManager
        );
    }

    public function testRedirectsToPaymentLink(): void
    {
        $this->payment->method('getAdditionalInformation')
            ->with(PaymentLinkHandler::PAYMENT_LINK)
            ->willReturn(self::PAYMENT_LINK);

        $this->redirect->expects($this->once())->method('setUrl')->with(self::PAYMENT_LINK)->willReturnSelf();
        $this->redirect->expects($this->never())->method('setPath');
        $this->session->expects($this->never())->method('restoreQuote');
        $this->messageManager->expects($this->never())->method('addExceptionMessage');

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testMissingPaymentLinkRestoresQuoteAndRedirectsToCart(): void
    {
        $this->payment->method('getAdditionalInformation')->willReturn(null);

        $this->assertFailureHandled();
    }

    public function testExceptionRestoresQuoteAndRedirectsToCart(): void
    {
        $this->payment->method('getAdditionalInformation')
            ->willThrowException(new LocalizedException(__('Boom')));

        $this->assertFailureHandled();
    }

    private function assertFailureHandled(): void
    {
        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage')
            ->with($this->isInstanceOf(LocalizedException::class));
        $this->logger->expects($this->once())->method('critical');
        $this->session->expects($this->once())->method('restoreQuote');
        $this->redirect->expects($this->never())->method('setUrl');
        $this->redirect->expects($this->once())->method('setPath')->with('checkout/cart')->willReturnSelf();

        $this->assertSame($this->redirect, $this->controller->execute());
    }
}
