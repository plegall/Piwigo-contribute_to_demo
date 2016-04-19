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

function ctd_ws_photo_submitted($params, &$service)
{
  // register a new contribution
  global $conf;

  // check the uuid
  if (!preg_match(CTD_UUID_PATTERN, $params['uuid']))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid uuid');
  }

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

  single_insert(
    CTD_CONTRIB_TABLE,
    array(
      'image_idx' => $params['image_id'],
      'demo_url' => $conf['ctd_demo_url'],
      'contrib_uuid' => $params['uuid'],
      'state' => 'submitted',
    )
  );

  return true;
}

function ctd_ws_photo_removed($params, &$service)
{
  // register a new contribution
  global $conf;

  // check the uuid
  if (!preg_match(CTD_UUID_PATTERN, $params['uuid']))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid uuid');
  }

  // has the photo already been "contributed"?
  $query = '
SELECT
    *
  FROM '.CTD_CONTRIB_TABLE.'
  WHERE contrib_uuid = \''.$params['uuid'].'\'
;';
  $contribs = query2array($query);

  if (count($contribs) == 0)
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'unknown uuid');
  }

  $contrib = $contribs[0];

  $query = '
DELETE
  FROM '.CTD_CONTRIB_TABLE.'
  WHERE image_idx = '.$contrib['image_idx'].'
;';
  pwg_query($query);

  return true;
}

function ctd_ws_photo_validated($params, &$service)
{
  header("Access-Control-Allow-Origin: *");

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
  header("Access-Control-Allow-Origin: *");

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
