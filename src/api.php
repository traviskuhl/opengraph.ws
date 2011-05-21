<?php

error_reporting(E_ALL);
ini_set("display_errors",1);

	// common
	include("common.inc");

	// header
	header("Content-Type: text/javascript");

	// clean
	$clean = p('clean', false);
	$wrap = p('wrap', false);

	// raw url
	$url = p('url'); 
		
		// empty url try to use the referrer
		if ( $url === false AND p('HTTP_REFERER', false, $_SERVER) ) {
			$url = $_SERVER['HTTP_REFERER'];
		}
		
		// still none
		if ( !$url ) {
			error("No URL Provided", 400);
		}
		
		// https
		$_https = ( stripos($url,"https") !== false ? true : false );
	
	// santaize
	$url = str_replace(array("http:/","https:/"), "", $url);
	
	// what to add back
	$url = ($_https ? "https://" : "http://" ) . trim($url,"/");

	// make sure it's really a url
	filter_var($url, FILTER_VALIDATE_URL);

	// make sure it's really a url
	if ( !$url ) { error("Invalid URL", 400); }
	
	// one more trick
	$url = _getFinalUrl($url);
		
	// parse url
	$p = parse($url);
	
	// mongo
	$m = mongo();
	
	// cached 
	$cached = array();
	
	// og 
	$og = $m->sites->findOne(array('_id'=>$p['uid']));

		// no og
		if ( !$og OR ( $og AND (time() - $og['ts'] > (60*60*24)) ) ) {	
				
			// fetch the page to 
			// see what ogg we can get
			$og = OpenGraph::fetch($url);
				
				if ( !$og ) { $og = array(); }
			
			// $d
			$d = (is_object($og) ? iterator_to_array($og) : array());
			
			// set data 
			$data = array(
				'$set' => array(
					'ts' => time(),
					'rid' => $p['rid'],
					'data' => $d
				)
			);
			
			// save
			$m->sites->update(array('_id'=>$p['uid']), $data, array('upsert'=>true));
			
			// reset ogg
			$og = array('data' => $d, 'ts' => time() );
	
		}		

		// cached
		$cached[] = $p['uid'].":".$og['ts'];

	// ogd
	$ogd = $m->sites->findOne(array('_id'=>$p['domain_uid']));
	
		// no og
		if ( !$ogd OR ( $ogd AND (time() - $ogd['ts'] > (60*60*24)) ) ) {	
				
			// fetch the page to 
			// see what ogg we can get
			$ogd = OpenGraph::fetch("http://" . $p['domain_rid']);
						
			// $d
			$d = (is_object($ogd) ? iterator_to_array($ogd) : array());
			
			// set data 
			$data = array(
				'$set' => array(
					'ts' => time(),
					'rid' => $p['domain_rid'],
					'data' => $d
				)
			);
			
			// save
			$m->sites->update(array('_id'=>$p['domain_uid']), $data, array('upsert'=>true));
			
			// reset ogg
			$ogd = array('data' => $d, 'ts' => time());
	
		}			
	
		// cached
		$cached[] = $p['domain_uid'].":".$og['ts'];	
	
	// cache
	header("X-OpenGraphWs-Cache: ".implode(",",$cached));
	
	// expire time
	$exp = $og['ts'] + (60*60*24);
	
	// expire
	header("Expires: ".dt($exp));
	header("Cache-Control:max-age=".(60*60*24));
	header("Last-Modified:".dt($og['ts']));
	
	// resp
	$resp = array( 
		"status" => 1, 
		'page' => array( 
			"uid" => $p['uid'], 
			"resource" => $p['rid'],
			"created" => date('c', $og['ts'])
		), 
		'domain' => array( 
			"uid" => $p['domain_uid'], 
			"resource" => $p['domain_rid'],
			"created" => date('c', $ogd['ts'])			
		) 
	);

	// append our page stuff
	appendOpenGraph($resp['page'], $og['data']);
	appendOpenGraph($resp['domain'], $ogd['data']);
		
	// print our response
	exit(json_encode($resp));

/* dt */
function dt($ts) {
	return date("D, d M Y H:i:s T", $ts);
}

/* appendOpenGraph */
function appendOpenGraph(&$var, $og) {
	global $clean, $wrap;

	// meta
	$var['meta'] = array();

	// toplevel
	$top = array('site_name', 'title', 'description', 'type', 'image', 'url');
	
		// clean
		if ( $clean AND isset($og['description']) ) {
			$og['description'] = preg_replace(array("#(\n|\t)+#","#\s{2,}#"), " ", $og['description']);
		}

	// add them
	foreach ( $top as $name ) {
		if ( isset($og[$name]) ) {
			$var['meta'][$name] = $og[$name];
		}
	}

	// loc
	$loc = array('latitude', 'longitude', 'street-address', 'locality', 'region', 'postal-code', 'country-name');
	$location = array();

	// loop and add 
	foreach ( $loc as $name ) {
		if ( isset($og[$name]) ) {
			$location[$name] = $og[$name];
		}
	}

		// yes
		if ( count($location) > 0 ) {
			$var['location'] = $location;
		}

	// contact
	$con = array('email', 'phone_number', 'fax_number');
	$contact = array();

	// loop and add
	foreach ( $con as $name ) {
		if ( isset($og[$name]) ) {
			$contact[$name] = $og[$name];
		}
	}

		// yes
		if ( count($contact) > 0 ) {
			$var['contact'] = $contact;
		}

	// video
	$vid = array('video', 'video:height', 'video:width', 'video:type');
	$video = array();

	// add them
	foreach ( $vid as $name ) {
		$n = array_pop(explode(":", $name));
		if ( isset($og[$name])) {
			$video[$n] = $og[$name];
		}
	}

		// yes
		if ( count($video) > 0 ) {
			$var['video'] = $video;
		}

	// video
	$aud = array('audio', 'audio:title', 'audio:artist', 'audio:album', 'audio:type');
	$audio = array();

	// add them
	foreach ( $aud as $name ) {
		$n = array_pop(explode(":", $name));
		if ( isset($og->{$name})) {
			$audio[$n] = $og->{$name};
		}
	}

		// yes
		if ( count($audio) > 0 ) {
			$var['audio'] = $audio;
		}

}


/* error */
function error($msg, $code) {
	header("x-OpenGraphWs-Error: $msg", false, $code);	
	exit(json_encode(array("status"=>0, "error"=>$msg, 'code' => $code)));
}

?>