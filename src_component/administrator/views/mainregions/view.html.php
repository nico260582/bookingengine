<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class bookingmanagerViewmainregions extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        $this->addToolbar();

        parent::display($tpl);
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
