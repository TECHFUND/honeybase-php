<?php namespace App\Util;

class FacebookPage {

  var $id;

	function __construct($_id) {
    $id = $_id;
  }

  public function likes(){
  	//Construct a Facebook URL
  	$json_url ='https://graph.facebook.com/'.$id.'';
  	$json = file_get_contents($json_url);
  	$json_output = json_decode($json);

  	//Extract the likes count from the JSON object
  	if($json_output->likes){
  		return $likes = $json_output->likes;
  	}else{
  		return 0;
  	}
  }
}
