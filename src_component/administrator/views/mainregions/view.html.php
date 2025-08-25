<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

class BookingmanagerViewMainregions extends BaseHtmlView
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
        BookingmanagerHelper::addSubmenu('mainregions');
        $this->sidebar = \JHtmlSidebar::render();
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_BOOKINGMANAGER_MAIN_REGIONS'));

        ToolbarHelper::addNew('mainregion.add');
        ToolbarHelper::editList('mainregion.edit');
        ToolbarHelper::deleteList('', 'mainregions.delete');
        ToolbarHelper::publish('mainregions.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('mainregions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
    }
}
