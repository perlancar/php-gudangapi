<?php

# Remote sub HTTP client.
#
# This software is copyright (c) 2011 by Steven Haryanto,
# <stevenharyanto@gmail.com>.
#
# This is free software; you can redistribute it and/or modify it under the
# Artistic License 2.0.

# todo:
# - support log viewing
# - support proxy

function call_sub_http($url, $module, $func, $args=array(), $opts=array()) {
  if (!extension_loaded("curl")) die("curl extension required");
  if (!preg_match('/\A\w+((?:::)\w+)*\z/', $module))
    die("Invalid module syntax");
  if (!preg_match('/\A\w+\z/', $func))
    die("Invalid func syntax");

  $params = array();
  if (isset($opts['user']    )) $params['-user']     = $opts['user'];
  if (isset($opts['password'])) $params['-password'] = $opts['password'];
  foreach ($args as $k0 => $v0) {
    if     (!isset($v0))    { $k = "$k0:y"; $v = "~";           }
    elseif (is_array($v0))  { $k = "$k0:p"; $v = serialize($v); }
    else                    { $k = $k0    ; $v = $v0;           }
    $params[$k] = $v;
  }

  $headers = array();
  $headers['Accept'] = 'application/vnd.php.serialized';

  $retries     = isset($opts['retries'])     ? $opts['retries']     : 5;
  $retry_delay = isset($opts['retry_delay']) ? $opts['retry_delay'] : 5;

  $attempt = 0;
  $do_retry = true;
  while (true) {
  #echo "D1\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $cres = curl_exec($ch);
    $cinfo = curl_getinfo($ch);
    if (curl_errno($ch)) {
      $res = array(500, "Network failure: ".curl_errno($ch));
    } elseif ($cinfo['content_type'] != 'application/vnd.php.serialized') {
      echo "D1b (content_type=$cinfo[content_type])\n";
      $res = array($cinfo['http_code'],
                   ($cinfo['http_code'] == 200 ? "OK" :
                    "Error $cinfo[http_code]"),
                   $cres);
      $do_retry = false;
    } else {
      #echo "D1c\n";
      $res = unserialize($cres);
      $do_retry = false;
    }
    curl_close($ch);
    if (!$do_retry) break;
    $attempt++;
    if ($attempt > $retries) break;
    sleep($retry_delay);
  }

  #echo "D2\n";
  return $res;
}

