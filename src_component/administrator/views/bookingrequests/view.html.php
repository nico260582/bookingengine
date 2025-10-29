<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\FileLayout;

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

        $layout = new FileLayout('joomla.searchtools.default', ['view' => $this]);
        $this->sidebar = $layout->render();

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
