<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 */

namespace Dmf\MailAttachment\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Shopware\Core\Checkout\Payment\Exception\InvalidOrderException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Uuid\Uuid;
use function sprintf;

class MailAttachmentTriggerOrderCreationCommand extends Command
{
    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderConverter
     */
    private $orderConverter;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        EntityRepositoryInterface $orderRepository,
        OrderConverter $orderConverter,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->orderConverter = $orderConverter;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dmf:mailattachment:triggerordercreation')
            ->addArgument('orderId', InputArgument::REQUIRED, 'Order ID')
            ->setDescription('Trigger order creation');
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     * @throws InvalidOrderException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orderId = $input->getArgument('orderId');
        if (!$orderId) {
            throw new InvalidArgumentException('Empty "orderId" argument');
        }
        if (!Uuid::isValid($orderId)) {
            throw new InvalidArgumentException(sprintf(
                'Incorrect "orderId" argument: "%s". Should follow this pattern: %s',
                $orderId,
                Uuid::VALID_PATTERN
            ));
        }
        $context = Context::createDefaultContext();
        $criteria = (new Criteria([$orderId]))
            ->addAssociation('lineItems.payload')
            ->addAssociation('deliveries.shippingCosts')
            ->addAssociation('deliveries.shippingMethod')
            ->addAssociation('deliveries.shippingOrderAddress.country')
            ->addAssociation('cartPrice.calculatedTaxes')
            ->addAssociation('transactions.paymentMethod')
            ->addAssociation('currency')
            ->addAssociation('addresses.country')
            ->setLimit(1);
        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search($criteria, $context)->first();
        if (!$order) {
            throw new InvalidOrderException($orderId);
        }
        $salesChannelContext = $this->orderConverter->assembleSalesChannelContext($order, $context);
        $this->eventDispatcher->dispatch(
            new CheckoutOrderPlacedEvent(
                $salesChannelContext->getContext(),
                $order,
                $salesChannelContext->getSalesChannel()->getId()
            )
        );
    }
}
