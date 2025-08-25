<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

class BookingmanagerViewSubregions extends BaseHtmlView
{
    public $items;
    public $pagination;
    public $state;
    public $filterForm;
    public $sidebar;

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->filterForm = $this->get('FilterForm');

        $this->addToolbar();
        $this->addSidebar();

        parent::display($tpl);
    }

    protected function addSidebar()
    {
        BookingmanagerHelper::addSubmenu('subregions');
        $this->sidebar = \JHtmlSidebar::render();
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_BOOKINGMANAGER_SUB_REGIONS'));

        ToolbarHelper::addNew('subregion.add');
        ToolbarHelper::editList('subregion.edit');
        ToolbarHelper::deleteList('', 'subregions.delete');
        ToolbarHelper::publish('subregions.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('subregions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
    }
}
