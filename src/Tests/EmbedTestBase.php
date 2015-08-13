<?php

/**
 * @file
 * Contains \Drupal\embed\Tests\TestBase.
 */

namespace Drupal\embed\Tests;

use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\simpletest\WebTestBase;

/**
 * Base class for all embed tests.
 */
abstract class EmbedTestBase extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['embed', 'editor', 'ckeditor', 'quickedit'];

  /**
   * The test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  protected function setUp() {
    parent::setUp();

    // Create Filtered HTML text format and enable entity_embed filter.
    $format = FilterFormat::create([
      'format' => 'embed_format',
      'name' => 'Embed format',
      'filters' => [
      ],
    ]);
    $format->save();

    $editor_group = [
      'name' => 'Embed',
      'items' => [
      ],
    ];
    $editor = Editor::create([
      'format' => 'embed_format',
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [[$editor_group]],
        ],
      ],
    ]);
    $editor->save();

    // Create a user with required permissions.
    $this->webUser = $this->drupalCreateUser([
      'administer embed buttons',
      'use text format embed_format',
    ]);

    // Log in the user.
    $this->drupallogin($this->webUser);
  }

}
