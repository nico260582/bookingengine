<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\LayoutHelper;

class BookingmanagerViewSuppliers extends HtmlView
{
    protected $items;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        BookingmanagerHelper::addSubmenu('suppliers');
        $this->addToolbar();

        if (LayoutHelper::getLayoutFile('sidebar')) {
            $this->sidebar = LayoutHelper::render('sidebar');
        }

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
