<?php

namespace Drupal\sharedemail\Plugin\Validation\Constraint;

use Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldValueValidator;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Validator\Constraint;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SharedEmailUniqueValidator.
 *
 * @package Drupal\sharedemail\Plugin\Validation\Constraint
 */
class SharedEmailUniqueValidator extends UniqueFieldValueValidator implements ContainerInjectionInterface {

  /**
   * Provides config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Returns the current_user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new SharedEmailUniqueValidator.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Defines the configuration object factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Defines an account interface which represents the current user.
   */
  public function __construct(ConfigFactory $config, AccountInterface $current_user) {
    $this->config = $config;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!$item = $items->first()) {
      return;
    }
    if ($this->currentUser->hasPermission('create shared email account')) {
      $allowed = $this->config->getEditable('sharedemail.settings')->get('sharedemail_allowed');
      if (empty($allowed) || stripos($allowed, $item->value) !== FALSE) {
        return;
      }
    }
    parent::validate($items, $constraint);
  }

}
