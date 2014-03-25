<?php
/*****************************************************************
Created : 2014/03/25
Author : Mr. Khwanchai Kaewyos (LookHin)
E-mail : khwanchai@gmail.com
Website : www.LookHin.com
Blog : www.unzeen.com
Copyright (C) 2014, www.LookHin.com all rights reserved.
*****************************************************************/

set_time_limit(0);

$strMp4Folder = "./mp4/";

$strYoutubeDownloadUrl = "
http://youtu.be/z-FNiOs_748
https://www.youtube.com/watch?v=gwKBOnMmpvU
https://www.youtube.com/watch?v=dh7GMmRsQvA
";

$arrYoutubeId = explode("\n", trim($strYoutubeDownloadUrl));

foreach ($arrYoutubeId as $key => $value) {
    	
    	// Get Youtube ID
	preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $value, $matches);
	$strYoutubeId = trim($matches[1]);

	print "Download => {$strYoutubeId}\n";

	// Get Title Name
	$json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/videos/{$strYoutubeId}?v=2&alt=json"),true);

	$strTitleName = iconv("UTF-8", "TIS-620", preg_replace("/[\"'\/:|\\\?\$\*%\-\+ ]/", "-", $json['entry']['title']['$t']));

	// Get Mp4 Link
	$strMp4LinkTmp = "";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER,1);
	curl_setopt($ch, CURLOPT_URL,"http://www.youtube.com/watch?v={$strYoutubeId}");
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:19.0) Gecko/20100101 Firefox/19.0");
	curl_setopt($ch, CURLOPT_REFERER, "http://www.youtube.com/watch?v={$strYoutubeId}");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$result=curl_exec ($ch);
	curl_close ($ch);

	//print $result;
	preg_match_all("|url_encoded_fmt_stream_map\"\: \"(.*)\"|U",$result,$out, PREG_PATTERN_ORDER);

	$arrUrl = explode(',',$out[1][0]);

	foreach($arrUrl as $url){
		if(substr_count(urldecode($url), "type=video/mp4") > 0 && substr_count(urldecode($url), "quality=medium") > 0){
			$arrTmp = explode("\\u0026",urldecode($url));

			$strUrl = "";
			$strSig = "";
			foreach($arrTmp as $strTmp){
				if(substr_count(urldecode($strTmp), "url=") > 0){
					$tmp = explode('url=',$strTmp);
					$strUrl = $tmp[1];
				}

				if(substr_count(urldecode($strTmp), "sig=") > 0){
					$tmp = explode('sig=',$strTmp);
					$strSig = $tmp[1];
				}
			}
			
			$strMp4LinkTmp = $strUrl."&signature=".$strSig;
			
			//print $strMp4LinkTmp;
		}
	}

	// Download
	if($strMp4LinkTmp != ""){
		//print $strMp4LinkTmp;
		copy($strMp4LinkTmp,"{$strMp4Folder}{$strTitleName}.mp4");
	}
}


print "\n\nDownload Complete\n\n";

?>
