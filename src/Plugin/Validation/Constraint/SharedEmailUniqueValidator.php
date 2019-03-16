<?php

namespace Drupal\sharedemail\Plugin\Validation\Constraint;

use Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldValueValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Class SharedEmailUniqueValidator.
 *
 * @package Drupal\sharedemail\Plugin\Validation\Constraint
 */
class SharedEmailUniqueValidator extends UniqueFieldValueValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!$item = $items->first()) {
      return;
    }
    $account = \Drupal::currentUser()->getAccount();
    if ($account->hasPermission('create shared email account')) {
      return;
    }
    parent::validate($items, $constraint);
  }

}
