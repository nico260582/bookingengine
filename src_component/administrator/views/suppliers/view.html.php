<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

class BookingmanagerViewSuppliers extends HtmlView
{
    protected $items;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        BookingmanagerHelper::addSubmenu('suppliers');
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolbarHelper::title('Suppliers');
        JToolbarHelper::addNew('supplier.add');
        JToolbarHelper::editList('supplier.edit');
        JToolbarHelper::deleteList('Are you sure?', 'suppliers.delete');
    }
}