<?php
    require ('./TwitterAPI.inc.php');
    $appUrl = 'http://'.$_SERVER['HTTP_HOST'].'/twitter/index.php';
    $twitter = new TwitterAPI($appUrl);

    // No oauth_verifier in URL. Do the 3-legged-OAuth-dance with Twitter, baby!
    // Step #0: Authorize flow
    if (!isset($_GET['oauth_verifier'])) {
        $authorizationURL = $twitter->getOAuthAuthorizationURL();
        header('Location: '.$authorizationURL);
        exit;
    }

    // Into the app flow now
    // Step #1: Get access token
    $oauthToken = $_GET['oauth_token'];
    $oauthVerifier = $_GET['oauth_verifier'];
    $status = $twitter->getOAuthAccessToken($oauthToken, $oauthVerifier);

    // Step #2: Echo the OAuth-ed user's screen name and user ID
    echo '<pre>';
    var_dump($twitter->screenName);
    var_dump($twitter->userId);
    echo '</pre>';

    // Step #3: Invoke API
    // Step 3a - Get 5 of @teemus' tweets
    $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
    $timelineJson = $twitter->invokeAPI($url, 'GET', array('screen_name' => 'teemus', 'count' => '5'));
    $timeline = json_decode($timelineJson, true);
    echo '<pre>';
    var_dump($timeline);
    echo '</pre>';
    
    // Step 3b - Get 25 of your own tweets that were retweeted
    $url = 'https://api.twitter.com/1.1/statuses/retweets_of_me.json';
    $retweetsJson = $twitter->invokeAPI($url, 'GET', array('count' => '25'));
    $retweets = json_decode($retweetsJson, true);
    echo '<pre>';
    var_dump($retweets);
    echo '</pre>';

    // Step 3c - Post status update!
    // Uncomment the section below
    /*
    $url = 'https://api.twitter.com/1.1/statuses/update.json';
    $updateJson = $twitter->invokeAPI($url, 'POST', array('status' => 'First programmatic tweet using Twitter API v1.1! #win'));
    $update = json_decode($updateJson, true);
    echo '<pre>';
    var_dump($update);
    echo '</pre>';
    */
