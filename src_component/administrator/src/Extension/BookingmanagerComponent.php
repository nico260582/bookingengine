<?php
namespace Rtholidays\Component\Bookingmanager\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class BookingmanagerComponent extends MVCComponent
{
    public function __construct($app)
    {
        parent::__construct($app);

        // Load component CSS
        HTMLHelper::_('stylesheet', 'administrator/components/com_bookingmanager/assets/css/bookingmanager.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::_('stylesheet', 'administrator/components/com_bookingmanager/assets/css/custom-booking-styles.css', ['version' => 'auto', 'relative' => true]);
    }
}
