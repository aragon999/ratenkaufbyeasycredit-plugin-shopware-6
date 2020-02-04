<?php declare(strict_types=1);
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Payment;

use Netzkollektiv\EasyCredit\Api\CheckoutFactory;
use Netzkollektiv\EasyCredit\Api\Storage;
use Netzkollektiv\EasyCredit\Helper\PaymentIdProvider;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Checkout implements EventSubscriberInterface
{
    private $paymentIdProvider;

    private $checkoutFactory;

    private $storage;

    private $cache;

    public function __construct(
        PaymentIdProvider $paymentIdProvider,
        CheckoutFactory $checkoutFactory,
        Storage $storage,
        TagAwareAdapterInterface $cache
    ) {
        $this->paymentIdProvider = $paymentIdProvider;
        $this->checkoutFactory = $checkoutFactory;
        $this->storage = $storage;
        $this->cache = $cache;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'onCheckoutConfirmLoaded',
        ];
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function onCheckoutConfirmLoaded(CheckoutConfirmPageLoadedEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();
        /*if (!$this->paymentMethodUtil->isPaypalPaymentMethodInSalesChannel($salesChannelContext)) {
            return;
        }*/
        $checkout = $this->checkoutFactory->create($salesChannelContext);

        $cacheItem = $this->cache->getItem('easycredit-agreement');
        if ($cacheItem->isHit() && $cacheItem->get()) {
            $agreement = $cacheItem->get();
        } else {
            $agreement = $checkout->getAgreement();

            $cacheItem->set($agreement);
            $this->cache->save($cacheItem);
        }

        $error = null;
        if ($this->storage->get('error')) {
            $error = $this->storage->get('error');
            $this->storage->set('error', null);
        }

        $event->getPage()->addExtension('easycredit', (new CheckoutData())->assign([
            'paymentMethodId' => $this->paymentIdProvider->getPaymentMethodId($salesChannelContext->getContext()),
            'agreement' => $agreement,
            'paymentPlan' => $this->storage->get('payment_plan'),
            'error' => $error,
        ]));
    }
}
