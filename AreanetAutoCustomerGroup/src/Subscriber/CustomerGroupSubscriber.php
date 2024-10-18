<?php declare(strict_types=1);

namespace AreanetAutoCustomerGroup\Subscriber;

use Shopware\Core\Checkout\Customer\CustomerEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CustomerGroupSubscriber implements EventSubscriberInterface
{
    private EntityRepository $customerRepository;

    public function __construct(EntityRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CustomerRegisterEvent::class => 'onCustomerRegister'
        ];
    }

    public function onCustomerRegister(CustomerRegisterEvent $event): void
    {
        $customer = $event->getCustomer();
        $context = $event->getContext();

        if ($requestedCustomerGroup = $customer->getRequestedGroupId()) {
            $this->customerRepository->update([
                [
                    'id' => $customer->getId(),
                    'groupId' => $requestedCustomerGroup,
                    'requestedGroupId' => null
                ]
            ], $context);
        }
    }
}
