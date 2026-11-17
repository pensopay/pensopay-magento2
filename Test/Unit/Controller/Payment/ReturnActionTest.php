<?php

namespace Pensopay\Gateway\Test\Unit\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Pensopay\Gateway\Controller\Payment\ReturnAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReturnActionTest extends TestCase
{
    private const ORDER_HASH = 'encrypted-hash';
    private const INCREMENT_ID = '000000123';
    private const ENTITY_ID = 42;
    private const QUOTE_ID = 7;

    /** @var Session|MockObject */
    private $session;

    /** @var EncryptorInterface|MockObject */
    private $encryptor;

    /** @var RequestInterface|MockObject */
    private $request;

    /** @var SearchCriteriaBuilder|MockObject */
    private $searchCriteriaBuilder;

    /** @var OrderRepositoryInterface|MockObject */
    private $orderRepository;

    /** @var Redirect|MockObject */
    private $redirect;

    private ReturnAction $controller;

    protected function setUp(): void
    {
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'getLastRealOrderId',
                'setLastSuccessQuoteId',
                'setLastQuoteId',
                'setLastRealOrderId',
                'setLastOrderId',
            ])
            ->getMock();

        $this->encryptor = $this->createMock(EncryptorInterface::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);

        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->searchCriteriaBuilder->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->method('setPageSize')->willReturnSelf();
        $this->searchCriteriaBuilder->method('create')->willReturn($this->createMock(SearchCriteria::class));

        $this->redirect = $this->createMock(Redirect::class);
        $this->redirect->expects($this->once())
            ->method('setPath')
            ->with('checkout/onepage/success')
            ->willReturnSelf();

        $redirectFactory = $this->createMock(RedirectFactory::class);
        $redirectFactory->method('create')->willReturn($this->redirect);

        $this->controller = new ReturnAction(
            $this->session,
            $this->encryptor,
            $this->request,
            $redirectFactory,
            $this->searchCriteriaBuilder,
            $this->orderRepository
        );
    }

    public function testSessionWithOrderSkipsLookup(): void
    {
        $this->session->method('getLastRealOrderId')->willReturn(self::INCREMENT_ID);

        $this->request->expects($this->never())->method('getParam');
        $this->orderRepository->expects($this->never())->method('getList');
        $this->session->expects($this->never())->method('setLastRealOrderId');

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testMissingOrderHashSkipsLookup(): void
    {
        $this->session->method('getLastRealOrderId')->willReturn(null);
        $this->request->method('getParam')->with('ori')->willReturn(null);

        $this->encryptor->expects($this->never())->method('decrypt');
        $this->orderRepository->expects($this->never())->method('getList');
        $this->session->expects($this->never())->method('setLastRealOrderId');

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testUndecryptableOrderHashSkipsLookup(): void
    {
        $this->session->method('getLastRealOrderId')->willReturn(null);
        $this->request->method('getParam')->with('ori')->willReturn('tampered');
        $this->encryptor->method('decrypt')->with('tampered')->willReturn('');

        $this->orderRepository->expects($this->never())->method('getList');
        $this->session->expects($this->never())->method('setLastRealOrderId');

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testUnknownOrderLeavesSessionUntouched(): void
    {
        $this->givenLostSessionWithValidHash();
        $this->orderRepository->method('getList')->willReturn($this->createSearchResult(null));

        $this->session->expects($this->never())->method('setLastRealOrderId');
        $this->session->expects($this->never())->method('setLastOrderId');

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testMismatchingIncrementIdLeavesSessionUntouched(): void
    {
        $this->givenLostSessionWithValidHash();
        $this->orderRepository->method('getList')
            ->willReturn($this->createSearchResult($this->createOrder('000000999')));

        $this->session->expects($this->never())->method('setLastRealOrderId');
        $this->session->expects($this->never())->method('setLastOrderId');

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testLostSessionIsRestoredFromOrderHash(): void
    {
        $this->givenLostSessionWithValidHash();

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with('increment_id', self::INCREMENT_ID)
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('setPageSize')
            ->with(1)
            ->willReturnSelf();

        $this->orderRepository->expects($this->once())
            ->method('getList')
            ->willReturn($this->createSearchResult($this->createOrder(self::INCREMENT_ID)));

        $this->session->expects($this->once())->method('setLastSuccessQuoteId')->with(self::QUOTE_ID);
        $this->session->expects($this->once())->method('setLastQuoteId')->with(self::QUOTE_ID);
        $this->session->expects($this->once())->method('setLastRealOrderId')->with(self::INCREMENT_ID);
        // Must be the order entity id, not the increment id (used by success page / analytics observers)
        $this->session->expects($this->once())->method('setLastOrderId')->with(self::ENTITY_ID);

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    private function givenLostSessionWithValidHash(): void
    {
        $this->session->method('getLastRealOrderId')->willReturn(null);
        $this->request->method('getParam')->with('ori')->willReturn(self::ORDER_HASH);
        $this->encryptor->method('decrypt')->with(self::ORDER_HASH)->willReturn(self::INCREMENT_ID);
    }

    /**
     * @param Order|null $order
     * @return OrderCollection|MockObject
     */
    private function createSearchResult(?Order $order)
    {
        $searchResult = $this->createMock(OrderCollection::class);
        $searchResult->method('getTotalCount')->willReturn($order ? 1 : 0);
        if ($order) {
            $searchResult->method('getFirstItem')->willReturn($order);
        }
        return $searchResult;
    }

    /**
     * @param string $incrementId
     * @return Order|MockObject
     */
    private function createOrder(string $incrementId)
    {
        $order = $this->createMock(Order::class);
        $order->method('getIncrementId')->willReturn($incrementId);
        $order->method('getEntityId')->willReturn((string)self::ENTITY_ID);
        $order->method('getQuoteId')->willReturn(self::QUOTE_ID);
        return $order;
    }
}
