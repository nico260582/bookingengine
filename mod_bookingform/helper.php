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

        // Fetch property details including number_of_units and allow_extra_mattress
        $propertyQuery = $db->getQuery(true)
            ->select('p.number_of_units, p.allow_extra_mattress, p.max_guests')
            ->from($db->quoteName('#__bookingmanager_properties', 'p'))
            ->where('p.article_id = ' . (int) $articleId);
        $propertyDetails = $db->setQuery($propertyQuery)->loadObject();
        $numberOfUnits = $propertyDetails ? $propertyDetails->number_of_units : 1;
        $allowExtraMattress = $propertyDetails ? (int)$propertyDetails->allow_extra_mattress : 0;
        $maxGuests = $propertyDetails ? (int)$propertyDetails->max_guests : 1;


        $query->select('s.rules, s.out_of_season_surcharge, s.global_discount, s.show_global_discount_notification')
            ->from($db->quoteName('#__bookingmanager_property_map', 'm'))
            ->join('INNER', $db->quoteName('#__bookingmanager_suppliers', 's') . ' ON m.supplier_id = s.id')
            ->where('m.property_id = ' . (int) $articleId);

        $supplierData = $db->setQuery($query)->loadObject();

        if (!$supplierData) {
            return null;
        }

        $rulesJson = $supplierData->rules;
        $rules = json_decode($rulesJson, true);

        if (isset($rules['seasons']) && is_string($rules['seasons'])) {
            $rules['seasons'] = json_decode($rules['seasons'], true);
        }

        // Get supplier ID for market lookup
        $query->clear()
            ->select('supplier_id')
            ->from($db->quoteName('#__bookingmanager_property_map'))
            ->where('property_id = ' . (int) $articleId);
        $supplierId = $db->setQuery($query)->loadResult();

        // Get all defined markets for the supplier, including currency
        $allMarkets = [];
        if ($supplierId) {
            $query->clear()
                ->select('market_name, currency, currency_symbol')
                ->from('#__bookingmanager_supplier_markets')
                ->where('supplier_id = ' . (int)$supplierId)
                ->where('state = 1');
            $allMarkets = $db->setQuery($query)->loadObjectList('market_name');
        }
        // Always ensure a Global Rate market exists as a fallback
        if (!isset($allMarkets['Global Rate'])) {
             $allMarkets['Global Rate'] = (object)['market_name' => 'Global Rate', 'currency' => 'EUR'];
        }


        // Get all saved rates for this property
        $query->clear()
            ->select('season_name, rates, active_markets')
            ->from($db->quoteName('#__bookingmanager_rates'))
            ->where('property_id = ' . (int) $articleId);
        $ratesList = $db->setQuery($query)->loadObjectList('season_name');

        $ratesBySeason = [];
        $activeMarkets = [];
        foreach ($ratesList as $seasonName => $rateInfo) {
            $decodedRates = !empty($rateInfo->rates) ? json_decode($rateInfo->rates, true) : [];
            if (!is_array($decodedRates)) $decodedRates = [];

            // Inject currency and symbol into each market's rate data
            foreach ($decodedRates as $marketName => &$marketData) {
                $marketData['currency'] = $allMarkets[$marketName]->currency ?? 'EUR';
                $marketData['currency_symbol'] = $allMarkets[$marketName]->currency_symbol ?? '€';
            }

            $ratesBySeason[$seasonName] = $decodedRates;

            // Load active markets from the first available season
            if (empty($activeMarkets) && !empty($rateInfo->active_markets)) {
                $activeMarkets = json_decode($rateInfo->active_markets, true);
                if (!is_array($activeMarkets)) $activeMarkets = [];
            }
        }


        $cleanRules = [
            'number_of_units' => $numberOfUnits,
            'allow_extra_mattress' => $allowExtraMattress,
            'max_guests' => $maxGuests,
            'pricing_model' => $rules['pricing_model'] ?? 'FlatUnitRate',
            'adult_supplement' => (float)($rules['adult_supplement'] ?? 0),
            'child_supplement' => (float)($rules['child_supplement'] ?? 0),
            'extra_mattress_fee' => (float)($rules['extra_mattress_fee'] ?? 0),
            'infant_max_age' => (int)($rules['infant_max_age'] ?? 5),
            'child_max_age' => (int)($rules['child_max_age'] ?? 12),
            'teen_max_age' => (int)($rules['teen_max_age'] ?? 17),
            'free_with_parents_age' => (int)($rules['free_with_parents_age'] ?? 0),
            'seasons' => isset($rules['seasons']) && is_array($rules['seasons']) ? array_values($rules['seasons']) : [],
            'rates' => $ratesBySeason,
            'active_markets' => $activeMarkets,
            'country_discounts' => isset($rules['country_discounts']) && is_array($rules['country_discounts']) ? array_values($rules['country_discounts']) : [],
            'coupon_codes' => isset($rules['coupon_codes']) && is_array($rules['coupon_codes']) ? array_values($rules['coupon_codes']) : [],
            'out_of_season_surcharge' => (float)($supplierData->out_of_season_surcharge ?? 10),
            'global_discount' => (float)($supplierData->global_discount ?? 0),
            'show_global_discount_notification' => (int)($supplierData->show_global_discount_notification ?? 1),
            'alternative_properties' => self::getAlternativeProperties($articleId)
        ];

        if (empty($cleanRules['seasons'])) {
            return null;
        }

        // Validate that there is a valid Global Rate for every season
        foreach ($cleanRules['seasons'] as $season) {
            if (empty($cleanRules['rates'][$season['name']]['Global Rate']['rate']) ||
                !is_numeric($cleanRules['rates'][$season['name']]['Global Rate']['rate']) ||
                $cleanRules['rates'][$season['name']]['Global Rate']['rate'] <= 0) {
                // If the global rate is missing or invalid for any season, we can't proceed.
                return null;
            }
        }

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

        // Find all assigned complexes for the property, ordered by priority
        $query->clear()
            ->select('complex_id')
            ->from($db->quoteName('#__bookingmanager_complex_property_map'))
            ->where('property_id = ' . (int)$propertyId)
            ->order('priority ASC');
        $complexIds = $db->setQuery($query)->loadColumn();

        if (empty($complexIds)) {
            return [];
        }

        $alternatives = [];
        $added_properties = [];

        foreach ($complexIds as $complexId) {
            // Find all other properties in the same complex
            $query->clear()
                ->select([
                    'p.max_guests',
                    'a.title',
                    'a.id AS article_id',
                    'a.images'
                ])
                ->from($db->quoteName('#__bookingmanager_properties', 'p'))
                ->join('INNER', $db->quoteName('#__bookingmanager_complex_property_map', 'map') . ' ON p.id = map.property_id')
                ->join('INNER', $db->quoteName('#__content', 'a') . ' ON p.article_id = a.id')
                ->where('map.complex_id = ' . (int)$complexId)
                ->where('p.article_id != ' . (int)$currentArticleId)
                ->where('a.state = 1')
                ->order('map.priority ASC'); // Order by priority within the complex

            $results = $db->setQuery($query)->loadObjectList();

            // Add the URL and intro image to each alternative
            foreach ($results as $alt) {
                if (in_array($alt->article_id, $added_properties)) {
                    continue;
                }

                $images = json_decode($alt->images);
                $alt->intro_image = $images->image_intro ?? '';
                $alt->url = Route::_('index.php?option=com_content&view=article&id=' . $alt->article_id);
                unset($alt->images);
                $alternatives[] = $alt;
                $added_properties[] = $alt->article_id;
            }
        }

        return $alternatives;
    }
}