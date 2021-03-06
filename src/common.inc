<?php

date_default_timezone_set("UTC");

function _getFinalUrl($url) {
	foreach ( explode("\r\n\r\n", trim(`curl -IL $url`)) as $msg ) {		
		$h = http_parse_headers($msg);	
		if ( isset($h['Location'])) {
			$url = $h['Location'];
		}
	}
	return $url;
}

function parse($url) {

	// parts
	$parts = parse_url(strtolower($url));
	
	// domain
	$host = explode(".", $parts['host']);	
	
		// domain
		if ( count($host) == 2 ) {
			$parts['host'] = "www." . $parts['host'];
		}
	
	// get hte uul
	$uurl = str_replace(array("http://", "https://"), "", trim(http_build_url("",$parts),"/"));

	// and the id
	$uid = md5($uurl);	
	
	// return
	return array(
		'uid' => $uid, 
		'rid' => $uurl, 
		'domain_rid' => $parts['host'],
		'domain_uid' => md5($parts['host'])
	);
	
}

// mongo
function mongo() {

	$host = getenv("opengraph_site__mongo_host");
	$db = getenv("opengraph_site__mongo_db");
	$user = getenv("opengraph_site__mongo_user");
	$pass = getenv("opengraph_site__mongo_pass");	
	$port = 27017;
	
	// set dbh
	if ( $user != false AND $pass != false ) {
		$sth = new \Mongo("mongodb://{$user}:{$pass}@{$host}:{$port}");		
	}
	else {
		$sth = new \Mongo("mongodb://{$host}:{$port}");
	}

	// sth
	return $sth->{$db};
	
}

function  p($key,$default=false,$array=false,$filter=FILTER_SANITIZE_STRING) {

	// check if key is an array
	if ( is_array($key) ) {

		// alawys 
		$key = $key['key'];

		// check for other stuff
		$default = p('default',false,$key);
		$array = p('array',false,$key);
		$filter = p('filter',false,$key);

	}

	// no array
	if ( $array === false ) {
		$array = $_REQUEST;
	}

	// not an array
	if ( !is_array($array) ){ return false; }

	// check 
	if ( !array_key_exists($key,$array) OR $array[$key] == "" OR $array[$key] == 'false' ) {
		return $default;
	}	

	// if final is an array,
	// weand filter we need to filter each el		
	if ( is_array($array[$key]) ) {

		// filter
		array_walk($array[$key],function($item,$key,$a){
			$item = p($key,$a[1],$a[0]);
		},array($filter,$array[$key]));

	}
	else {
		$array[$key] = filter_var($array[$key],$filter);
	}

	// reutnr
	return $array[$key];

}



