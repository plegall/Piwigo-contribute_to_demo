<?php
/*
Plugin Name: Contribute to Demo
Version: auto
Description: Send photo and contribute to Piwigo demo
Plugin URI: auto
Author: plg
Author URI: http://piwigo.org
*/

// +-----------------------------------------------------------------------+
// | Define plugin constants                                               |
// +-----------------------------------------------------------------------+

global $prefixeTable;

define('CTD_PATH', PHPWG_PLUGINS_PATH.basename(dirname(__FILE__)).'/');
define('CTD_CONTRIB_TABLE', $prefixeTable.'demo_contribs');
define('CTD_UUID_PATTERN', '/^[a-zA-Z0-9]{20,}$/');

include_once(CTD_PATH.'include/functions.inc.php');

// +-----------------------------------------------------------------------+
// | Handlers                                                              |
// +-----------------------------------------------------------------------+

add_event_handler('init', 'ctd_init');
function ctd_init()
{
  global $conf;

  if (!isset($conf['ctd_demo_url']))
  {
    $conf['ctd_demo_url'] = 'http://piwigo.org/demo';
  }
}

add_event_handler('get_admin_plugin_menu_links', 'ctd_admin_menu');
function ctd_admin_menu($menu)
{
  global $page;

  array_push(
    $menu,
    array(
      'NAME' => 'Contribute to Demo',
      'URL'  => get_root_url().'admin.php?page=plugin-contribute_to_demo'
      )
    );

  return $menu;
}

add_event_handler('loading_lang', 'ctd_loading_lang');
function ctd_loading_lang()
{
  load_language('plugin.lang', CTD_PATH);
}

// +-----------------------------------------------------------------------+
// | Edit Photo                                                            |
// +-----------------------------------------------------------------------+

add_event_handler('loc_begin_admin_page', 'ctd_add_link', 60);
function ctd_add_link()
{
  global $conf, $template, $page;

  $template->set_prefilter('picture_modify', 'ctd_add_link_prefilter');

  if (isset($page['page']) and 'photo' == $page['page'])
  {
    $query = '
SELECT
    *
  FROM '.IMAGES_TABLE.'
  WHERE id = '.$_GET['image_id'].'
;';
    $images = query2array($query);
    $image = $images[0];

    $template->assign(
      array(
        'CTD_DEMO_URL' => $conf['ctd_demo_url'],
        'CTD_ID' => $_GET['image_id'],
        'CTD_WIDTH' => $image['width'],
        'CTD_HEIGHT' => $image['height'],
        )
      );

    $query = '
SELECT
    *
  FROM '.CTD_CONTRIB_TABLE.'
  WHERE image_idx = '.$image['id'].'
;';
    $contribs = query2array($query, 'demo_url');
    if (count($contribs) > 0 and isset($contribs[ $conf['ctd_demo_url'] ]))
    {
      $contrib = $contribs[ $conf['ctd_demo_url'] ];

      $template->assign(
        array(
          'CTD_UUID' => $contrib['contrib_uuid'],
          'CTD_STATE' => $contrib['state'],
        )
      );
    }

    $template->set_filename('ctd_picture_modify', realpath(CTD_PATH.'picture_modify.tpl'));
    $template->assign_var_from_handle('CTD_CONTENT', 'ctd_picture_modify');
  }
}

function ctd_add_link_prefilter($content, &$smarty)
{
  $search = '{if !url_is_remote($PATH)}';
  $replacement = '{if !url_is_remote($PATH)}{$CTD_CONTENT}';

  return str_replace($search, $replacement, $content);
}

// +-----------------------------------------------------------------------+
// | API                                                                   |
// +-----------------------------------------------------------------------+

add_event_handler('ws_add_methods', 'ctd_ws_add_methods');
function ctd_ws_add_methods($arr)
{
  global $conf;
  $service = &$arr[0];

  $service->addMethod(
    'contrib.photo.submit',
    'ctd_ws_photo_submit',
    array(
      'image_id' => array('type'=>WS_TYPE_ID),
      ),
    'Submits a photo to Piwigo demo'
    );

  $service->addMethod(
    'contrib.photo.remove',
    'ctd_ws_photo_remove',
    array(
      'image_id' => array('type'=>WS_TYPE_ID),
    ),
    'Removes a photo from Piwigo demo'
  );

  $service->addMethod(
    'contrib.photo.validated',
    'ctd_ws_photo_validated',
    array(
      'uuid' => array(),
      ),
    'Piwigo demo tells us our photo has been validated'
    );

  $service->addMethod(
    'contrib.photo.rejected',
    'ctd_ws_photo_rejected',
    array(
      'uuid' => array(),
      ),
    'Piwigo demo tells us our photo has been rejected'
    );
}
