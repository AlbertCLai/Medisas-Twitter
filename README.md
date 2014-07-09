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
* Tall vertical screen space would be most helpful for visualization of results

#Execution

1) Edit medisas.php and enter in your Twitter Credentials. Save.

2) Command line: $ php medisas.php window=1

(Consumes Twitter Stream API for a 1 minute rolling window)

(Floats [Non-integers ARE allowed])

(If no value or non-numeric is entered then the default window is 1 minute)

#Comments
The Tweet stream moves forward in time so the window of time is 'n' minutes into the past from the present. If the runtime has not been 'n' minutes yet then the window of time is from the present to the time of execution. So a window of 10 minutes and a runtime of 5 minutes means the time window is only 5 minutes. If the runtime were 15 minutes then the time window is from the present to 10 minutes into the past or ... 5 minutes after initial execution to the present. It is helpful to have a taller vertical screen to display the top 10 Re-Tweets as it appears not all Tweets are only a few lines.

The top of resulting output shows other helpful information for reference including memory usage.

#How it Works
1) User runs program defining a rolling window time frame

2) A new connection to the Twitter streaming API is made and the time window duration is set

3) Consumption of Stream (function enqueueStatus)

3a) Make sure there is a (created_at) value, convert to Unixtime

3b) Make sure this is a Re-Tweet and not an initial Tweet

3c) Grab (id_str) and (text) of the original source from (retweeted_status) object. 

3d) Check if Tweet ID already exists in (id2tweet) associative array. If so, increment (id2count) by 1. If not, set (id2count) to 1 and set (id2tweet) to (text). 'Push' Re-Tweet instance record onto (inFrame).

4) Check to see if the current time and the last time an update was displayed is different. (This is because there are multiple Tweet instances consumed per second, so let's just update the screen once per second).

4a) Clean (inFrame) of older Re-Tweet instances that are no longer within the scope of the rolling window time frame. Those that are 'cleaned', decrement the respective Tweet count.

4b) If a Tweet count is zero, clean/remove/trim the other respective arrays of this Tweet to keep memory usage low.

4c) 'Pop' (clean) the most recent 'old' Re-Tweet instance. If at step (4) results in false, break, because all records in (inFrame) are within time frame since they were 'pushed' chronologically.

5) Sorts 'id2count' array and outputs the top 10 Re-Tweets with the Original Tweet ID, Re-Tweet Count, and Original Tweet Text