<?php 

/* 
 * 	Copyright 2011 Owen Mundy 
 *
 *	This file is part of Freedom for Our Files (Facebook Canvas App Starter page).
 *
 *	Freedom for Our Files is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *	
 *	Freedom for Our Files is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *	
 *	You should have received a copy of the GNU General Public License
 *	along with Freedom for Our Files.  If not, see <http://www.gnu.org/licenses/>.
 */ 
  


echo '
	<style> 
		div { font:0.8em/1.3em Arial, sans-serif; } 
		ol { padding-left:20px; } 
		li,textarea { margin:7px 0; } 
		textarea.showdata { width:700px; height:130px } 
	</style>
	
	<div>
		<h3>Freedom for Our Files: Facebook API workshop</h3>';


/* AUTHORIZE APPLICATION
....................................................................................................*/

// require fb config details
if (require_once('inc/fb_config.php'));

// define data permissions
$scope = "email,read_stream";
// auth url
$auth_url = "http://www.facebook.com/dialog/oauth?client_id=" 
	. $fbconfig['appid'] . "&redirect_uri=" . urlencode($fbconfig['canvas_page']) . "&scope=" . $scope; 
// store signed_request 
list($encoded_sig, $payload) = explode('.', $_REQUEST["signed_request"], 2); 
// array is returned with user_id
$data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
// if no user found they are not logged-in or did not give permission
if (empty($data["user_id"])) 
{
	// forward the parent window to the authorization url
	echo("<script> top.location.href='" . $auth_url . "'</script>");
}
// user is logged-in and has given permission, proceed
else if (!empty($data["user_id"])) 
{	
	// create facebook object to query
	require 'src/facebook.php';

	$facebook = new Facebook(array(
	  'appId'  => $fbconfig['appid'],
	  'secret' => $fbconfig['secret'],
	  'cookie' => true,
	));
	
	


/* EXAMPLES
....................................................................................................*/

	echo '<ol>';
	
	// 1. user_id in $data
	echo ('<li>user_id in $data: ' . $data["user_id"]) ."</li>";
	
	
	// 2. user_id again (I think this is a REST API)
	$uid = $facebook->getUser();
	echo "<li>user_id again: $uid</li>";


	// 3. return user from graph API 
	$userInfo = $facebook->api("/$uid");
	echo "<li>return user from graph API<br /><textarea class='showdata'>";
	print_r($userInfo);
	echo "</textarea></li>";	
	
	
	
	
	// define and use an FQL query to get all user data
	$fql = "SELECT 
		
			uid, first_name, middle_name, last_name, name, 
			pic_small, pic_big, pic_square, pic, 
			affiliations, profile_update_time, timezone, religion, birthday, birthday_date, sex, hometown_location, 
			meeting_sex, meeting_for, relationship_status, significant_other_id, political, current_location, activities, interests, is_app_user, 
			music, tv, movies, books, quotes, about_me, 
			hs_info, education_history, work_history, notes_count, wall_count, status, online_presence, locale, proxied_email, profile_url, email_hashes,
			pic_small_with_logo, pic_big_with_logo, pic_square_with_logo, pic_with_logo, 
			allowed_restrictions, verified, profile_blurb, family, username, website, is_blocked, contact_email, email, third_party_id	
		
		FROM user WHERE uid=" . $uid;
	
	// run query
	try{
		$param  =   array(
			'method'    => 'fql.query',
			'query'     => $fql,
			'callback'  => ''
		);
		$fqlResult   =   $facebook->api($param);
	}
	catch(Exception $o){
		d($o);
	}
	
	// 4. fql result as JSON
	echo "<li>fql result as JSON<br /><textarea class='showdata'>";
	echo json_encode($fqlResult);
	echo "</textarea></li>";
	
	
	// 5. fql result as array
	echo "<li>fql result as array:<br /><textarea class='showdata'>";
	if (is_array($fqlResult)){
		print_r($fqlResult);
	} else {
		print $fqlResult;
	}
	echo "</textarea></li>";
	
	
	
	
	echo "</ol>";
} 
else
{
	// catch loops, none so far
	print "FAIL";	
}

?></div>