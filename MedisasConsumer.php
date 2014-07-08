<?php 
require_once('Phirehose.php');
require_once('OauthPhirehose.php');

class MedisasConsumer extends OauthPhirehose {
  private $timeWindow = 1;
  private $startTime;
  private $stopTime;
  private $id2tweet = array();
  private $id2count = array();

  public function setTimeWindow($window) {
    $this->timeWindow = $window;
    $this->startTime = time();
    $this->stopTime = $this->startTime + ($window * 60);
  }

  public function sortTweets() {
    // Sorting in descending order by value
    arsort($this->id2count);

    // Extra: Being mindful of system memoery usage
    echo "\n------------------------------------------------";
    echo "\nAlbert: Memory Usage: " . (memory_get_usage()/1024/1024) . " MB";
    echo "\n------------------------------------------------"; 
    echo "\nAlbert: Top 10 Tweets: ";
    echo "\n------------------------------------------------"; 

    // Showing top 10 Retweets in current rolling window of time
    $top = 10;
    foreach ($this->id2count as $idkey => $count) {
      echo "\nTweet ID: $idkey | Tweet Count: $count | Tweet Text (below): \n" . $this->id2tweet[$idkey] . "\n";
      echo "\n------------------------------------------------"; 
      $top--;
      
      if ($top <= 0) {
        break;
      }
    }    
  }

  public function enqueueStatus($status) {
    $data = json_decode($status, true);

    if (is_array($data)) {
      // Make sure there is a (created_at) field first
      if (!empty($data['created_at'])) {
        // Convert to Unix time
        $unixtime = strtotime($data['created_at']);

        // Is this tweet within the time window range?
        if ($unixtime < $this->stopTime) {         
          // Check if this is a retweet, if so, grab original source
          if (!empty($data['retweeted_status'])) {
            $id = $data['retweeted_status']['id_str'];
            $text = $data['retweeted_status']['text'];
          } else {
            // Grab the current ID and Text
            $id = $data['id_str'];
            $text = $data['text'];
          }

          // Does this record already exist?
          if (empty($this->id2tweet[$id])) {
            $this->id2tweet[$id] = $text;
            $this->id2count[$id] = 1;
          } else {
            $this->id2count[$id]++;
            echo "\nAlbert: Retweet within time window found for [" . $id . "] ... count ... [" . $this->id2count[$id] . "].";
          }

        } else {
          echo "\n------------------------------------------------\n";

          // Getting tweets beyond time window range, let's disconnect from stream.
          $this->disconnect();
        }

      }      
    }
  }

}