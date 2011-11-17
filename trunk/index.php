<?php
/**
 * mblr (モブラー)
 * 携帯でTumblrするためのPHPスクリプトです。
 * ドコモ携帯電話でTumblrのDashboardが見れる「mblr（モブラー）」をPHPで書いた [C!]
 * http://creazy.net/2010/03/mblr_tumblr_dashboard_on_mobile.html
 * ==API==
 * http://www.tumblr.com/api/dashboard?email={email}&password={password}$start={start}&num={num}
 *
 * @author yager <yager at creazy.net>
 * @modifier tknr < http://tknr.com >
 */
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Cache-Control" content="no-cache" />
<style type="text/css">
<!--
body,p,blockquote {
	font-size: small;
}
-->
</style>
<title>mblr</title>
</head>
<body>
<h1>mblr</h1>
<?php
// api
$api_base = 'http://www.tumblr.com/api/dashboard';
$api_dashboard = 'http://www.tumblr.com/api/dashboard';
$api_likes = 'http://www.tumblr.com/api/likes';
$api_like = 'http://www.tumblr.com/api/like';
$api_unlike = 'http://www.tumblr.com/api/unlike';
$api_reblog = 'http://www.tumblr.com/api/reblog';
// opt
$limit_per_page = 10;
$url_site_mobilize = 'http://www.google.co.jp/gwt/x?u=';
$show_avatar = 0; // off : 0 on : 1
$debug = 0; // off : 0 on : 1

// replace caption url
function replace_caption_url($text){
	global $url_site_mobilize;
	preg_match_all('/href="(.*?)"/',$text,$matches, PREG_SET_ORDER);
	foreach ($matches as $url) {
		if(strpos($url[1],'.youtube.com/') !== false){
			$after_url = str_replace('http://www.youtube.com/','http://m.youtube.com/',$url[1]);
			$after_url = str_replace('http://youtube.com/','http://m.youtube.com/',$after_url);
			$text = str_replace('href="'.$url[1],'href="'.$after_url,$text);
		}else if(strpos($url[1],'.tumblr.com/') === false || strpos($url[1],'http://data.tumblr.com/') !== false){
			$encurl = urlencode($url[1]);
			$after_url = str_replace('http%3A%2F%2F',$url_site_mobilize.'http%3A%2F%2F',$encurl);
			$text = str_replace('href="'.$url[1],'href="'.$after_url,$text);
		}else{
			$after_url = str_replace('.tumblr.com/','.tumblr.com/mobile/',$url[1]);
			$text = str_replace('href="'.$url[1],'href="'.$after_url,$text);
		}
	}
	return $text;
}

// replace video source
function replace_video_source($text){
	global $url_site_mobilize;
	if(preg_match('/src="(.*?)"/',$text,$matches)){
		$text = $matches[1];
	}
	if(strpos($text,'.youtube.com/') !== false){
		$youtube_mobile_url = str_replace('http://www.youtube.com/','http://m.youtube.com/',$video_source);
		$youtube_mobile_url = str_replace('http://youtube.com/','http://m.youtube.com/',$youtube_mobile_url);
		$text = '<a href='.$youtube_mobile_url.'>'.$text.'</a>';
	}else{
		$text = '<a href='.$url_site_mobilize.''.urlencode($text).'>'.$text.'</a>';
	}
	return $text;
}

// params
$email    = isset($_POST['email'])    ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$start    = isset($_POST['start'])    ? $_POST['start'] : '0';
$mode = isset($_POST['mode'])    ? $_POST['mode'] : 'dashboard';
$post_id    = isset($_POST['post_id'])    ? $_POST['post_id'] : '';
$reblog_key    = isset($_POST['reblog_key'])    ? $_POST['reblog_key'] : '';
switch($mode){
	case 'likes':{
		$api_base = $api_likes;
		break;
	}
	default:{
		$api_base = $api_dashboard;
		break;
	}
}
if ( preg_match('/^[0-9]+~[0-9]+$/',$start) ) {
	$start_end = explode('~',$start);
	$start     = $start_end[0]-1;
} else {
	$start = 0; $end = $limit_per_page;
}
// like or unlike or reblog
$sub_mode    = isset($_POST['sub_mode'])    ? $_POST['sub_mode'] : '';
$post_id    = isset($_POST['post_id'])    ? $_POST['post_id'] : '';
$reblog_key    = isset($_POST['reblog_key'])    ? $_POST['reblog_key'] : '';
$reblog_comment    = isset($_POST['comment'])    ? $_POST['comment'] : '';

