<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class BookingmanagerViewBookingrequest extends HtmlView
{
    protected $form;
    protected $item;
    protected $messages;
    protected $logs;
    protected $activityLogs;
    protected $attachments;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $model = $this->getModel();
        $this->messages = $model->getMessages($this->item->id);
        $this->supplierMessages = $model->getSupplierMessages($this->item->id);
        $this->logs = $model->getChangeLog($this->item->id);
        $this->activityLogs = $model->getActivityLog($this->item->id);
        $this->attachments = $model->getAttachments($this->item->id);
        $this->document->getWebAssetManager()->useScript('form.validate');
        $this->document->addStyleSheet(Uri::root(true) . '/administrator/components/com_bookingmanager/assets/css/bookingmanager.css');
        $this->document->addStyleSheet(Uri::root(true) . '/administrator/components/com_bookingmanager/assets/css/custom-booking-styles.css');
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title($this->item->id ? 'Edit Request' : 'New Request');
        ToolbarHelper::apply('bookingrequest.apply');
        ToolbarHelper::save('bookingrequest.save');
        ToolbarHelper::cancel('bookingrequest.cancel');
    }
}