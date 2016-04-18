<?php 
require('OAuth2/Client.php');
require('OAuth2/GrantType/IGrantType.php');
require('OAuth2/GrantType/AuthorizationCode.php');

const CLIENT_ID     = '3b9f646909d84583bda2ac8131aafdcd';
const CLIENT_SECRET = '9a39a153dce640daaa3190540863aaa1';

const REDIRECT_URI            = 'http://dnatracks.com/tmp/i/followdiff/followdiff.php';
const AUTHORIZATION_ENDPOINT  = 'https://api.instagram.com/oauth/authorize';
const TOKEN_ENDPOINT          = 'https://api.instagram.com/oauth/access_token';

$client = new OAuth2\Client(CLIENT_ID, CLIENT_SECRET);

session_start();

if(!isset($_SESSION['ACCESS_TOKEN'])) {
  if(!isset($_GET['code'])) {
    $auth_url = $client->getAuthenticationUrl(AUTHORIZATION_ENDPOINT, REDIRECT_URI, array('scope' => 'relationships'));
    header('Location: ' . $auth_url);
  } else {
    $params = array('code' =>$_GET['code'], 'redirect_uri' => REDIRECT_URI);
    $response = $client->getAccessToken(TOKEN_ENDPOINT, 'authorization_code', $params);
    $_SESSION['ACCESS_TOKEN'] = $response['result']['access_token'];
  }
} else {
  $client->setAccessToken($_SESSION['ACCESS_TOKEN']);
}

?>
<!DOCTYPE html>
<html>
<head>

  <script src='https://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js' type='text/javascript' charset='utf-8'></script>
  <script src='http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js'></script>
  <script src='js.js' type='text/javascript' charset='utf-8'></script>
  <link rel='stylesheet' href='http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.1/css/bootstrap.min.css' type='text/css' media='screen'>
  <link rel='stylesheet' href='style.css' type='text/css' media='screen'>

</head>
<body>

	<form id='search'>
          <div class="input-append">
            <input class='search-tag' type='text' tabindex='1' value='' />
            <button class="btn" id="search-button" dir="ltr" tabindex="2" type="submit">
              <i class='icon-search'></i>
    		</button>
    	</div>
    </form>


  <div id='user-list' style="display:none"></div>


  <div class="follower-list-wrap">
    <h1>Followers:</h1>
    <a id="load-more-followers" href="#">Load more followers</a>
    <ol id='follower-list'>
    </ol>
  </div>


  <div class="following-list-wrap">
    <h1>Following:</h1>
    <a id="load-more-following" href="#">Load more following</a>
    <ol id='following-list'></ol>
  </div>

  <div>
    <h1>Difference:</h1>
    <a href="#" class="difference">Show difference</a>
    <ol id='difference-list'>
    </ol>
  </div>


  <div id='user-feed' style="display:none">
<?php
    try {
      // get authenticated user's feed
      $response = $client->fetch('https://api.instagram.com/v1/users/self/feed');
      $result = json_decode(json_encode($response['result']));

      // display images
      $data = $result->data;  
      if (count($data) > 0) {
        echo '<ul>';
        foreach ($data as $item) {
          echo '<li style="display: inline-block; padding: 25px">
            <a href="' . $item->link . '">
          <img src="' . $item->images->thumbnail->url . 
            '" /></a> <br/>';
          echo 'By: <em>' . $item->user->username . 
            '</em> <br/>';
          echo 'Date: ' . date ('d M Y h:i:s', $item->created_time) . 
            '<br/>';
          echo $item->comments->count . ' comment(s). ' . 
            $item->likes->count . ' likes. </li>';
        }
        echo '</ul>';
      }

    } catch (Exception $e) {
      echo 'ERROR: ' . $e->getMessage() . print_r($client);
      exit;
    }
    ?>
  </div>


  <div id='photos-wrap' style="display:none"></div>

</body>
</html>