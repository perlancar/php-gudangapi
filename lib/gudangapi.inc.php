<?php

# The official GudangAPI client. See http://www.gudangapi.com/ for more
# details.
#
# This software is copyright (c) 2011 by Steven Haryanto,
# <stevenharyanto@gmail.com>.
#
# This is free software; you can redistribute it and/or modify it under the
# Artistic License 2.0.

require_once "phi_access_http_client.inc.php";

$GA_CLIENT_VERSION = "0.02";

function call_ga_api($module, $func, $args=array(), $opts=array()) {
  #echo "D:module=$module\n";
  if (!preg_match("#\A[\w-]+((?:::|/)[\w-]+)*\z#", $module))
    die("Invalid module syntax");
  $module = preg_replace('!/!', '::', $module);
  $module = preg_replace('/-/', '_', $module);
  if (!preg_match('/\A[\w-]+\z/', $func))
    die("Invalid func syntax");
  $func   = preg_replace('/-/', '_', $func);

  $host  = "api.gudangapi.com";
  $proto = isset($opts['https']) && $opts['https'] ? "https" : "http";
  $url   = "$proto://$host/v1/$module/$func";

  #echo "D:url=$url\n";

  $copts = array();

  return phi_http_request("call", $url, array("args"=>$args), $copts);
}
