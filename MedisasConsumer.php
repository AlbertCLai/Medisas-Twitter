<?php 
require_once('Phirehose.php');
require_once('OauthPhirehose.php');

class MedisasConsumer extends OauthPhirehose {
  // Time in minutes entered by user
  private $timeWindow = 1;

  // Time converted into seconds for unix-time
  private $timeWindow_sec = 60;

  // The last time the top 10 were shown (in unix-time)
  private $lastShownTime = NULL;

  // Pushed & Popped Array of Re-Tweets within Frame of time window
  private $inFrame = array();

  // Associative Array lookup of Tweet ID 2 Tweet Text
  private $id2tweet = array();

  // Associative Array lookup of Tweet ID 2 Tweet Count
  private $id2count = array();

  // Sets user input window duration in mins & secs
  public function setTimeWindow($window) {
    $this->timeWindow = $window;
    $this->timeWindow_sec = intval($window * 60);
  }

  // 'Cleans' $inFrame of Re-Tweet instances outside of window time frame
  public function cleanFrame(){
    // Calculate the oldest Re-tweet allowed
    $startTime = (time() - $this->timeWindow_sec);

    foreach ($this->inFrame as $tweet) {
      // Is Re-tweet instance too old? (Outside window time frame)
      if ($tweet['unixtime'] < $startTime) {
        // Grab the Tweet ID
        $id = $tweet['id'];

        // Decrement that Tweet ID's Count
        $this->id2count[$id]--;

        // If Tweet ID's Count is Zero, let's remove its record from respective 
        // arrays to keep memory usage low
        if ($this->id2count[$id] == 0) {
          unset($this->id2count[$id]); 
          unset($this->id2tweet[$id]); 
        }

        // 'Popping' this record since it's too old
        array_shift($this->inFrame);
      } else {
        // Since record is 'pushed' onto the array in chronological order, the
        // first instance of it being false means the rest are all within time frame
        break;
      }
    }

    return;
  }

  // Sorts and Displays the top 10 Re-Tweets (+ some other info)
  public function sortTweets() {
    // Sorting in descending order by value
    arsort($this->id2count);

    // Extra: Being mindful of system memoery usage
    echo "\n------------------------------------------------";
    echo "\nAlbert: Top 10 Re-Tweets: Window Size: " . $this->timeWindow . " Minute" . ($this->timeWindow > 1 ? "s" : "");
    echo "\n------------------------------------------------"; 
    echo "\nAlbert: Window Time Frame: [" . DATE("H:i:s", $this->lastShownTime)  . " - " .  DATE("H:i:s", ($this->lastShownTime - $this->timeWindow_sec))  . "]";
    echo "\n------------------------------------------------"; 
    echo "\nAlbert: Current Memory Usage: " . (memory_get_usage()/1024/1024) . " MB";
    echo "\n------------------------------------------------"; 
    echo "\nAlbert: Last Shown Time: " . DATE("H:i:s", $this->lastShownTime) . " | Unix-time: " . $this->lastShownTime;
    echo "\n------------------------------------------------"; 

    // Showing top 10 Re-Tweets in current rolling window of time
    $top = 10;
    foreach ($this->id2count as $idkey => $count) {
      echo "\nTweet ID: $idkey | Tweet Count: $count | Tweet Text (below): \n" . $this->id2tweet[$idkey] . "\n";
      echo "\n------------------------------------------------"; 
      $top--;
      
      if ($top <= 0) {
        break;
      }
    }

    return;
  }

  // Performs Consumption of Tweet
  public function enqueueStatus($status) {
    $data = json_decode($status, true);

    if (is_array($data)) {
      // Make sure there is a (created_at) field first
      if (!empty($data['created_at'])) {
        // Convert to Unix time
        $unixtime = strtotime($data['created_at']);

        // Make sure this is Re-Tweet and not an initial Tweet (Incorporating Linda's feedback...)
        if (!empty($data['retweeted_status'])) {
          $id = $data['retweeted_status']['id_str'];
          $text = $data['retweeted_status']['text'];

          // Does this record already exist?
          if (empty($this->id2tweet[$id])) {
            $this->id2tweet[$id] = $text;
            $this->id2count[$id] = 1;
            array_push($this->inFrame, array('id' => $id, 'unixtime' => $unixtime));
          } else {
            $this->id2count[$id]++;
            array_push($this->inFrame, array('id' => $id, 'unixtime' => $unixtime));
          }

          // No need to update the screen for every Tweet instance. (Multiple per second)
          // Screen flies by and is unreadable. Let's just do it once a second.
          // Basically when the unix-time changes (each second)
          $currentTime = time();
          // Only show/update if the current time is not the same as the last time an update was shown
          if($this->lastShownTime != $currentTime){
            // Update with new time
            $this->lastShownTime = $currentTime;

            // Clears screen on Terminal (User screen also needs to be tall enough though...)
            passthru('clear');

            // Clean $inFrame of old Re-Tweet instances and update respective arrays
            $this->cleanFrame();

            // Sort and Display top 10 Re-Tweets
            $this->sortTweets();
          }
        }

      }      
    }

    return;
  }

}