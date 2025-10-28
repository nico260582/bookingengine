<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

class BookingmanagerViewRegions extends BaseHtmlView
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
        $layout = new FileLayout('joomla.searchtools.default', ['view' => $this]);
        $this->sidebar = $layout->render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_BOOKINGMANAGER_REGIONS'));

        $dropdown = '<button class="btn btn-small dropdown-toggle" data-toggle="dropdown"><span class="icon-plus"></span> ' . Text::_('JTOOLBAR_NEW') . ' <span class="caret"></span></button>';
        $dropdown .= '<ul class="dropdown-menu">';
        $dropdown .= '<li><a href="index.php?option=com_bookingmanager&task=mainregion.add"><span class="icon-plus"></span> ' . Text::_('COM_BOOKINGMANAGER_NEW_MAIN_REGION') . '</a></li>';
        $dropdown .= '<li><a href="index.php?option=com_bookingmanager&task=subregion.add"><span class="icon-plus"></span> ' . Text::_('COM_BOOKINGMANAGER_NEW_SUB_REGION') . '</a></li>';
        $dropdown .= '</ul>';

        $bar = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
        $bar->appendButton('Custom', $dropdown, 'new');

        ToolbarHelper::deleteList(Text::_('JTOOLBAR_DELETE'), 'regions.delete');
        ToolbarHelper::publish('regions.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('regions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
    }
}
