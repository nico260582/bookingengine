<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

class BookingmanagerViewBookingrequests extends HtmlView
{
    protected $items;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        BookingmanagerHelper::addSubmenu('bookingrequests');
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolbarHelper::title('Booking Requests');
        JToolbarHelper::addNew('bookingrequest.add');
        JToolbarHelper::editList('bookingrequest.edit');
        JToolbarHelper::deleteList('Are you sure?', 'bookingrequests.delete');
        JToolbarHelper::preferences('com_bookingmanager');
    }
}