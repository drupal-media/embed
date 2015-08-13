<?php

/**
 * @file
 * Contains \Drupal\embed\Tests\PreviewTest.
 */

namespace Drupal\embed\Tests;

/**
 * Tests the preview controller and route.
 *
 * @group embed
 */
class PreviewTest extends EmbedTestBase {

  /**
   * Tests the route used for generating preview of embedding entities.
   */
  public function testPreviewRoute() {
    // Test preview route.
    $content = 'Testing preview route';
    $this->drupalGet('embed/preview/embed_format', array('query' => array('value' => $content)));
    $this->assertResponse(200);
    $this->assertText($content);

    // Test preview route with an empty request.
    $this->drupalGet('embed/preview/embed_format');
    $this->assertResponse(404);

    // Test preview route with an invalid text format.
    $this->drupalGet('embed/preview/invalid_format');
    $this->assertResponse(404);

    // Log out the user to test access to the filter itself is checked.
    $this->drupalLogout();

    $this->drupalGet('embed/preview/embed_format', array('query' => array('value' => $content)));
    $this->assertResponse(403);
  }

}
