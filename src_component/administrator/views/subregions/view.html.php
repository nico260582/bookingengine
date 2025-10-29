<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use BookingmanagerHelper;

class BookingmanagerViewSubregions extends BaseHtmlView
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
        BookingmanagerHelper::addSubmenu('subregions');
        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Sub Regions');
        ToolbarHelper::addNew('subregion.add');
        ToolbarHelper::editList('subregion.edit');
        ToolbarHelper::deleteList('', 'subregions.delete');
    }
}
