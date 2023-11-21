<?php
//------------------------------------------------------------------------------------------------------------------------
// SVG tools
//------------------------------------------------------------------------------------------------------------------------
namespace svg_int;
//------------------------------------------------------------------------------------------------------------------------
require_once('.root');

use svg_int\SVG;
//------------------------------------------------------------------------------------------------------------------------
class SVGTools {

	private static $image_mime_types = ['image/png', 'image/jpeg', 'image/gif', 'image/bmp', 'image/tiff'];
	

	public static function svg_from_image(string $src_image_file_path, string $dest_svg_image_path): bool {
		// create .svg file from image (.png, .jpg) file
		if(file_exists($src_image_file_path) 
			&& in_array(mime_content_type($src_image_file_path), static::$image_mime_types)) {
				list($width, $height, $type) = getimagesize($src_image_file_path);
				$svg_body = '<?xml version="1.0" encoding="UTF-8"?>';
				$svg_body .= '<svg width="' . $width . '" height="' . $height . '" version="1.1" viewBox="0 0 ' . $width .' ' . $height . '" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">';
				$svg_body .= '<image x="0.0" y="0.0" width="' . $width . '" height="' . $height . '" preserveAspectRatio="none" xlink:href="data:' . image_type_to_mime_type($type) . ';base64,' . base64_encode(file_get_contents($src_image_file_path)) . '"/>';
				$svg_body .= '</svg>';
				$svg = new SVG($svg_body);
				$svg->save($dest_svg_image_path);
				return true;
		}
		return false;
	}
}
//------------------------------------------------------------------------------------------------------------------------
?>