switch($sub_mode){
	case 'like':
		{
			// Kick API
			$api = $api_like.'?email='.urlencode($email).'&password='.urlencode($password).'&post-id='.urlencode($post_id).'&reblog-key='.urlencode($reblog_key);
			$response = file_get_contents($api);
			echo '<br />'.$response.'<br />';
			break;
		}
	case 'unlike':
		{
			// Kick API
			$api = $api_unlike.'?email='.urlencode($email).'&password='.urlencode($password).'&post-id='.urlencode($post_id).'&reblog-key='.urlencode($reblog_key);
			$response = file_get_contents($api);
			echo '<br />'.$response.'<br />';
			break;
		}
	case 'reblog':
		{
			// Kick API
			$api = $api_reblog.'?email='.urlencode($email).'&password='.urlencode($password).'&post-id='.urlencode($post_id).'&reblog-key='.urlencode($reblog_key);
			if(strlen($reblog_comment)!=0){
				$api .= '&comment='.urlencode($reblog_comment);
			}
			$response = file_get_contents($api);
//			echo '<br />'.$response.'<br />';
			echo '<br />reblogged.<br />';
			break;
		}
	default:
		{
			break;
		}
}


if ( empty($email) || empty($password) ) {
	// ID/PWなし->ログイン
	?>
<form action="?<?php echo time(); ?>" method="POST">
email:<input type="text" name="email" /><br />
password:<input type="text" name="password" /><br />
<font color="red">*email,passwordはサーバ及びcookieに保存されません</font><br />
<input type="submit" value="ログイン" />
</form>
<?php
} else {
	// ID/PWあり->ダッシュボード
	echo '<a name="top"></a>';
	echo '<span style="font-size:small;">'.$mode.' '.($start?$start/$limit_per_page+1:1).'page</span>|<a href="#bottom" accesskey="8">bottom</a><hr size="1" />';

	// Kick API
	$api = $api_base.'?email='.urlencode($email).'&password='.urlencode($password).'&num='.$limit_per_page.'&start='.$start;
	$response = file_get_contents($api);
	$xml = simplexml_load_string($response);

	// Format
	$i = 0;
	foreach ( $xml->posts->post as $post ) {
		$out = '';
		{
			$post_attributs_url = $post->attributes()->url;
			$post_attributs_url = str_replace('.tumblr.com/','.tumblr.com/mobile/',$post_attributs_url);
			$avatar_url .= $post->attributes()->{'avatar-url-16'};
			if($show_avatar == 1 && isset($avatar_url)){
				$out .= '<a href="'.$post_attributs_url.'"><img src="'.$avatar_url.'" />'.$post->attributes()->tumblelog.'</a>:<br />';
			}else{
				$out .= '<a href="'.$post_attributs_url.'">'.$post->attributes()->tumblelog.'</a>:<br />';
			}
		}
		switch ($post->attributes()->type) {
			case 'link':
				{
					$feed_item = $post->attributes()->{'feed-item'};
					$out .= '<div style="font-size:small;"><a href="'.$url_site_mobilize.''.urlencode($feed_item).'">'.$feed_item.'</a></div>';
					break;
				}
			case 'quote':
				{
					$post_children_1 = $post->children()->{1};
					$post_children_1 = replace_caption_url($post_children_1);

					$post_children_2 = $post->children()->{2};
					$post_children_2 = replace_caption_url($post_children_2);

					$out .= '<div style="font-size:small;">'.$post_children_1.'</div>'. $post_children_2;
					break;
				}
			case 'photo':
				{
					$thumbnail_count = count($post->{'photo-url'});
					$thumbnail = $post->{'photo-url'}[$thumbnail_count -1];
					$photo_caption  = $post->{'photo-caption'};
					$photo_caption = replace_caption_url($photo_caption);

					$out .= '<a href="'.$post->{'photo-url'}[3].'"><img src="'.$thumbnail.'" border="0" /></a><br />'	. $photo_caption;
					break;
				}
			case 'video':
				{
					$video_caption = $post->{'video-caption'};
					$video_caption = replace_caption_url($video_caption);

					$video_source = $post->{'video-source'};
					$video_source = replace_video_source($video_source);

					$out.= '<div style="font-size:small;">[Video]'.$video_caption.'</div><br />'.$video_source.'<br />';
					break;
				}
			case 'audio':
				{
					$audio_caption = $post->{'audio-caption'};
					$audio_caption = replace_caption_url($audio_caption);

					$audio_source = $post->{'audio-source'};
					$audio_source = '<a href='.$url_site_mobilize.''.urlencode($audio_source).'>'.$audio_source.'</a>';

					$out.= '<div style="font-size:small;">[Audio]'.$audio_caption.'</div><br />'.$audio_source.'<br />';
					break;
				}
			case 'regular':
				{
					$regular_body = $post->{'regular-body'};
					$out .= replace_caption_url($regular_body);
					break;
				}
			default: // regular, quote, conversation
				{
					$out .= '<div style="font-size:small;">'.$post->children()->{0}.'</div>'. $post->children()->{1};
					$out .= replace_caption_url($out);
					break;
				}
		}

		// like or unlike post
		$out .= '<form action="?'.time().'" method="POST">';
		$out .= '<input type="hidden" name="email" value="'.htmlspecialchars($email,ENT_QUOTES).'" />';
		$out .= '<input type="hidden" name="password" value="'.htmlspecialchars($password,ENT_QUOTES).'" />';
		$out .= '<input type="hidden" name="start" value="'.$start.'" />';
		$out .= '<input type="hidden" name="mode" value="'.$mode.'" />';
		$out .= '<input type="hidden" name="post_id" value="'.$post->attributes()->id.'" />';
		$out .= '<input type="hidden" name="reblog_key" value="'.$post->attributes()->{'reblog-key'}.'" />';
		$out .= '<textarea name="comment" cols="26" rows="1" wrap="soft" maxlength="2000"></textarea><br />';
		$out .=  '<input type="submit" name="sub_mode" value="reblog" />';
		switch($mode){
			case "likes":
				{
					$out .=  '<input type="submit" name="sub_mode" value="unlike" />';
					break;
				}
			default:
				{
					$out .=  '<input type="submit" name="sub_mode" value="like" />';
					break;
				}
		}

		$out .= '</form>';

//		$out = str_replace('<p>','<div style="font-size:small;">',$out);
//		$out = str_replace('</p>','</div>',$out);
		$out = str_replace('<p>','<br /><small>',$out);
		$out = str_replace('</p>','</small><br />',$out);
		
		//		$out = str_replace('<blockquote>','<blockquote style="font-size:small;">',$out);
		//remove blockquote
//		$out = str_replace('<blockquote>','<span style="font-size:small;">',$out);
//		$out = str_replace('</blockquote>','</span>',$out);
		$out = str_replace('<blockquote>','<br /><small>',$out);
		$out = str_replace('</blockquote>','</small><br />',$out);
		echo $out."<hr size=\"1\" />\n";
		if ( $i == ($limit_per_page -1) ) {
			break;
		} else {
			$i++;
		}
	}


	?>
<a name="bottom"></a>
<form action="?<?php echo time(); ?>" method="POST" style="text-align: center;">
<input type="hidden" name="email" value="<?php echo htmlspecialchars($email,ENT_QUOTES); ?>" />
<input type="hidden" name="password" value="<?php echo htmlspecialchars($password,ENT_QUOTES); ?>" />
<?php
	if ( $start > 0 )   echo '<input type="submit" name="start" value="'.($start-($limit_per_page -1)).'~'.$start.'" accesskey="4" />';
	echo '<input type="hidden" name="mode" value="'.$mode.'" />';
	echo '<input type="submit" name="submit" value="start" accesskey="0" />';
	if(strcmp($mode,"likes")==0){
		echo '<input type="submit" name="mode" value="dashboard" accesskey="5" />';
	}else{
		echo '<input type="submit" name="mode" value="likes" accesskey="5" />';
	}
	if ( $start < 250 ){ echo '<input type="submit" name="start" value="'.($start+($limit_per_page+1)).'~'.($start+($limit_per_page *2)).'" accesskey="6" />';}
	echo '<a href="#top" accesskey="2">top</a><br />';
}
?></form>
<?php
if($debug == 1){
	echo '<textarea>';
	var_dump($xml);
	echo '</textarea>';
}?>
</body>
</html>