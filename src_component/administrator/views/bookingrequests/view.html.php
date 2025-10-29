<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use BookingmanagerHelper;

class BookingmanagerViewBookingrequests extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $filterForm;
	public $sidebar;

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->filterForm = $this->get('FilterForm');

        $this->addToolbar();

		BookingmanagerHelper::addSubmenu('bookingrequests');
		$this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Booking Requests');
        ToolbarHelper::addNew('bookingrequest.add');
        ToolbarHelper::editList('bookingrequest.edit');
        ToolbarHelper::deleteList('Are you sure you want to delete these requests?', 'bookingrequests.delete');
    }
}
