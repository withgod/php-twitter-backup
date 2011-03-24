#!/usr/bin/php
<?php

require_once 'Services/Twitter.php';
require_once 'HTTP/OAuth/Consumer.php';

$c = yaml_parse_file(dirname(__FILE__) . '/config.yml');

$t = new Services_Twitter();
$t->setOAuth(
    new HTTP_OAuth_Consumer(
        $c['consumer_key'], $c['consumer_secret'],
        $c['user_token'], $c['user_secret']
    )
);

$target = 0;
if ($argc == 2) {
    $target = $argv[1];
} else {
    $target = $t->account->verify_credentials()->id_str; # self
}

$tweets = array();
$opt = array(
    'count'       => 200,
    'include_rts' => 0,
    'trim_user'   => 0
);
if (preg_match('/^\d+$/', $target)) {
    $opt['id'] = $target;
} else {
    $opt['screen_name'] = $target;
}

for ($i = 0; true; $i++) {
    $opt['page'] = $i + 1;
    try {
    $ret = $t->statuses->user_timeline($opt);
    } catch (Exception $e) {
        var_dump($e);
        break;
    }
    if (count($ret) == 0) {
        break;
    }
    $tweets = array_merge($tweets, $ret);
    sleep(1);
}

print "total: [" . count($tweets) . "]\n";
print "latest opt:[\n";
var_dump($opt);
print "]\n--\n";
print json_encode($tweets);
?>
