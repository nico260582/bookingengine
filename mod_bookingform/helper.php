<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_bookingform
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class ModBookingFormHelper
{
    public static function getPricingDataForArticle(int $articleId)
    {
        if (!$articleId) {
            return null;
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('s.rules')
            ->from($db->quoteName('#__bookingmanager_property_map', 'm'))
            ->join('INNER', $db->quoteName('#__bookingmanager_suppliers', 's') . ' ON m.supplier_id = s.id')
            ->where('m.property_id = ' . (int) $articleId);

        $rulesJson = $db->setQuery($query)->loadResult();

        if (!$rulesJson) {
            return null; 
        }

        $rules = json_decode($rulesJson, true);

        $query->clear()
            ->select(['season_name', 'base_rate'])
            ->from($db->quoteName('#__bookingmanager_rates'))
            ->where('property_id = ' . (int) $articleId);

        $ratesList = $db->setQuery($query)->loadObjectList('season_name');

        $rates = [];
        foreach ($ratesList as $seasonName => $rate) {
            $rates[$seasonName] = (float)$rate->base_rate;
        }

        $cleanRules = [
            'pricing_model' => $rules['pricing_model'] ?? 'FlatUnitRate',
            'adult_supplement' => (float)($rules['adult_supplement'] ?? 0),
            'child_supplement' => (float)($rules['child_supplement'] ?? 0),
            'extra_mattress_fee' => (float)($rules['extra_mattress_fee'] ?? 0),
            'infant_max_age' => (int)($rules['infant_max_age'] ?? 5),
            'child_max_age' => (int)($rules['child_max_age'] ?? 12),
            'teen_max_age' => (int)($rules['teen_max_age'] ?? 17),
            'free_with_parents_age' => (int)($rules['free_with_parents_age'] ?? 0),
            'seasons' => isset($rules['seasons']) && is_array($rules['seasons']) ? array_values($rules['seasons']) : [],
            'rates' => $rates,
            'country_discounts' => isset($rules['country_discounts']) && is_array($rules['country_discounts']) ? array_values($rules['country_discounts']) : []
        ];

        return $cleanRules;
    }
}