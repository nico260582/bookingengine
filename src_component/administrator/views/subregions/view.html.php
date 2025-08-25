<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class bookingmanagerViewsubregions extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $sidebar;

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        $this->addToolbar();
        $this->sidebar = $this->addSidebar();

        parent::display($tpl);
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

    protected function addSidebar()
    {
        $vName = 'subregions';

        \JHtmlSidebar::addEntry('<i class="icon-calendar"></i> ' . Text::_('COM_BOOKINGMANAGER_BOOKING_REQUESTS'), 'index.php?option=com_bookingmanager&view=bookingrequests', $vName == 'bookingrequests' || $vName == 'bookingrequest');
        \JHtmlSidebar::addEntry('<i class="icon-home"></i> ' . Text::_('COM_BOOKINGMANAGER_PROPERTIES'), 'index.php?option=com_bookingmanager&view=properties', $vName == 'properties' || $vName == 'property');
        \JHtmlSidebar::addEntry('<i class="icon-folder-open"></i> ' . Text::_('COM_BOOKINGMANAGER_COMPLEXES'), 'index.php?option=com_bookingmanager&view=complexes', $vName == 'complexes' || $vName == 'complex');
        \JHtmlSidebar::addEntry('<i class="icon-user"></i> ' . Text::_('COM_BOOKINGMANAGER_SUPPLIERS'), 'index.php?option=com_bookingmanager&view=suppliers', $vName == 'suppliers' || $vName == 'supplier');
        \JHtmlSidebar::addEntry('<i class="icon-tags"></i> ' . Text::_('COM_BOOKINGMANAGER_PROPERTY_RATES'), 'index.php?option=com_bookingmanager&view=propertyrates', $vName == 'propertyrates');
        \JHtmlSidebar::addEntry('<i class="icon-envelope"></i> ' . Text::_('COM_BOOKINGMANAGER_EMAIL_TEMPLATES'), 'index.php?option=com_bookingmanager&view=templates', $vName == 'templates' || $vName == 'template');
        \JHtmlSidebar::addEntry('<i class="icon-cogs"></i> ' . Text::_('COM_BOOKINGMANAGER_DIAGNOSTIC'), 'index.php?option=com_bookingmanager&view=diagnostic', $vName == 'diagnostic');

        return \JHtmlSidebar::render();
    }
}
