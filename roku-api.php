<?php

//calculate how long we want to cache the data
$seconds_of_caching = 60 * 60 * 2; // 2 hours
header('Content-Type: application/json');
//cached headers for ajax calls
header("Cache-Control: private, max-age=$seconds_of_caching");
header("Expires: " . gmdate('r', time() + $seconds_of_caching));

$con = mysqli_connect("host", "username", "password", "dbname");
// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

mysqli_query($con, 'SET CHARACTER SET utf8'); //a single quote could break the json

include("predis.php");  //php version 5.3+
$redis = new Predis\Client('');
// require_once("predis5.2.php");   //use this if you are using php version 5.2 or lower
// $redis = new Predis_Client();

$redis_key = md5('someUniqueRandomString');



if ($redis->exists($redis_key)) {
    //if it exists display the cache to the user
    $cache = $redis->get($redis_key);
    echo $cache;
    mysqli_close($con);
    exit;
    
} else {
    //did not find the cache in redis, lets fetch the catch
    
    
    
    
    $rs = array();  
    
    /*your query should be whatever images you want to pull in for the screen saver. 
       This query specifically looks for posts that have over 800 likes on Facebook
    */
    $result = mysqli_query($con, "SELECT ID,post_title,post_content FROM wp_jthmm2_posts,wp_jthmm2_postmeta WHERE post_type = 'post' AND post_status = 'publish' and  wp_jthmm2_posts.ID=wp_jthmm2_postmeta.post_id and wp_jthmm2_postmeta.meta_key ='_mn_fb_likes' and wp_jthmm2_postmeta.meta_value > 800 order by rand() LIMIT 40 ");
    
    
    while ($row = mysqli_fetch_assoc($result)) {
        $tmp     = array();
        $content = $row['post_content'];
        
        $img  = get_first_img($content);
        //change the URL of the image to a relative path in your OS
        $file = str_replace('http://cutecaptions.com', '/home/username/cutecaptions.com', $img);
        //get the dimensions of the image
        list($width, $height, $type, $attr) = getimagesize($file);
        
        if ($height > 680) {
            //too tall
            coninue;
        } else if ($width < 350) {
            //too small
            coninue;
        } else {
            $tmp['url']    = $img;
            $tmp['title']  = $row['post_title'];
            $tmp['width']  = $width;
            $tmp['height'] = $height;
            
            array_push($rs, $tmp);
        }
        
        
        
    }

    $encodedJson = json_encode($rs);   
    echo $encodedJson;   
    $redis->setex($redis_key, $seconds_of_caching, $encodedJson); //this caches it!!
    mysqli_close($con);

}




function get_first_img($content)
{
    
    $first_img   = '';
    
    $content = str_replace("<!--more  -->", "", $content);
    $content = str_replace("><img", "> <img", $content);
    
    $output    = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
    $first_img = $matches[1][0];
    
    if (empty($first_img)) { //if we do not find an image try to find one via string split
        $split     = split("<img src=", $content);
        //echo $split[1];
        $first_img = split(" alt=", $split[1]);
        $first_img = $first_img[0];
        return $first_img;
    }
    return $first_img;
}
