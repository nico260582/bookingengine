<?php
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;

class BookingmanagerViewSupplier extends HtmlView
{
    protected $form;
    protected $item;
    protected $logs;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $model = $this->getModel();
        $this->logs = $model->getChangeLog($this->item->id);

        // Explicitly load params and pass them to the model
        $params = ComponentHelper::getParams('com_bookingmanager');
        $propertyCategories = $params->get('property_categories', []);
        $this->allProperties = $model->getAllPropertiesWithAssignments($this->item->id, $propertyCategories);

        $this->document->getWebAssetManager()->useScript('form.validate');
        $this->document->addStyleSheet(JUri::root(true) . '/administrator/components/com_bookingmanager/assets/css/bookingmanager.css');
        $this->document->addStyleSheet(JUri::root(true) . '/administrator/components/com_bookingmanager/assets/css/custom-booking-styles.css');
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolbarHelper::title($this->item->id ? 'Edit Supplier' : 'New Supplier');
        JToolbarHelper::apply('supplier.apply');
        JToolbarHelper::save('supplier.save');
        JToolbarHelper::cancel('supplier.cancel');
    }
}