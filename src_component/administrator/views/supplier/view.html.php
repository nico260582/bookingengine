<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

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
        $this->document->addStyleSheet(Uri::root(true) . '/administrator/components/com_bookingmanager/assets/css/bookingmanager.css');
        $this->document->addStyleSheet(Uri::root(true) . '/administrator/components/com_bookingmanager/assets/css/custom-booking-styles.css');
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title($this->item->id ? 'Edit Supplier' : 'New Supplier');
        ToolbarHelper::apply('supplier.apply');
        ToolbarHelper::save('supplier.save');
        ToolbarHelper::cancel('supplier.cancel');
    }
}