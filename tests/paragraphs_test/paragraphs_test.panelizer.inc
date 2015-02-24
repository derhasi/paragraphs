<?php
/**
 * @file
 * paragraphs_test.panelizer.inc
 */

/**
 * Implements hook_panelizer_defaults().
 */
function paragraphs_test_panelizer_defaults() {
  $export = array();

  $panelizer = new stdClass();
  $panelizer->disabled = FALSE; /* Edit this to true to make a default panelizer disabled initially */
  $panelizer->api_version = 1;
  $panelizer->name = 'paragraphs_item:ptest:default:default';
  $panelizer->title = 'Default';
  $panelizer->panelizer_type = 'paragraphs_item';
  $panelizer->panelizer_key = 'ptest';
  $panelizer->no_blocks = FALSE;
  $panelizer->css_id = '';
  $panelizer->css = '';
  $panelizer->pipeline = 'standard';
  $panelizer->contexts = array();
  $panelizer->relationships = array();
  $panelizer->access = array();
  $panelizer->view_mode = 'default';
  $panelizer->css_class = '';
  $panelizer->title_element = 'H2';
  $panelizer->link_to_entity = TRUE;
  $panelizer->extra = array();
  $display = new panels_display();
  $display->layout = 'flexible';
  $display->layout_settings = array();
  $display->panel_settings = array();
  $display->cache = array();
  $display->title = '';
  $display->uuid = 'a6e355d2-2f62-4351-81bc-b72a8c82b8f6';
  $display->content = array();
  $display->panels = array();
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $panelizer->display = $display;
  $export['paragraphs_item:ptest:default:default'] = $panelizer;

  return $export;
}
