<?php
// +-----------------------------------------------------------------------+
// | Piwigo - a PHP based picture gallery                                  |
// +-----------------------------------------------------------------------+
// | Copyright(C) 2008-2015 Piwigo Team                  http://piwigo.org |
// | Copyright(C) 2003-2008 PhpWebGallery Team    http://phpwebgallery.net |
// | Copyright(C) 2002-2003 Pierrick LE GALL   http://le-gall.net/pierrick |
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation                                          |
// |                                                                       |
// | This program is distributed in the hope that it will be useful, but   |
// | WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU      |
// | General Public License for more details.                              |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the Free Software           |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, |
// | USA.                                                                  |
// +-----------------------------------------------------------------------+

if( !defined("PHPWG_ROOT_PATH") )
{
  die ("Hacking attempt!");
}

include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');

function ctd_ws_photo_submit($params, &$service)
{
  // register a new contribution
  global $conf, $logger, $user;

  // has the photo already been "contributed"?
  $query = '
SELECT
    *
  FROM '.CTD_CONTRIB_TABLE.'
  WHERE image_idx = '.$params['image_id'].'
    AND demo_url = \''.$conf['ctd_demo_url'].'\'
;';
  $contribs = query2array($query);

  if (count($contribs) > 0)
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Image already contributed');
  }

  $query = '
SELECT
    *
  FROM '.IMAGES_TABLE.'
  WHERE id = '.$params['image_id'].'
;';
  $images = query2array($query);
  $image = $images[0];

  // calls the remote Piwigo
  $server_url = $conf['ctd_demo_url'].'/ws.php';

  $get_data = array(
    'format' => 'json',
    'method' => 'contrib_server.photo.submit',
  );

  $post_data = array(
    'file' => $image['file'],
    'name' => $image['name'],
    'gallery_title' => $conf['gallery_title'],
    'piwigo_url' => get_absolute_root_url(),
    'piwigo_relative_path' => $image['path'],
    'piwigo_image_id' => $params['image_id'],
    'file_content' => base64_encode(file_get_contents($image['path'])),
    'email' => $user['email'],
    );

  if (!fetchRemote($server_url, $result, $get_data, $post_data))
  {
    return new PwgError(500, 'error calling remote Piwigo');
  }

  $data = json_decode($result, true);
  if (!is_array($data))
  {
    return new PwgError(500, 'error parsing reply from remote Piwigo: '.$result);
  }
  
  // check the uuid
  if (!preg_match(CTD_UUID_PATTERN, $data['result']['uuid']))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid uuid');
  }

  single_insert(
    CTD_CONTRIB_TABLE,
    array(
      'image_idx' => $params['image_id'],
      'demo_url' => $conf['ctd_demo_url'],
      'contrib_uuid' => $data['result']['uuid'],
      'state' => 'submitted',
    )
  );

  return array('uuid' => $data['result']['uuid']);
}

function ctd_ws_photo_remove($params, &$service)
{
  // register a new contribution
  global $conf;

  $query = '
SELECT
    *
  FROM '.CTD_CONTRIB_TABLE.'
  WHERE image_idx = '.$params['image_id'].'
    AND demo_url = \''.$conf['ctd_demo_url'].'\'
;';
  $contribs = query2array($query);

  if (count($contribs) == 0)
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'not a contribution');
  }

  $contrib = $contribs[0];

  // calls the remote Piwigo
  $server_url = $conf['ctd_demo_url'].'/ws.php';

  $get_data = array(
    'format' => 'json',
    'method' => 'contrib_server.photo.remove',
  );

  $post_data = array(
    'uuid' => $contrib['contrib_uuid'],
    );

  if (!fetchRemote($server_url, $result, $get_data, $post_data))
  {
    return new PwgError(500, 'error calling remote Piwigo');
  }

  $data = json_decode($result, true);
  if (!is_array($data))
  {
    return new PwgError(500, 'error parsing reply from remote Piwigo: '.$result);
  }
  
  $query = '
DELETE
  FROM '.CTD_CONTRIB_TABLE.'
  WHERE contrib_uuid = \''.$contrib['contrib_uuid'].'\'
;';
  pwg_query($query);

  return true;
}

function ctd_ws_photo_validated($params, &$service)
{
  // register a new contribution
  global $conf;

  // check the uuid
  if (!preg_match(CTD_UUID_PATTERN, $params['uuid']))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid uuid');
  }

  // does the uuid exists?
  $query = '
SELECT
    *
  FROM '.CTD_CONTRIB_TABLE.'
  WHERE contrib_uuid = \''.$params['uuid'].'\'
;';
  $contribs = query2array($query);

  if (count($contribs) == 0)
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'unknow uuid');
  }

  single_update(
    CTD_CONTRIB_TABLE,
    array(
      'state' => 'validated',
    ),
    array(
      'contrib_uuid' => $params['uuid'],
    )
  );

  return true;
}

function ctd_ws_photo_rejected($params, &$service)
{
  // register a new contribution
  global $conf;

  // check the uuid
  if (!preg_match(CTD_UUID_PATTERN, $params['uuid']))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid uuid');
  }

  // does the uuid exists?
  $query = '
SELECT
    *
  FROM '.CTD_CONTRIB_TABLE.'
  WHERE contrib_uuid = \''.$params['uuid'].'\'
;';
  $contribs = query2array($query);

  if (count($contribs) == 0)
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'unknow uuid');
  }

  single_update(
    CTD_CONTRIB_TABLE,
    array(
      'state' => 'rejected',
    ),
    array(
      'contrib_uuid' => $params['uuid'],
    )
  );

  return true;
}
