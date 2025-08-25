<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

class BookingmanagerViewMainregions extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $filterForm;
    protected $sidebar;

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
        require_once JPATH_COMPONENT . '/helpers/bookingmanager.php';

        $vName = 'mainregions';

        \JHtmlSidebar::addEntry('<i class="icon-calendar"></i> ' . Text::_('COM_BOOKINGMANAGER_BOOKING_REQUESTS'), 'index.php?option=com_bookingmanager&view=bookingrequests', $vName == 'bookingrequests' || $vName == 'bookingrequest');
        \JHtmlSidebar::addEntry('<i class="icon-home"></i> ' . Text::_('COM_BOOKINGMANAGER_PROPERTIES'), 'index.php?option=com_bookingmanager&view=properties', $vName == 'properties' || $vName == 'property');
        \JHtmlSidebar::addEntry('<i class="icon-folder-open"></i> ' . Text::_('COM_BOOKINGMANAGER_COMPLEXES'), 'index.php?option=com_bookingmanager&view=complexes', $vName == 'complexes' || $vName == 'complex');
        \JHtmlSidebar::addEntry('<i class="icon-user"></i> ' . Text::_('COM_BOOKINGMANAGER_SUPPLIERS'), 'index.php?option=com_bookingmanager&view=suppliers', $vName == 'suppliers' || $vName == 'supplier');
        \JHtmlSidebar::addEntry('<i class="icon-tags"></i> ' . Text::_('COM_BOOKINGMANAGER_PROPERTY_RATES'), 'index.php?option=com_bookingmanager&view=propertyrates', $vName == 'propertyrates');
        \JHtmlSidebar::addEntry('<i class="icon-envelope"></i> ' . Text::_('COM_BOOKINGMANAGER_EMAIL_TEMPLATES'), 'index.php?option=com_bookingmanager&view=templates', $vName == 'templates' || $vName == 'template');
        \JHtmlSidebar::addEntry('<i class="icon-cogs"></i> ' . Text::_('COM_BOOKINGMANAGER_DIAGNOSTIC'), 'index.php?option=com_bookingmanager&view=diagnostic', $vName == 'diagnostic');

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
