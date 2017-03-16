<?php

/**
 * Webhook Edit Form
 */
namespace SweetTooth\Webhook\Block\System\Webhook\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Getter
     *
     * @return \SweetTooth\Webhook\Model\Webhook
     */
    public function getWebhook()
    {
        return $this->_coreRegistry->registry('current_webhook');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return \SweetTooth\Webhook\Block\System\Webhook\Edit\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base', ['legend' => __('Webhook'), 'class' => 'fieldset-wide']);

        $fieldset->addField(
            'event',
            'select',
            [
                'name' => 'event',
                'label' => __('Event'),
                'title' => __('Event'),
                'required' => true,
                'class' => 'validate-xml-identifier',

                /**
                 * TODO: We should build this in some kind of dynamic way
                 * through config or at the very least move this options array
                 * to a shared location so it can be used elsewhere
                 */
                'options' => [
                    'customer/updated' => 'Customer Updated',
                    'customer/deleted' => 'Customer Deleted',
                    'product/updated' => 'Product Updated',
                    'product/deleted' => 'Product Deleted',
                    'order/updated' => 'Order Updated',
                    'order/deleted' => 'Order Deleted',
                    'invoice/updated' => 'Invoice Updated',
                    'shipment/updated' => 'Shipment Updated',
                ]
            ]
        );

        $fieldset->addField(
            'url',
            'text',
            ['name' => 'url', 'label' => __('Url'), 'title' => __('Url'), 'required' => true]
        );

        $form->setValues($this->getWebhook()->getData())->addFieldNameSuffix('webhook')->setUseContainer(true);

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
