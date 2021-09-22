<?php
/*
 * Copyright (c) 2019 Ozow (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 * 
 * Released under the GNU General Public License
 */

// @codingStandardsIgnoreFile

namespace Ozow\Ozow\Model;

/**
 * Ozow payment information model
 *
 * Aware of all Ozow payment methods
 * Collects and provides access to Ozow-specific payment data
 * Provides business logic information about payment flow
 */
class Info
{
    /**
     * Apply a filter upon value getting
     *
     * @param string $value
     * @param string $key
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getValue( $value, $key )
    {
        $label       = '';
        $outputValue = implode( ', ', (array) $value );

        return sprintf( '#%s%s', $outputValue, $outputValue == $label ? '' : ': ' . $label );
    }

}
