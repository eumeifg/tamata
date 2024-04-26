<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Model;

use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\StringLength;

/**
 * Description of UserValidationRules
 *
 * @author Rocket Bazaar Core Team
 */
class UserValidationRules
{
    /**
     * Minimum length of admin password
     */
    const MIN_PASSWORD_LENGTH = 7;

    /**
     * Adds validation rule for user first name, last name, username and email
     *
     * @param \Magento\Framework\Validator\DataObject $validator
     * @return \Magento\Framework\Validator\DataObject
     */
    public function addUserInfoRules(\Magento\Framework\Validator\DataObject $validator)
    {
        $userNameNotEmpty = new NotEmpty();
        $userNameNotEmpty->setMessage(__('User Name is a required field.'), \Zend_Validate_NotEmpty::IS_EMPTY);
        $firstNameNotEmpty = new NotEmpty();
        $firstNameNotEmpty->setMessage(__('First Name is a required field.'), \Zend_Validate_NotEmpty::IS_EMPTY);
        $lastNameNotEmpty = new NotEmpty();
        $lastNameNotEmpty->setMessage(__('Last Name is a required field.'), \Zend_Validate_NotEmpty::IS_EMPTY);
        $emailValidity = new EmailAddress();
        $emailValidity->setMessage(__('Please enter a valid email.'), \Zend_Validate_EmailAddress::INVALID);

        /** @var $validator \Magento\Framework\Validator\DataObject */
        $validator->addRule(
            $userNameNotEmpty,
            'username'
        )->addRule(
            $firstNameNotEmpty,
            'firstname'
        )->addRule(
            $lastNameNotEmpty,
            'lastname'
        )->addRule(
            $emailValidity,
            'email'
        );

        return $validator;
    }

    /**
     * Adds validation rule for user password
     *
     * @param \Magento\Framework\Validator\DataObject $validator
     * @return \Magento\Framework\Validator\DataObject
     */
    public function addPasswordRules(\Magento\Framework\Validator\DataObject $validator)
    {
        $passwordNotEmpty = new NotEmpty();
        $passwordNotEmpty->setMessage(__('Password is required field.'), NotEmpty::IS_EMPTY);
        $minPassLength = self::MIN_PASSWORD_LENGTH;
        $passwordLength = new StringLength(['min' => $minPassLength, 'encoding' => 'UTF-8']);
        $passwordLength->setMessage(
            __('Your password must be at least %1 characters.', $minPassLength),
            \Zend_Validate_StringLength::TOO_SHORT
        );
        $passwordChars = new Regex('/[a-z].*\d|\d.*[a-z]/iu');
        $passwordChars->setMessage(
            __('Your password must include both numeric and alphabetic characters.'),
            \Zend_Validate_Regex::NOT_MATCH
        );
        $validator->addRule(
            $passwordNotEmpty,
            'password'
        )->addRule(
            $passwordLength,
            'password'
        )->addRule(
            $passwordChars,
            'password'
        );

        return $validator;
    }

    /**
     * Adds validation rule for user password confirmation
     *
     * @param \Magento\Framework\Validator\DataObject $validator
     * @param string $passwordConfirmation
     * @return \Magento\Framework\Validator\DataObject
     */
    public function addPasswordConfirmationRule(
        \Magento\Framework\Validator\DataObject $validator,
        $passwordConfirmation
    ) {
        $passwordConfirmation = new \Zend_Validate_Identical($passwordConfirmation);
        $passwordConfirmation->setMessage(
            __('Your password confirmation must match your password.'),
            \Zend_Validate_Identical::NOT_SAME
        );
        $validator->addRule($passwordConfirmation, 'password');
        return $validator;
    }
}
