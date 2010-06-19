<?
function gzip_file ($file, $out_file) {
	
	$ok = false;
	
	if ( $fp_out = gzopen( $out_file, 'wb' ) ) {
		
		if ( $fp_in = fopen( $file, 'rb' ) ) {
			
			while ( !feof( $fp_in ) ) {
				gzwrite( $fp_out, fread( $fp_in, 1024*512 ) );
			}
			
			fclose( $fp_in );
			
			$ok = true;
			
		}
		
		gzclose( $fp_out );
		
	}
	
	return $ok;
	
}

function cache_headers ($file) {
	
	// Set the HTTP response headers that will tell the client to cache the resource.
	$file_last_modified = filemtime( $src_uri );
	$max_age = 300 * 24 * 60 * 60; // 300 days
	$expires = $file_last_modified + $max_age;
	$etag = dechex( $file_last_modified );
	$cache_control = 'must-revalidate, proxy-revalidate, max-age=' . $max_age . ', s-maxage=' . $max_age;
	
	// Send headers
	header( 'Last-Modified: ' . date( 'r', $file_last_modified ) );
	header( 'Expires: ' . date( 'r', $expires ) );
	header( 'ETag: ' . $etag );
	header( 'Cache-Control: ' . $cache_control );
	
	// Check if the client should use the cached version.
	if ( function_exists( 'http_match_etag' ) && function_exists( 'http_match_modified' ) ) {
		if ( http_match_etag( $etag ) || http_match_modified( $file_last_modified ) ) {
			header( 'HTTP/1.1 304 Not Modified' );
			exit;
		}
	}
	else {
		error_log( 'The HTTP extensions to PHP does not seem to be installed...' );
	}

}

function show_error ($code, $msg) {
	header( 'HTTP/1.1 ' . $code . ' ' . $msg . '' );
	echo( '<html><body><h1>HTTP ' . $code . ' - ' . $msg . '</h1></body></html>' );
	exit;
}

function debug () {
	echo '<pre>';
	var_dump(func_get_args());
	exit;
}

// Allowed content-types
$content_types = array('js' => 'text/javascript');

////////////////////////////////////////////////////////////

$file = $_GET['file'];
$ext = '';

if (!$file) {
	show_error(403, 'Forbidden');
}

// Get and check file extension
$file_parts = explode('.', $file);
$ext = end($file_parts);
if (!array_key_exists($ext, $content_types)) {
	show_error(403, 'Forbidden');
}

// Verify the requested file is under the doc root for security reasons
$real_file = realpath($file);
$doc_root = realpath('.');
if (strpos($real_file, $doc_root) !== 0) {
	show_error(403, 'Forbidden');
}

if (!file_exists($file)) {
	show_error(404, 'Not Found');
}

// Client supports gzipping?
if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
	
	$gz_file = $file . '.gz';
	
    if (file_exists($gz_file) && (filemtime($file) > filemtime($gz_file))) {
		unlink($gz_file);
    }
	
	if (gzip_file($file, $gz_file)) {
		header('Content-Encoding: gzip');
		$file = $gz_file;
	}
	
}

header('Content-Type: ' . $content_types[$ext]);
header('Content-Length: ' . filesize($file));

readfile($file);
?>
