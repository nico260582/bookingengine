<?php
defined('_JEXEC') or die;

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
        $this->allProperties = $model->getAllPropertiesWithAssignments($this->item->id);
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