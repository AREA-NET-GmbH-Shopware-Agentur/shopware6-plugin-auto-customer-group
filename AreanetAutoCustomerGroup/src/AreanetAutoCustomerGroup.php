<?php declare(strict_types=1);

namespace AreanetAutoCustomerGroup;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class AreanetAutoCustomerGroup extends Plugin
{
    const FIELD_SET_NAME = 'areanetautocustomergroup';

    public function update(UpdateContext $context): void
    {
        parent::update($context);
        $this->installCustomFields($context);
    }

    public function install(InstallContext $context): void {
        $this->installCustomFields($context);
    }

    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }

        $this->removeCustomField($context);
    }

    private function installCustomFields($context){
        $customFields = [
            [
                'id' => md5(self::FIELD_SET_NAME),
                'name' => self::FIELD_SET_NAME,
                'active' => true,
                'config' => [
                    'label' => [
                        'en-GB' => 'registerform',
                        'de-DE' => 'Registrierungsformular'
                    ],
                ],
                'customFields' => [
                    [
                        'id' => md5('boxed_form_areanetautocustomergroup'),
                        'name' => 'boxed_form_areanetautocustomergroup',
                        'type' => CustomFieldTypes::BOOL,
                        'config' => [
                            'label' => [
                                'en-GB' => 'Formular boxed',
                                'de-DE' => 'Formular boxed'
                            ],
                            "componentName" => "sw-field",
                            "customFieldType" => "checkbox"
                        ]
                    ]
                ],
                'relations' => [
                    [
                        'id' => md5("customer_group_areanetautocustomergroup"),
                        'entityName' => 'customer_group'
                    ]
                ]
            ]
        ];

        $repo = $this->container->get('custom_field_set.repository');

        foreach ($customFields as $customFieldSet) {
            $repo->upsert([$customFieldSet], $context->getContext());
        }
    }

    private function customFieldsExist(Context $context): ?IdSearchResult
    {
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('name', [self::FIELD_SET_NAME]));

        $ids = $customFieldSetRepository->searchIds($criteria, $context);

        return $ids->getTotal() > 0 ? $ids : null;
    }

    private function removeCustomField(UninstallContext $uninstallContext)
    {
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $fieldIds = $this->customFieldsExist($uninstallContext->getContext());

        if ($fieldIds) {
            $customFieldSetRepository->delete(array_values($fieldIds->getData()), $uninstallContext->getContext());
        }
    }
}
