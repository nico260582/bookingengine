<?php
namespace RTHolidays\Component\BookingManager\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

class PropertyTable extends Table
{
	public $id;
	public $article_id;
	public $max_guests;
	public $allow_extra_mattress = 0;
	public $number_of_units = 1;
	public $state;
	public $ordering;

    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_properties', 'id', $db);
    }

    public function check()
    {
        if (empty($this->article_id)) {
            $this->setError(Text::_('COM_BOOKINGMANAGER_ERR_PROPERTY_ARTICLE_REQUIRED'));
            return false;
        }

        if (empty($this->max_guests) || (int)$this->max_guests <= 0) {
            $this->setError(Text::_('COM_BOOKINGMANAGER_ERR_PROPERTY_MAX_GUESTS_INVALID'));
            return false;
        }

        if (empty($this->number_of_units) || (int)$this->number_of_units <= 0) {
            $this->setError('Number of available units must be a positive number.');
            return false;
        }

        return true;
    }
}
