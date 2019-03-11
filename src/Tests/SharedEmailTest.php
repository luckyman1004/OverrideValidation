<?php

namespace Drupal\sharedemail\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests for the shared email module.
 *
 * @group SharedEmail
 */
class SharedEmailTest extends WebTestBase {


  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['sharedemail'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->user = $this->drupalCreateUser([
      'administer users',
      'administer site configuration',
      'access shared email message',
    ], NULL, FALSE);
  }

  /**
   * Test that a non-duplicate email does not display the warning message.
   */
  public function testNonDuplicateEmail() {

    $this->drupalLogin($this->user);

    $this->config('user.settings')
      ->set('verify_mail', FALSE)
      ->set('register', USER_REGISTER_VISITORS)
      ->save();

    // Set up a user to check for duplicates.
    $duplicate_user = $this->drupalCreateUser();

    $edit = [];
    $name = $this->randomMachineName();
    $edit['name'] = $name;
    $edit['mail'] = $this->randomMachineName() . $duplicate_user->getEmail();
    $edit['pass[pass1]'] = 'Test1Password';
    $edit['pass[pass2]'] = 'Test1Password';

    // Attempt to create a new account using an unique email address.
    $this->drupalPostForm('admin/people/create', $edit, t('Create new account'));

    $this->assertText("Created a new user account for $name. No email has been sent", 'Verifying that standard message is displayed.');
    $config = $this->config('sharedemail.settings');
    $this->assertNoText($config->get('sharedemail_msg'), 'Verifying that a non-duplicate email does not display the warning message.');
  }

  /**
   * Test that a duplicate email is allowed.
   */
  public function testAllowsDuplicateEmail() {

    $this->drupalLogin($this->user);

    $this->config('user.settings')
      ->set('verify_mail', FALSE)
      ->set('register', USER_REGISTER_VISITORS)
      ->save();

    // Set up a user to check for duplicates.
    $duplicate_user = $this->drupalCreateUser();

    $edit = [];
    $name = $this->randomMachineName();
    $edit['name'] = $name;
    $edit['mail'] = $duplicate_user->getEmail();
    $edit['pass[pass1]'] = 'Test1Password';
    $edit['pass[pass2]'] = 'Test1Password';

    // Attempt to create a new account using an existing email address.
    $this->drupalPostForm('admin/people/create', $edit, t('Create new account'));

    $config = $this->config('sharedemail.settings');

    $this->assertText("Created a new user account for $name. No email has been sent", 'Verifying original message is still displayed.');
    $this->assertText($config->get('sharedemail_msg'), 'Verifying that a duplicate email displays the warning message.');
  }

  /**
   * Test allowed duplicate email, but w/o access to the message.
   */
  public function testAllowsDuplicateEmailNoMessage() {

    $this->user = $this->drupalCreateUser([
      'administer users',
      'administer site configuration',
    ], NULL, FALSE);

    $this->drupalLogin($this->user);

    $this->config('user.settings')
      ->set('verify_mail', FALSE)
      ->set('register', USER_REGISTER_VISITORS)
      ->save();

    // Set up a user to check for duplicates.
    $duplicate_user = $this->drupalCreateUser();

    $edit = array();
    $name = $this->randomMachineName();
    $edit['name'] = $name;
    $edit['mail'] = $duplicate_user->getEmail();
    $edit['pass[pass1]'] = 'Test1Password';
    $edit['pass[pass2]'] = 'Test1Password';

    // Attempt to create a new account using an existing email address.
    $this->drupalPostForm('admin/people/create', $edit, t('Create new account'));

    $config = $this->config('sharedemail.settings');

    $this->assertNoText($config->get('sharedemail_msg'), 'Verifying that a non-duplicate email does not display the warning message.');
  }

  /**
   * Test the configuration form.
   */
  public function testConfigForm() {

    $this->drupalLogin($this->user);

    $this->drupalGet('/admin/config/people/shared-email');
    $this->assertResponse(200);

    $config = $this->config('sharedemail.settings');

    $this->assertFieldByName(
      'sharedemail_msg',
      $config->get('sharedemail_msg'),
      'Source text field has the default value'
    );

    // Post the form.
    $this->drupalPostForm('/admin/config/people/shared-email', [
      'sharedemail_msg' => 'Test message',
    ], t('Save configuration'));

    $this->assertText(
      'The configuration options have been saved.',
      'The form was saved correctly.'
    );

    // Test the new values are there.
    $this->drupalGet('/admin/config/people/shared-email');
    $this->assertResponse(200);
    $this->assertFieldByName(
      'sharedemail_msg',
      'Test message',
      'Shared email message is OK.'
    );

  }

}