/*
  Copyright 2010 Scott MacVicar

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

class OpenGraph implements Iterator
{
  /**
   * There are base schema's based on type, this is just
   * a map so that the schema can be obtained
   *
   */
	public static $TYPES = array(
		'activity' => array('activity', 'sport'),
		'business' => array('bar', 'company', 'cafe', 'hotel', 'restaurant'),
		'group' => array('cause', 'sports_league', 'sports_team'),
		'organization' => array('band', 'government', 'non_profit', 'school', 'university'),
		'person' => array('actor', 'athlete', 'author', 'director', 'musician', 'politician', 'public_figure'),
		'place' => array('city', 'country', 'landmark', 'state_province'),
		'product' => array('album', 'book', 'drink', 'food', 'game', 'movie', 'product', 'song', 'tv_show'),
		'website' => array('blog', 'website'),
	);

  /**
   * Holds all the Open Graph values we've parsed from a page
   *
   */
	private $_values = array();

  /**
   * Fetches a URI and parses it for Open Graph data, returns
   * false on error.
   *
   * @param $URI    URI to page to parse for Open Graph data
   * @return OpenGraph
   */
	static public function fetch($URI) {
		return self::_parse(`curl -sL $URI`, $URI);
	}

  /**
   * Parses HTML and extracts Open Graph data, this assumes
   * the document is at least well formed.
   *
   * @param $HTML    HTML to parse
   * @return OpenGraph
   */
	static private function _parse($HTML, $uri) {	
	
		// parse uri
		$up = parse_url($uri);
	
		$old_libxml_error = libxml_use_internal_errors(true);

		$doc = new DOMDocument();
		$doc->loadHTML($HTML);
		
		libxml_use_internal_errors($old_libxml_error);

		$tags = $doc->getElementsByTagName('meta');

		$page = new self();
		$desc = $img = false; 
	
		if ( $tags ) {
			foreach ($tags AS $tag) {
				if ($tag->hasAttribute('property') &&
				    strpos($tag->getAttribute('property'), 'og:') === 0) {
					$key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
					$page->_values[$key] = $tag->getAttribute('content');
				}
				else if ( $tag->hasAttribute('name') ){
					$n = $tag->getAttribute('name');
					if ( $n == 'description' ) {
						$desc = $tag->getAttribute('content');
					}
				}
			}
		}
		
		$ln = $doc->getElementsByTagName('link');
		
		// links
		if ( $ln ) {
			foreach ($ln as $tag) {
				if ( $tag->hasAttribute('rel') ) {
					$rel = $tag->getAttribute('rel');
					if ( $rel == 'image_src' ) {				
						$img = $tag->getAttribute('href');
					}
					else if ( $rel == 'img_src' ) {				
						$img = $tag->getAttribute('href');
					}
				}
			}
		}
		
		if ( !isset($page->_values['image']) AND $img ) {
			$page->_values['image'] = $img;
		}
		
		if ( !isset($page->_values['description']) AND $desc ) {
			$page->_values['description'] = $desc;
		}
		
		if ( !isset($page->_values['title']) AND $doc->getElementsByTagName('title')->length ) {
			$page->_values['title'] = (string)$doc->getElementsByTagName('title')->item(0)->firstChild->wholeText;
		}	
		
		$imgtags = $doc->getElementsByTagName('img');
		
		$_images = array();
		
		// image tags
		if ($imgtags) {
			foreach ( $imgtags as $tag ) {
				$src = $tag->getAttribute('src');
				if ( $src ) {
					// figure out if it's doesn't have http
					if ( strpos($src,'http') === false ) {
						if ( $src{0} == '/' ) {
							$src = $up['scheme'] ."://" . $up['host'] . $src;
						}
						else {
							$src = trim($uri,'/') . "/" . $src;
						}
					}				
					$uid = md5($src);
					$_images[$uid] = array(
						'href' => $src,
						'width' => (int)$tag->getAttribute('width'),
						'height' => (int)$tag->getAttribute('height'),
						'alt' => $tag->getAttribute('alt'),
						'title' => $tag->getAttribute('title')
					);
				}
			}		
		}
		
		$page->_values['images'] = array_filter(array_values($_images), function($i){ 
			if ( $i['width'] > 20 AND $i['height'] > 20 ) {
				return $i;
			}
			return false;
		}); 

		if (empty($page->_values)) { return false; }
		
		return $page;
	}

  /**
   * Helper method to access attributes directly
   * Example:
   * $graph->title
   *
   * @param $key    Key to fetch from the lookup
   */
	public function __get($key) {
		if (array_key_exists($key, $this->_values)) {
			return $this->_values[$key];
		}
		
		if ($key === 'schema') {
			foreach (self::$TYPES AS $schema => $types) {
				if (array_search($this->_values['type'], $types)) {
					return $schema;
				}
			}
		}
	}

  /**
   * Return all the keys found on the page
   *
   * @return array
   */
	public function keys() {
		return array_keys($this->_values);
	}

  /**
   * Helper method to check an attribute exists
   *
   * @param $key
   */
	public function __isset($key) {
		return array_key_exists($key, $this->_values);
	}

  /**
   * Will return true if the page has location data embedded
   *
   * @return boolean Check if the page has location data
   */
	public function hasLocation() {
		if (array_key_exists('latitude', $this->_values) && array_key_exists('longitude', $this->_values)) {
			return true;
		}
		
		$address_keys = array('street_address', 'locality', 'region', 'postal_code', 'country_name');
		$valid_address = true;
		foreach ($address_keys AS $key) {
			$valid_address = ($valid_address && array_key_exists($key, $this->_values));
		}
		return $valid_address;
	}

  /**
   * Iterator code
   */
	private $_position = 0;
	public function rewind() { reset($this->_values); $this->_position = 0; }
	public function current() { return current($this->_values); }
	public function key() { return key($this->_values); }
	public function next() { next($this->_values); ++$this->_position; }
	public function valid() { return $this->_position < sizeof($this->_values); }
}

?>