<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\SidebarHelper;


class BookingmanagerViewBookingrequests extends HtmlView
{
    protected $items;
    protected $state;
    protected $pagination;
    protected $filterForm;
    protected $activeFilters;
    public $properties;

    public function display($tpl = null)
    {
        $this->items         = $this->get('Items');
        $this->state         = $this->get('State');
        $this->pagination    = $this->get('Pagination');
        $this->properties    = $this->get('PropertyNames');

        BookingmanagerHelper::addSubmenu('bookingrequests');
        $this->addToolbar();
        $this->sidebar = SidebarHelper::render();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $canDo = ContentHelper::getActions('com_bookingmanager');
        ToolbarHelper::title(Text::_('Booking Requests'), 'address book');

        if ($canDo->get('core.create')) {
            ToolbarHelper::addNew('bookingrequest.add');
        }
        if ($canDo->get('core.edit')) {
            ToolbarHelper::editList('bookingrequest.edit');
        }
        if ($canDo->get('core.delete')) {
            ToolbarHelper::deleteList(Text::_('JGLOBAL_CONFIRM_DELETE'), 'bookingrequests.delete', 'JTOOLBAR_DELETE');
        }
        if ($canDo->get('core.admin')) {
            ToolbarHelper::preferences('com_bookingmanager');
        }
    }
}