<?php

/*
 * You have a $IP and $serverName (similar to $wgWiki) available here
 *
 * user-api-user-only is a new feature as off 0.8.0.5.0. Default is set to yes (old WSForm behaviour)
 * If set to "no", WSForm will create/edit/upload under the current logged in user, instead of the WSForm user
 * described below. If set to yes (leaving it empty also means yes) or no,
 * WSForm still need an API user for other tasks, so the next step is mandatory.
 *
 * api-username is the username you have create for WSForm in your Wiki to handle posts
 * make sure this user has right to create and edit pages as well as upload files.
 * api-password is the username password
 *
 * api-url-overrule can be used if WSForm incorrectly detects the MediaWiki api url
 * This needs to be the full url to the folder where api.php is.
 * api-url-overrule should be left empty unless WSForm cannot find the MediaWiki API
 * Example api-url-overrule : "https://mywebsite"
 *
 * api-cookie-path will default to the tmp folder of the server. With some hosting providers
 * you might need to change that location. Here's where you can change the path. When empty
 * it defaults to /tmp/CURLCOOKIE
 *
 * wgAbsoluteWikiPath is used when in a farm. Needs to be the path to the current wiki
 * There it will look for WSFormSettings.php to read the username and password for that wiki user
 * Example wgAbsoluteWikiPath :  $IP . '/wikis/' . $serverName
 *
 * By default use-smtp is set to no, meaning it will use PHP Mail() functions. When set to yes, make sure
 * you fill in all the other fields needed for SMTP. If you are in a Farm, please use WSFormSettings.php to setup
 * SMTP on a farm.
 *
 * wgScript default to '/index.php' and is used for following a new created page. If your main wiki instalment is
 * somewhere else, then set it here
 *
 * If you want to use the Google Recaptcha, set rc_site_key and rc_secret_key. You receive this from Google when
 * you sign-up
 *
 * sec is work in progress and means security heavy. Will also filter javascript and such.
 *
 */

$config = array(
	"use-api-user-only"   => 'yes',
	"api-username"        => '',
	"api-password"        => '',
	"api-url-overrule"    => '',
	"api-cookie-path"     => '',
	"wgAbsoluteWikiPath"  => '',
	"wgScript"            => '/index.php',
	"rc_site_key"         => '',
	"rc_secret_key"       => '',
	"use-smtp"            => 'no',
	"sec"                 => false,
	"smtp-host"           => '',
	"smtp-authentication" => true,
	"smtp-username"       => '',
	"smtp-password"       => '',
	"smtp-secure"         => 'TLS',
	"smtp-port"           => '587',
);