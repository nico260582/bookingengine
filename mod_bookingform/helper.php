<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_bookingform
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

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

        if (isset($rules['seasons']) && is_string($rules['seasons'])) {
            $rules['seasons'] = json_decode($rules['seasons'], true);
        }

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__bookingmanager_rates'))
            ->where('property_id = ' . (int) $articleId);

        $ratesList = $db->setQuery($query)->loadObjectList('season_name');

        $rates = [];
        $rateDetails = [];
        foreach ($ratesList as $seasonName => $rate) {
            $rates[$seasonName] = (float)$rate->base_rate;
            $rateDetails[$seasonName] = [
                'override_admin_commission' => (int)($rate->override_admin_commission ?? 0),
                'admin_commission'          => isset($rate->admin_commission) ? (float)$rate->admin_commission : null,
            ];
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
            'rate_details' => $rateDetails,
            'country_discounts' => isset($rules['country_discounts']) && is_array($rules['country_discounts']) ? array_values($rules['country_discounts']) : [],
            'coupon_codes' => isset($rules['coupon_codes']) && is_array($rules['coupon_codes']) ? array_values($rules['coupon_codes']) : [],
            'alternative_properties' => self::getAlternativeProperties($articleId) // Add alternatives
        ];

        return $cleanRules;
    }

    public static function getAlternativeProperties(int $currentArticleId)
    {
        $db = Factory::getDbo();

        // Find the property ID from the article ID
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__bookingmanager_properties'))
            ->where('article_id = ' . (int)$currentArticleId);
        $propertyId = $db->setQuery($query)->loadResult();

        if (!$propertyId) {
            return [];
        }

        // Find the complex ID from the property ID
        $query->clear()
            ->select('complex_id')
            ->from($db->quoteName('#__bookingmanager_complex_property_map'))
            ->where('property_id = ' . (int)$propertyId);
        $complexId = $db->setQuery($query)->loadResult();

        if (!$complexId) {
            return [];
        }

        // Find all other properties in the same complex
        $query->clear()
            ->select([
                'p.max_guests',
                'a.title',
                'a.id AS article_id'
            ])
            ->from($db->quoteName('#__bookingmanager_properties', 'p'))
            ->join('INNER', $db->quoteName('#__bookingmanager_complex_property_map', 'map') . ' ON p.id = map.property_id')
            ->join('INNER', $db->quoteName('#__content', 'a') . ' ON p.article_id = a.id')
            ->where('map.complex_id = ' . (int)$complexId)
            ->where('p.article_id != ' . (int)$currentArticleId);

        $alternatives = $db->setQuery($query)->loadObjectList();

        // Add the URL to each alternative
        foreach ($alternatives as &$alt) {
            $alt->url = Route::_('index.php?option=com_content&view=article&id=' . $alt->article_id);
        }

        return $alternatives;
    }
}