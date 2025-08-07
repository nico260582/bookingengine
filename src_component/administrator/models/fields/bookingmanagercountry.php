<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;

JFormHelper::loadFieldClass('list');

class JFormFieldBookingManagerCountry extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var    string
     */
    protected $type = 'BookingManagerCountry';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        // Load the main component helper
        JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
        
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