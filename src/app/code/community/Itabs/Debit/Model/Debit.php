<?php
/**
 * This file is part of the Itabs_Debit module.
 *
 * PHP version 5
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category  Itabs
 * @package   Itabs_Debit
 * @author    Rouven Alexander Rieker <rouven.rieker@itabs.de>
 * @copyright 2008-2014 ITABS GmbH (http://www.itabs.de)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @version   1.0.7
 * @link      http://www.magentocommerce.com/magento-connect/debitpayment.html
 */
/**
 * Debit Model
 *
 * @category Itabs
 * @package  Itabs_Debit
 * @author   Rouven Alexander Rieker <rouven.rieker@itabs.de>
 */
class Itabs_Debit_Model_Debit extends Mage_Payment_Model_Method_Abstract
{
    /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'debit';

    /**
     * payment form block
     *
     * @var string MODULE/BLOCKNAME
     */
    protected $_formBlockType = 'debit/form';

    /**
     * payment info block
     *
     * @var string MODULE/BLOCKNAME
     */
    protected $_infoBlockType = 'debit/info';

    /**
     * @var bool Allow capturing for this payment method
     */
    protected $_canCapture = true;

    /**
     * Assigns data to the payment info instance
     *
     * @param  Varien_Object|array $data Payment Data from checkout
     * @return Itabs_Debit_Model_Debit Self.
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();

        // Fetch account holder
        $ccOwner = $data->getDebitCcOwner();
        if (!$ccOwner) {
            $ccOwner = $data->getCcOwner();
        }

        // Fetch the account swift
        $swift = $data->getDebitSwift();
        if ($swift) {
            $swift = $info->encrypt($swift);
        }

        // Fetch the account iban
        $iban = $data->getDebitIban();
        if ($iban) {
            $iban = $info->encrypt($iban);
        }

        // Set account data in payment info model
        $info->setCcOwner($ccOwner)                                      // Kontoinhaber
            ->setDebitSwift($swift)                                     // SWIFT Code
            ->setDebitIban($iban)                                       // IBAN
            ->setDebitCompany($data->getDebitCompany())                 // Company
            ->setDebitStreet($data->getDebitStreet())                   // Street
            ->setDebitCity($data->getDebitCity())                       // City
            ->setDebitCountry($data->getDebitCountry())                 // Country
            ->setDebitEmail($data->getDebitEmail())                     // Email
            ->setDebitType(Itabs_Debit_Helper_Data::DEBIT_TYPE_SEPA);   // Debit Type

        return $this;
    }

    /**
     * Returns the custom text for this payment method
     *
     * @return string Custom text
     */
    public function getCustomText()
    {
        return $this->getConfigData('customtext');
    }

    /**
     * Returns the account name from the payment info instance
     *
     * @return string Name
     */
    public function getAccountName()
    {
        $info = $this->getInfoInstance();

        return $info->getCcOwner();
    }

    /**
     * Returns the account swift code from the payment info instance
     *
     * @return string SWIFT
     */
    public function getAccountSwift()
    {
        $info = $this->getInfoInstance();
        $data = $info->decrypt($info->getDebitSwift());

        return $data;
    }

    /**
     * Returns the account iban from the payment info instance
     *
     * @return string IBAN
     */
    public function getAccountIban()
    {
        $info = $this->getInfoInstance();
        $data = $info->decrypt($info->getDebitIban());

        return $data;
    }

    /**
     * Returns the account company for the payment info instance
     *
     * @return string Company
     */
    public function getAccountCompany()
    {
        return $this->getInfoInstance()->getDebitCompany();
    }

    /**
     * Returns the account street for the payment info instance
     *
     * @return string Street
     */
    public function getAccountStreet()
    {
        return $this->getInfoInstance()->getDebitStreet();
    }

    /**
     * Returns the account city for the payment info instance
     *
     * @return string City
     */
    public function getAccountCity()
    {
        return $this->getInfoInstance()->getDebitCity();
    }

    /**
     * Retrieve the account country for the payment info instance
     *
     * @return string Country
     */
    public function getAccountCountry()
    {
        $country = $this->getInfoInstance()->getDebitCountry();
        if ($country != '') {
            return Mage::getModel('directory/country')->setId($country)->getName();
        }

        return '';
    }

    /**
     * Retrieve the account country id for the payment info instance
     *
     * @return string Country
     */
    public function getAccountCountryId()
    {
        return $this->getInfoInstance()->getDebitCountry();
    }

    /**
     * Returns the account street for the payment info instance
     *
     * @return string Email
     */
    public function getAccountEmail()
    {
        return $this->getInfoInstance()->getDebitEmail();
    }

    /**
     * Returns the encrypted data for mail
     *
     * @param  string $data Data to crypt
     * @return string Crypted data
     */
    public function maskString($data)
    {
        $crypt = str_repeat('*', strlen($data)-3) . substr($data, -3);

        return $crypt;
    }
}
