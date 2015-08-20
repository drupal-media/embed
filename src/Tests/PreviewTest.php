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
    $content = 'Testing preview route!';

    // Ensure the default filter can be previewed by the anonymous user.
    $this->drupalGet('embed/preview/plain_text', array('query' => array('value' => $content)));
    $this->assertResponse(200);
    $this->assertText($content);

    // The anonymous user should not have permission to use embed_test format.
    $this->drupalGet('embed/preview/embed_test', array('query' => array('value' => $content)));
    $this->assertResponse(403);

    // Now login a user that can use the embed_test format.
    $this->drupalLogin($this->webUser);

    $this->drupalGet('embed/preview/plain_text', array('query' => array('value' => $content)));
    $this->assertResponse(200);
    $this->assertText($content);

    $this->drupalGet('embed/preview/embed_test', array('query' => array('value' => $content)));
    $this->assertResponse(200);
    $this->assertText($content);

    // Test preview route with an empty request.
    $this->drupalGet('embed/preview/embed_test');
    $this->assertResponse(404);

    // Test preview route with an invalid text format.
    $this->drupalGet('embed/preview/invalid_test');
    $this->assertResponse(404);
  }

}
