<?php
namespace RTHolidays\Component\BookingManager\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;
use RTHolidays\Component\BookingManager\Administrator\Helper\BookingmanagerHelper;

class BookingmanagerCountryField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    protected $type = 'BookingmanagerCountry';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $countries = BookingmanagerHelper::getCountries();
        $options   = [];

        if ($countries)
        {
            foreach ($countries as $country)
            {
                $options[] = HTMLHelper::_('select.option', $country['name'], $country['flag'] . ' ' . $country['name']);
            }
        }

        // Merge with parent options
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}