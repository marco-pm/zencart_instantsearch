<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace Zencart\Plugins\Admin\InstantSearch;

class InstantSearchConfigurationValidation extends \base
{
    /**
     * Array of allowed search fields for the dropdown.
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
     * Array of allowed search fields for the results page.
     *
     * @var array
     */
    protected const VALID_SEARCH_FIELDS_PAGE = [
        'meta-keywords',
        'model-broad',
        'model-exact',
        'name',
        'name-description',
    ];

    /**
     * Validates the fields list for the dropdown.
     *
     * @param string $val The list to validate
     * @return bool True if the list is valid
     */
    public static function validateFieldsListDropdown(string $val): bool
    {
        return self::validateFieldsList($val, self::VALID_SEARCH_FIELDS_DROPDOWN);
    }

    /**
     * Validates the fields list for the results page.
     *
     * @param string $val The list to validate
     * @return bool True if the list is valid
     */
    public static function validateFieldsListPage(string $val): bool
    {
        return self::validateFieldsList($val, self::VALID_SEARCH_FIELDS_PAGE);
    }

    /**
     * Performs a series of checks on the fields list to validate it.
     *
     * @param string $fieldsList The list to validate
     * @param array $validFields Array of allowed values that the list must contain
     * @return bool True if the list is valid
     */
    protected static function validateFieldsList(string $fieldsList, array $validFields): bool {
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
            if (!in_array($searchField, $validFields, true)) {
                return false;
            }
        }

        return true;
    }
}
