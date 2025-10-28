<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\FileLayout;

class BookingmanagerViewSuppliers extends HtmlView
{
    protected $items;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->addToolbar();

        $layout = new FileLayout('sidebar');
        $this->sidebar = $layout->render();

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
