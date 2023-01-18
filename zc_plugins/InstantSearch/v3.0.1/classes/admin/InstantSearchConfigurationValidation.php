<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace Zencart\Plugins\Admin\InstantSearch;

class InstantSearchConfigurationValidation extends \base
{
    /**
     * Array of allowed search fields.
     *
     * @var array
     */
    protected const VALID_SEARCH_FIELDS_DROPDOWN = [
        'category',
        'manufacturer',
        'meta-keywords',
        'model-broad',
        'model-exact',
        'name',
        'name-description',
    ];

    /**
     * Performs a series of checks on the fields list to validate it.
     *
     * @param string $fieldsList
     * @return bool
     */
    public static function validateFieldsList(string $fieldsList): bool
    {
        // Check that the string is in the correct format
        if (preg_match('/^[a-z][a-z,-]*[a-z-]$/', $fieldsList) !== 1) {
            return false;
        }

        $searchFields = explode(',', $fieldsList);

        // Check that there are no duplicates
        if (count(array_unique($searchFields)) < count($searchFields)) {
            return false;
        }

        // Check that there is only one value between name and name-description in the list
        if (in_array('name', $searchFields, true) && in_array('name-description', $searchFields, true)) {
            return false;
        }

        foreach ($searchFields as $searchField) {
            // Check that $searchField is a valid field name
            if (!in_array($searchField, self::VALID_SEARCH_FIELDS_DROPDOWN, true)) {
                return false;
            }
        }

        return true;
    }
}
