<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class BookingmanagerViewMainregion extends BaseHtmlView
{
    protected $form;
    protected $item;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $isNew = ($this->item->id == 0);
        ToolbarHelper::title($isNew ? Text::_('COM_BOOKINGMANAGER_MAIN_REGION_NEW') : Text::_('COM_BOOKINGMANAGER_MAIN_REGION_EDIT'));

        ToolbarHelper::apply('mainregion.apply');
        ToolbarHelper::save('mainregion.save');
        ToolbarHelper::cancel('mainregion.cancel');
    }
}
