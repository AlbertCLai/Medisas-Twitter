<?php
/*
  Example Command Line Use:
  php <this_file.php> window=1

  @param int window = time in minutes (floats allowed)
*/
  
require_once('Phirehose.php');
require_once('OauthPhirehose.php');
require_once('MedisasConsumer.php');

// Twitter Credentials
// -----------------------------------------------------------------------
// The OAuth credentials you received when registering your app at Twitter
define("TWITTER_CONSUMER_KEY", "");
define("TWITTER_CONSUMER_SECRET", "");

// The OAuth data for the twitter account
define("OAUTH_TOKEN", "");
define("OAUTH_SECRET", "");


// Rolling Window of time
// -----------------------------------------------------------------------
// Parsing input from command line
parse_str(implode('&', array_slice($argv, 1)), $_GET);
if (!empty($argv)) {
  if (!empty($_GET['window']) && is_numeric(($_GET['window']))) {
    $rollingWindow = $_GET['window'];
    $defaultWindow = false;
  } else {
    $rollingWindow = 1;
    $defaultWindow = true;
  }
}

// State rolling window size to be used
echo "\n------------------------------------------------\n";
echo "\nAlbert: Minutes of Rolling Window: " . $rollingWindow . ($defaultWindow ? " (Time not stated. Default of 1 minute used.)\n" : "\n");
echo "\n------------------------------------------------\n";

// Start streaming, consume, & sort
$mc = new MedisasConsumer(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_SAMPLE);
$mc->setTimeWindow($rollingWindow);
$mc->consume();
$mc->sortTweets();

echo "\n------------------------------------------------";
echo "\nAlbert: Task completed.\n";