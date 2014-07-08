Medisas-Twitter Challenge
=========================

#Description
Use Twitter's sample streaming API to show the top 10 retweeted tweets in a rolling window of time, where the window's start is n minutes ago (where n is defined by the user) and the window's end is the current time.

Output should include the tweet text and number of times retweeted in the current rolling window.

#Requirements

* Unix/Linux Terminal
* PHP 5
* Phirehose Classes (included)
* Twitter Developer API Credentials

#Execution

1) Edit medisas.php and enter in your Twitter Credentials. Save.

2) Command line: $ php medisas.php window=1
(Consumes Twitter Stream API for a 1 minute rolling window)
(Floats [Non-integers ARE allowed])
(If no value or non-numeric is entered then the default window is 1 minute)

#Comments
The Tweet stream moves forward in time so the window of time in 'n' minutes is from the time of execution to 'n' minutes into the future. So a window of 10 minutes means you'll be waiting for ten minutes. There is some on screen output during the the execution duration as an indication that the program is running. The end result displays the top 10 tweets in descending order. The information displayed is the Tweet ID, Tweet Count, and the original Tweet Text.

#How it Works
1) User runs program defining a rolling window time frame
2) A new connection to the Twitter streaming API is made and an end time set based on time window
3) Comsumption of Stream (function enqueueStatus)
3a) Make sure there is a (created_at) value, convert to Unixtime
3b) If time is before end of window, check if a (retweeted_status) object exists. If exists, grab (id_str) and (text) of the original source fron (retweeted_status) object. If not, grab (id_str) and (test) of the current Tweet. 
3c) Check if Tweet ID already exists in (id2tweet) associative array. If so, increment (id2count) by 1. If not, set (id2count) to 1 and set (id2tweet) to (text)
4) Once (created_at) is no longer within the time window, disconnect from Twitter Stream API.
5) Sorts 'id2count' array and outputs the top 10 with the Original Tweet ID, Tweet Count, and Original Tweet Text