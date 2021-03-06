<?php

/**
 * TechDivision\Import\Repositories\StoreWebsiteRepository
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Repositories;

use TechDivision\Import\Utils\MemberNames;

/**
 * Repository implementation to load store website data.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import
 * @link      http://www.techdivision.com
 */
class StoreWebsiteRepository extends AbstractRepository
{

    /**
     * The prepared statement to load the store websites.
     *
     * @var \PDOStatement
     */
    protected $storeWebsitesStmt;

    /**
     * Initializes the repository's prepared statements.
     *
     * @return void
     */
    public function init()
    {

        // load the utility class name
        $utilityClassName = $this->getUtilityClassName();

        // initialize the prepared statements
        $this->storeWebsitesStmt = $this->getConnection()->prepare($utilityClassName::STORE_WEBSITES);
    }

    /**
     * Return's an array with the available store websites and their
     * code as keys.
     *
     * @return array The array with all available store websites
     */
    public function findAll()
    {

        // initialize the array with the available store websites
        $storeWebsites = array();

        // execute the prepared statement
        $this->storeWebsitesStmt->execute();

        // fetch the store websites and assemble them as array with the codes as key
        foreach ($this->storeWebsitesStmt->fetchAll() as $storeWebsite) {
            $storeWebsites[$storeWebsite[MemberNames::CODE]] = $storeWebsite;
        }

        // return the array with the store websites
        return $storeWebsites;
    }
}
