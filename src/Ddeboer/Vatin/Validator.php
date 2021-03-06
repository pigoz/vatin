<?php

namespace Ddeboer\Vatin;

use Ddeboer\Vatin\Vies\Client;

/**
 * Validate a VAT identification number (VATIN)
 *
 * @link http://en.wikipedia.org/wiki/VAT_identification_number
 * @link http://sima.cat/nif.php
 * @link https://github.com/jonathanmaron/zf2_proposal/blob/master/library/Zend/Validator/Vatin.php
 */
class Validator
{
    /**
     * Regular expression patterns per country code
     *
     * @var array
     * @link http://ec.europa.eu/taxation_customs/vies/faq.html?locale=lt#item_11
     */
    protected $patterns = array(
        'AT' => 'U[A-Z\d]{8}',
        'BE' => '0\d{9}',
        'BG' => '\d{9,10}',
        'CY' => '\d{8}[A-Z]',
        'CZ' => '\d{8,10}',
        'DE' => '\d{9}',
        'DK' => '(\d{2} ?){3}\d{2}',
        'EE' => '\d{9}',
        'EL' => '\d{9}',
        'ES' => '[A-Z]\d{7}[A-Z]|\d{8}[A-Z]|[A-Z]\d{8}',
        'FI' => '\d{8}',
        'FR' => '([A-Z]{2}|\d{2})\d{9}',
        'GB' => '\d{9}|\d{12}|(GD|HA)\d{3}',
        'HU' => '\d{8}',
        'IT' => '\d{11}',
        'LT' => '(\d{9}|\d{12})',
        'LV' => '\d{11}',
        'MT' => '\d{8}',
        'NL' => '\d{9}B\d{2}',
        'PL' => '\d{10}',
        'PT' => '\d{9}',
        'RO' => '\d{2,10}',
        'SE' => '\d{12}',
        'SI' => '\d{8}',
        'SK' => '\d{10}'
    );

    /**
     * Client for the VIES web service
     *
     * @var Client
     */
    protected $viesClient;

    /**
     * Set VIES client
     *
     * @param Client $viesClient Client for the VIES web service
     */
    public function setViesClient(Client $viesClient)
    {
        $this->viesClient = $viesClient;
    }

    /**
     * Returns true if value is a valid VAT identification number, false
     * otherwise
     *
     * @param string $value          Value
     * @param bool   $checkExistence In addition to checking the VATIN's format
     *                               for validity, also check whether the VATIN
     *                               exists. This requires a call to the VIES
     *                               web service.
     *
     * @return bool
     */
    public function isValid($value, $checkExistence = false)
    {
        if (null === $value || '' === $value) {
            return false;
        }

        $countryCode = substr($value, 0, 2);
        $vatin = substr($value, 2);

        if (false === $this->isValidCountryCode($countryCode)) {
            return false;
        }

        if (0 === preg_match('/^'.$this->patterns[$countryCode].'$/', $vatin)) {
            return false;
        }

        if (true === $checkExistence) {
            if (null === $this->viesClient) {
                throw new \Exception(
                    'For checking VATIN existence, VIES client must be set'
                );
            }
            $result = $this->viesClient->checkVat($countryCode, $vatin);

            return $result->isValid();
        }

        return true;
    }

    /**
     * Returns true if value is valid country code, false otherwise
     *
     * @param string $value Value
     *
     * @return bool
     */
    public function isValidCountryCode($value)
    {
        return isset($this->patterns[$value]);
    }
}