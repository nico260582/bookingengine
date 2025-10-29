<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use BookingmanagerHelper;

class BookingmanagerViewSuppliers extends HtmlView
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
        BookingmanagerHelper::addSubmenu('suppliers');
        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Suppliers');
        ToolbarHelper::addNew('supplier.add');
        ToolbarHelper::editList('supplier.edit');
        ToolbarHelper::deleteList('Are you sure?', 'suppliers.delete');
    }
}
