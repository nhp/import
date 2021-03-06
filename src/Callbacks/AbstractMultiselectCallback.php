<?php

/**
 * TechDivision\Import\Callbacks\AbstractMultiselectCallback
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

namespace TechDivision\Import\Callbacks;

use TechDivision\Import\Utils\MemberNames;
use TechDivision\Import\Utils\RegistryKeys;
use TechDivision\Import\Utils\StoreViewCodes;

/**
 * A callback implementation that converts the passed multiselect value.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import
 * @link      http://www.techdivision.com
 */
abstract class AbstractMultiselectCallback extends AbstractCallback
{

    /**
     * Will be invoked by a observer it has been registered for.
     *
     * @param string $attributeCode  The code of the attribute the passed value is for
     * @param mixed  $attributeValue The value to handle
     *
     * @return mixed|null The modified value
     * @see \TechDivision\Import\Callbacks\CallbackInterface::handle()
     */
    public function handle($attributeCode, $attributeValue)
    {

        // explode the multiselect values
        $vals = explode('|', $attributeValue);

        // initialize the array for the mapped values
        $mappedValues = array();

        // convert the option values into option value ID's
        foreach ($vals as $val) {
            // load the ID of the actual store
            $storeId = $this->getRowStoreId(StoreViewCodes::ADMIN);

            // try to load the attribute option value and add the option ID
            if ($eavAttributeOptionValue = $this->getEavAttributeOptionValueByOptionValueAndStoreId($val, $storeId)) {
                $mappedValues[] = $eavAttributeOptionValue[MemberNames::OPTION_ID];
                continue;
            }

            // query whether or not we're in debug mode
            if ($this->isDebugMode()) {
                // log a warning and continue with the next value
                $this->getSystemLogger()->warning(
                    $this->appendExceptionSuffix(
                        sprintf(
                            'Can\'t find multiselect option value "%s" for attribute %s',
                            $val,
                            $attributeCode
                        )
                    )
                );

                // add the missing option value to the registry
                $this->mergeAttributesRecursive(
                    array(
                        RegistryKeys::MISSING_OPTION_VALUES => array(
                            $attributeCode => array(
                                $val => array(
                                    $this->raiseCounter($val),
                                    array($this->getUniqueIdentifier() => true)
                                )
                            )
                        )
                    )
                );

                // continue with the next option value
                continue;
            }

            // throw an exception if the attribute is not available
            throw new \Exception(
                $this->appendExceptionSuffix(
                    sprintf(
                        'Can\'t find multiselect option value "%s" for attribute %s',
                        $val,
                        $attributeCode
                    )
                )
            );
        }

        // return NULL, if NO value can be mapped to an option
        if (sizeof($mappedValues) === 0) {
            return;
        }

        // re-concatenate and return the values
        return implode(',', $mappedValues);
    }

    /**
     * Return's the store ID of the actual row, or of the default store
     * if no store view code is set in the CSV file.
     *
     * @param string|null $default The default store view code to use, if no store view code is set in the CSV file
     *
     * @return integer The ID of the actual store
     * @throws \Exception Is thrown, if the store with the actual code is not available
     */
    protected function getRowStoreId($default = null)
    {
        return $this->getSubject()->getRowStoreId($default);
    }

    /**
     * Return's the attribute option value with the passed value and store ID.
     *
     * @param mixed   $value   The option value
     * @param integer $storeId The ID of the store
     *
     * @return array|boolean The attribute option value instance
     */
    protected function getEavAttributeOptionValueByOptionValueAndStoreId($value, $storeId)
    {
        return $this->getSubject()->getEavAttributeOptionValueByOptionValueAndStoreId($value, $storeId);
    }
}
