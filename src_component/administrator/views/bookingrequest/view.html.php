<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

class BookingmanagerViewBookingrequest extends HtmlView
{
    protected $form;
    protected $item;
    protected $messages;
    protected $logs;
    protected $attachments;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $model = $this->getModel();
        $this->messages = $model->getMessages($this->item->id);
        $this->logs = $model->getChangeLog($this->item->id);
        $this->attachments = $model->getAttachments($this->item->id);
        $this->document->getWebAssetManager()->useScript('form.validate');
        $this->document->addStyleSheet(JUri::root(true) . '/administrator/components/com_bookingmanager/assets/css/bookingmanager.css');
        $this->document->addStyleSheet(JUri::root(true) . '/administrator/components/com_bookingmanager/assets/css/custom-booking-styles.css');
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolbarHelper::title($this->item->id ? 'Edit Request' : 'New Request');
        JToolbarHelper::apply('bookingrequest.apply');
        JToolbarHelper::save('bookingrequest.save');
        JToolbarHelper::cancel('bookingrequest.cancel');
    }
}