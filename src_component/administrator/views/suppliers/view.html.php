<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\HTML\Helpers\Sidebar;

class BookingmanagerViewSuppliers extends HtmlView
{
    protected $items;
    protected $sidebar;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        BookingmanagerHelper::addSubmenu('suppliers');
        $this->addToolbar();
        $this->sidebar = Sidebar::render();
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