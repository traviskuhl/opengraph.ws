<?php
	
	// common
	include("common.inc");

	// q
	$q = $o = strtolower(p('q'));
	
		// cleanup
		$q = trim(str_replace(array("http:/","http:/","http://","https://"),"",$q),'/');	
	
		// no http
		if ( $q AND strpos("http", $q) === false ) {
			$q = (strpos("https:", $q) !== false ? "https://" : "http://" ) . $q;
		} 

		// no q
		if ( !$q AND p('HTTP_REFERER', false, $_SERVER, FILTER_VALIDATE_URL) ) {
			$q = $_SERVER['HTTP_REFERER'];
		}

	// make sure it's really a url
	filter_var($q, FILTER_VALIDATE_URL);

	// no q
	if ( !$q ) {
		$q = "http://" . getenv("opengraph_site__host") . "/test";
	} 
	
	// api
	$api = "http://opengraph.ws/api/v1/".$q;
	
?>
<!doctype html> 
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8"> 			
		<?php if ( trim(p('path'),'/') == 'test' ) { ?>
			<title>The Rock (1996)</title>
			<meta property="og:title" content="The Rock" />
			<meta property="og:type" content="movie" />
			<meta property="og:url" content="http://www.imdb.com/title/tt0117500/" />
			<meta property="og:image" content="http://ia.media-imdb.com/images/rock.jpg" />		
			<meta property="og:type" content="actor" />
			<meta property="og:description" content="Sean Connery found fame and fortune as the
			                                         suave, sophisticated British agent, James
			                                         Bond." />
			<meta property="og:site_name" content="IMDb" />	
		
			<!-- location -->
			<meta property="og:latitude" content="37.416343" />
			<meta property="og:longitude" content="-122.153013" />
			<meta property="og:street-address" content="1601 S California Ave" />
			<meta property="og:locality" content="Palo Alto" />
			<meta property="og:region" content="CA" />
			<meta property="og:postal-code" content="94304" />
			<meta property="og:country-name" content="USA" />	
		
			<!-- contact -->
			<meta property="og:email" content="me@example.com" />
			<meta property="og:phone_number" content="650-123-4567" />
			<meta property="og:fax_number" content="+1-415-123-4567" />		
	
			<!-- video -->	
			<meta property="og:video" content="http://example.com/awesome.flv" />
			<meta property="og:video:height" content="640" />
			<meta property="og:video:width" content="385" />
			<meta property="og:video:type" content="application/x-shockwave-flash" />	
	
			<!-- audio -->	
			<meta property="og:audio" content="http://example.com/amazing.mp3" />
			<meta property="og:audio:title" content="Amazing Song" />
			<meta property="og:audio:artist" content="Amazing Band" />
			<meta property="og:audio:album" content="Amazing Album" />
			<meta property="og:audio:type" content="application/mp3" />	
		<?php } else { ?>

			<title>OpenGraph.ws</title>		
			<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?3.2.0/build/cssfonts/fonts-min.css&3.2.0/build/cssreset/reset-min.css&3.2.0/build/cssgrids/grids-min.css">	
			<meta property="og:title" content="OpenGraph.ws" />
			<meta property="og:type" content="website" />
			<meta property="og:url" content="http://opengraph.ws" />
			<meta property="og:description" content="A simple web service to return Open Graph meta data." />
			<meta property="og:site_name" content="OpenGraph.ws" />		
		
		<?php } ?>
		<script type="text/javascript">
			if (location.href.indexOf("#!q=") != -1 ) {			
				window.location.href = "/q/" + location.hash.replace(/\#\!q=/,'');
			} 
		</script>

		<link href='http://fonts.googleapis.com/css?family=Amaranth:regular,bold' rel='stylesheet' type='text/css'>
		<style type="text/css">
			html, body { width: 100%; min-height: 100.1%; height: auto; }			
			body {
				background: #ccc; 
				background: -moz-radial-gradient(center 45deg, circle closest-side, #e0e0e0 0%, #fff 100%);
				background: -webkit-gradient(radial, center center, 0, center center, 650, from(#e0e0e0), to(#fff));
				background: linear-gradient(center 45deg, circle closest-side, #e0e0e0 0%, #fff 100%);
			}
			#doc {
				width: 1024px;
				margin: 0 auto;
				padding-top: 60px;
				min-height: 800px;
			}
			
			h1, b { 
				font-family: 'Amaranth', arial, serif;
				font-size: 40px;
				font-weight: bold;
				color: #3B5998;
				text-shadow: #fff 1px 1px 1px;
				float: left;
			}		
			
			h1 em {
				font-weight: normal;
				font-size: 30px;
				text-shadow: none;
			}				
			
			form {
				width: 765px;
				display: block;
				float: right;
				padding: 13px 0 0 0;
				margin-left: 15px;
			}
			
			form input {
				font-family: 'Amaranth', arial, serif;			
				font-size: 20px;
				padding: 5px 5px 5px 10px;
				background: transparent;
				border: none;
				border-left: solid 5px #ccc;
				width: 95%;			
				color: #666;
			}		
			
			form input:hover,
			form input:focus {
				background: #fff;
				box-shadow: 0 0 5px #888;
				-moz-box-shadow: 0 0 5px #888;
				-webkit-box-shadow: 0 0 5px #888;
			}
			
			form label em,
			form legend, 
			form button { display: none;}
	
			div.resp {
				clear: both;
				margin-top: 60px;
			}
			
			div.resp h2 {
				position: relative;
				bottom: -30px;
				right: 10px;
				font-size: 13px;
				color: #888;
				padding-bottom: 5px;
				float: right;
				text-shadow: #fff 1px 1px;
			}
			div.resp h2 strong {font-weight: bold;}
			div.resp h2 a {color:#888; text-decoration: none;}			
				
			pre {
				clear: both;
				background: #fff;
				padding: 30px;
				color: #555;
				font-size: 16px;
				line-height: 20px;
				text-shadow: #eee 1px 1px 1px;
				min-height: 200px;
				overflow-x: auto;				
				
				box-shadow: inset 0 0 5px #ccc;
				-moz-box-shadow: 0 0 5px #ccc;
				-webkit-box-shadow: 0 0 5px #ccc;
			}
			
			pre.loading {
				background-image: url(http://cdn.kuhl.co/generic/images/loader-gray-spinner.gif);
				background-repeat: no-repeat;
				background-position: center center;
			}
			
			#ft {
				padding: 30px;
				text-align: center;
				font-size: 12px;
				color: #888;
			}
			#ft a { color: #999; }
			
		</style>
		<script src="http://yui.yahooapis.com/3.3.0/build/yui/yui-min.js"></script>
	</head>
	<body>	
		<div id="doc">
			<h1>OpenGraph<em>.ws</em></h1>
			<form method="post">
				<fieldset>			
					<legend>Search for Open Graph Tags</legend>
					<label>
						<em>Website URL:</em><input type="text" name="url" value="<?php echo $q; ?>" onclick="this.select();">
					</label>
				</fieldset>
			</form>
			<div class="resp">
				<h2><strong>API:</strong> <a target="_blank" href="<?php echo $api; ?>"><?php echo $api; ?></a></h2>
				<pre class="loading"></pre>
			</div>
			<div id="ft">
				&copy; 2011 <a href="http://the.kuhl.co">the.kuhl.co</a> -
				developed by <a href="http://twitter.com/traviskuhl">@traviskuhl</a> -
				<a href="http://github.com/traviskuhl/opengraph.ws/">the code</a> -		
				<a href="http://github.com/traviskuhl/opengraph.ws/">api docs</a> -
				<a href="http://ogp.me/">ogp.me</a>
			</div>
		</div>		
		<script type="text/javascript">
		
			// yui stuff
			YUI().use('node','event','io','json',function(Y){
				
				// shortucts
				var $ = Y.one, $j = Y.JSON;
				
				// load
				function Load() {
					var i = $("input"); var p = $('div.resp pre'); p.set('innerHTML','');
					var q = i.get('value');			
					if ( typeof history == 'object' && typeof history.pushState == 'function' ) {
						history.pushState({'q': q}, '', "/q/"+q);
					}		
					else {	
						document.location.href = "#!q=" + id;		
					} 
					Y.all([i,p]).addClass('loading');
					Y.io("/api/v1/" + q + "?clean=1", {
						'method':'GET',
						'on': {
							'complete' : function(id, o) {
								var j = $j.parse(o.responseText);
								p.set('innerHTML', $j.stringify(j, 0, 4));	
								Y.all([i,p]).removeClass('loading');
								$('h2 a').set('innerHTML',"http://opengraph.ws/api/v1/"+j.page.resource).setAttribute('href',"http://opengraph.ws/api/v1/"+j.page.resource);
							}
						}
					});
				}
				
				// catch the submit event
				$('form').on('submit', function(e) { e.halt(); Load(); });
	
				// popstate 			
				if ( typeof history == 'object' && typeof history.pushState == 'function' ) {
					window.onpopstate = function(e){
						if (e.state) { $('input').set('value', e.state.q); Load();  } 
					};
				} 			
			
				// load
				<?php 
					if ( $q ) {
						echo "Load();";
					}
				?> 
			
			});
		</script>
	</body>
</html>