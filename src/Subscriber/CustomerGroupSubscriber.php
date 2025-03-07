<?php declare(strict_types=1);

namespace AreanetAutoCustomerGroup\Subscriber;

use Shopware\Core\Checkout\Customer\CustomerEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\CustomField\CustomFieldCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CustomerGroupSubscriber implements EventSubscriberInterface
{
    private EntityRepository $customerRepository;
    private EntityRepository $customerGroupRepository;

    public function __construct(EntityRepository $customerRepository, EntityRepository $customerGroupRepository)
    {
        $this->customerRepository       = $customerRepository;
        $this->customerGroupRepository  = $customerGroupRepository;
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

        if ($requestedCustomerGroupId = $customer->getRequestedGroupId()) {
            $group = $this->customerGroupRepository->search(new Criteria([$requestedCustomerGroupId]), $event->getContext())->first();

            /** @var CustomFieldCollection $groupCustomFields */
            $groupCustomFields = $group->getCustomFields();
            if(empty($groupCustomFields['auto_areanetautocustomergroup'])){
                return;
            }

            $this->customerRepository->update([
                [
                    'id' => $customer->getId(),
                    'groupId' => $requestedCustomerGroupId,
                    'requestedGroupId' => null
                ]
            ], $context);
        }
    }
}
