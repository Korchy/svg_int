<?php
//------------------------------------------------------------------------------------------------------------------------
// SVG document. This class works with the main <svg></svg> node as main data.
//------------------------------------------------------------------------------------------------------------------------
namespace svg_int;
//------------------------------------------------------------------------------------------------------------------------
require_once('.root');

use svg_int\SVGAttribute;
use svg_int\SVGNodeFactory;
//------------------------------------------------------------------------------------------------------------------------
class SVG {
	
	private $document = NULL;	// DomDocument
	private $path = NULL;	// Path to source file

	public function __construct(string $data=NULL) {
		// Constructor
		$this->document = new \DOMDocument;
		$this->document->formatOutput = true;
		$this->document->preserveWhiteSpace = false;
		if($data) {
			if(substr($data, 0, strlen('<?xml')) === '<?xml') {
				// $data is and xml content
				$this->document->loadXML($data);
			} else {
				// $data is the file path
				if(file_exists($data) && is_file($data)) {
					$this->document->load($data);
					$this->path = $data;
				}
			}
		} else $this->document->loadXML(static::empty());
	}

	public function __destruct() {
		// Деструктор
		unset($this->document);
	}

	public function search(string $node_name, string $attribute_filter=''): array {
		// get all $node_name nodes from all levels with filter by attributes
		//	$attribute_filter allows select only filtered nodes
		//		Ex: 'inkscape:label' - all nodes where attribute "inkscape:label" exists
		//			'inkscape:label="suit"' - all nodes with attribute "inkscape:label" == "suit"
		//			'id="clipPath398"' - all nodes wit attribute id = "clipPath398"
		$xpath = new \DOMXPath($this->document);
		// register 'svg' namespace anyway
		$xpath->registerNamespace('svg', 'http://www.w3.org/2000/svg');
		// register namespace if exists in $attribute_filter
		if(strpos($attribute_filter, ':') !== false) {
			$prefix = explode(':', $attribute_filter)[0];
			if($prefix) {
				$registered_namespaces = $this->namespaces();
				if(!array_key_exists($prefix, $registered_namespaces)) {
					$xpath->registerNamespace($prefix, $prefix . '.org/namespace/');
				}
			}
		}
		// search for the required node (rect) with attribute ('artcards::assets_lib')
		$node_path = '//svg:' . $node_name . ($attribute_filter ? '[@' . $attribute_filter . ']' : '');
		// var_dump($node_path);
		$nodes = $xpath->query($node_path);
		// var_dump($nodes);
		if($nodes) {
			return array_map(
				fn($item) => SVGNodeFactory::node($item, $this->document),
				iterator_to_array($nodes)
			);
		} else return [];
	}

	public function svg_node() {
		// get main <svg></svg> node from document
		return SVGNodeFactory::node(
			$this->document->documentElement,	// <svg ... />
			$this->document
		);
	}

	private function namespaces() {
		// return all namespaces
		$namespaces = [];
		$xpath = new \DOMXPath($this->document);
		foreach($xpath->query('namespace::*', $this->document->documentElement) as $node) {
			$namespaces[$node->prefix] = $node->nodeValue;
		}
		return $namespaces;
	}

	private function attributes() {
		// return <svg> node attributes
		return $this->svg_node()->attributes();
	}

	public function attribute(string $name, bool $ci = True) {
		// return <svg> node attribute value by name
		return $this->svg_node()->attribute($name, $ci);
	}

	public function set_attribute(string $name, $value) {
		// set attribute for <svg> node
		return $this->svg_node()->set_attribute($name, $value);
	}

	public function dimensions() {
		// get svg dimensions attributes
		if($this->svg_node()->has_attribute('viewBox')) {
			// viewBox="0 0 135.47 135.47"
			$view_box = explode(' ', $this->svg_node()->attribute('viewBox'));
			return [
				'x' => $view_box[0],
				'y' => $view_box[1],
				'width' => $view_box[2],
				'height' => $view_box[3]
			];
		} elseif($this->svg_node()->has_attribute('width') && $this->svg_node()->has_attribute('height')) {
			// width="512" height="512"
			return [
				'x' => 0,
				'y' => 0,
				'width' => SVGAttribute::width($this->attribute('width')),
				'height' => SVGAttribute::width($this->attribute('height'))
			];
		}
		return NULL;
	}

	public function children(): array {
		// return all child nodes inside main <svg></svg> node
		return $this->svg_node()->children();
	}

	public function children_as_group($exclude=[]) {
		// return all child nodes wrapped to group <g>...</g>
		return $this->svg_node()->children_as_group($exclude);
	}

	public function append_node($node) {
		// append new node to .svg
		return $this->svg_node()->append_node($node);
	}

	public function remove_node($node) {
		// remove node from .svg
		// return $this->svg_node()->remove_node();
		$node->parent()->removeChild($node->node());
	}

	public function replace_node($old_node, $new_node) {
		// replace $old_node with $new_node in .svg
		return $this->svg_node()->replace_node($old_node, $new_node);
	}

	public function merge($other_svg) {
		// merge current svg with other svg
		$this->add_defs($other_svg->defs());
		$this->add_style($other_svg->style());
		$this->append_node($other_svg->children_as_group(['defs', 'style']));
	}

	public function defs() {
		// get <defs> node
		if($defs = $this->search('defs')) return $defs[0];
		else return null;
	}

	public function add_defs($defs) {
		// add defs to svg
		// $defs = SVGNode with children
		if($defs) {
			$own_defs = $this->defs();
			if(!$own_defs) {
				// if own <defs> node not exists - create it
				$own_defs = $this->svg_node()->create_node('defs');
			}
			if($own_defs) {
				// appedn to <defs> node
				foreach($defs->children() as $defs_sub_node) {
					$own_defs->append_node($defs_sub_node);
				}
			}
		}
	}

	public function style() {
		// get <style> node
		if($style = $this->search('style')) return $style[0];
		else return null;
	}

	public function add_style($style) {
		// add style to svg
		// $style = SVGNode with children
		if($style) {
			$own_style = $this->style();
			if(!$own_style) {
				// if own <style> node not exists - create it
				// ToDo: now appends to the end, maybe need to append after <defs> or last <style> node
				$own_style = $this->svg_node()->create_node('style');
			}
			if($own_style) {
				// append to <style> node
				foreach($style->children() as $style_sub_node) {
					$own_style->append_node($style_sub_node);
				}
			}
		}
	}

	public function change_ids(string $postfix='', $node=NULL): void {
		// chage all id-s in svg adding a $postfix to all of them
		$node = $node ?: $this->svg_node()->node();
		if($node->nodeType == XML_ELEMENT_NODE) {
			// var_dump($node->nodeName);
			// id-s
			if($node->hasAttribute('id')) {
				$attr_value = $node->getAttribute('id');
				$node->setAttribute('id', $attr_value . '-' . $postfix);
			}
			// xlink (exclude embeded images, starts with "data:image")
			if($node->hasAttribute('xlink:href')) {
				$attr_value = $node->getAttribute('xlink:href');
				if(substr($attr_value, 0, strlen('data:image')) !== 'data:image') {
					$node->setAttribute('xlink:href', $attr_value . '-' . $postfix);
				}
			}
			// fill="url(#ab)"
			if($node->hasAttribute('fill')) {
				$attr_value = $node->getAttribute('fill');
				if(substr($attr_value, 0, strlen('url')) === 'url') {
					// url(#ab) -> url(#ab-0)
					$attr_value = preg_replace('#(url\(\#)(.+?)(\))#', '${1}${2}-' . $postfix . '${3}', $attr_value);
					$node->setAttribute('fill', $attr_value);
				}
			}
			// clip-path="url(#ep)"
			if($node->hasAttribute('clip-path')) {
				$attr_value = $node->getAttribute('clip-path');
				if(substr($attr_value, 0, strlen('url')) === 'url') {
					// url(#ab) -> url(#ab-0)
					$attr_value = preg_replace('#(url\(\#)(.+?)(\))#', '${1}${2}-' . $postfix . '${3}', $attr_value);
					$node->setAttribute('clip-path', $attr_value);
				}
			}
			// style="fill:url(#linearGradient2);...."
			if($node->hasAttribute('style')) {
				$attr_value = $node->getAttribute('style');
				// fill:url(#linearGradient2);.... -> fill:url(#linearGradient2-0);....
				$attr_value = preg_replace('#(fill:url\(\#)(.+?)(\))#', '${1}${2}-' . $postfix . '${3}', $attr_value);
				$node->setAttribute('style', $attr_value);
			}
			// <style> .s0 { fill: #000000 } .s1 { fill: #02ff08 } </style>
			// <style> .s1 { fill: url(#g1) } ... </style>
			if($node->nodeName == 'style') {
				$node_value = $node->nodeValue;
				// ".s0 {" -> ".s0-0 {"
				$node_value = preg_replace('#(\.)(\S+?)(\s*{)#', '${1}${2}-' . $postfix . '${3}', $node_value);
				// url(#g1) -> url(#g1-0)
				$node_value = preg_replace('#(url\(\#)(.+?)(\))#', '${1}${2}-' . $postfix . '${3}', $node_value);
				$node->nodeValue = $node_value;
			}
			// class calls
			if($node->hasAttribute('class')) {
				$attr_value = $node->getAttribute('class');
				$node->setAttribute('class', $attr_value . '-' . $postfix);
			}
			// process all child nodes of the current $node
			if($node->hasChildNodes()) {
				foreach($node->childNodes as $child_node) {
					static::change_ids($postfix, $child_node);
				}
			}
		}
	}

	public function XML(): string {
		// get .svg body
		return $this->document->saveXML();
	}

	public function base64(): string {
		// get .svg as base64 encoded data
		return 'data:image/svg+xml;base64,' . base64_encode($this->document->saveXML());
	}

	public function save(string $path): void {
		// save as .svg file by $path
		$this->document->save($path);
	}

	private static function empty() {
		return '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'
			. '<svg'
			. '	version="1.1"'
			. '	id="svg1"'
			. '	xmlns="http://www.w3.org/2000/svg"'
			. '	xmlns:svg="http://www.w3.org/2000/svg"'
			. '>'
			. '<defs'
			. '	id="defs1" />'
			. '</svg>';
	}

	public function remove_guidelines() {
		// remove guide lines from current .svg
		$xpath = new \DOMXPath($this->document);
		$xpath->registerNamespace('sodipodi', 'http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd');
		$nodes = $xpath->query('//sodipodi:guide');
		foreach($nodes as $node) {
			$node->parentNode->removeChild($node);
		}
	}

	public function image_size() {
		// get real image size dimensions
		if(static::imagick_available()) {
			// get real dimensions with imagick
			$imagick = new \Imagick();
			$imagick->readImage($this->path);
			return array_merge(
				['x' => 0, 'y' => 0],
				$imagick->getImageGeometry()
			);
			$imagick->clear();
			$imagick->destroy();
		}
		return NULL;
	}

	private static function imagick_available() {
		// check if ImageMagick (imagick) is installed and is available for use
		return extension_loaded('imagick') ? True : False;
	}

	public static function jpeg($dest_path, $dimensions=[], $compression=100) {
		// sage .svg to .jpg
		// $compression = 80 - level of compression in %, 100 - not compressed
		// $dimensions = [1200, 600] - scale image to dimensions in pix

		// ToDo: works bad, imagick can't work with complex .svg
		//	better to use librsvg2-bin -> rsvg-convert
		
		$rez = false;
		if(static::imagick_available()) {
			$imagick = new \Imagick();
			$imagick->setBackgroundColor('#ffffff');	// white
			$imagick->readImageBlob(base64_encode($this->document->saveXML()));
			$imagick->setImageFormat('jpeg');
			// resize
			if($dimensions) {
				$imagick->resizeImage($dimensions[0], $dimensions[1], $imagick::FILTER_LANCZOS, 1);
			}
			// compress
			if($compression && $compression < 100) {
				$imagick->setImageCompression(true);
				$imagick->setImageCompression($imagick::COMPRESSION_JPEG);
				$imagick->setImageCompressionQuality($compression);
			}
			// save to file
			$rez = $imagick->writeImage($dest_path);
			$imagick->clear();
			$imagick->destroy();
		}
		return $rez;
	}
}
//------------------------------------------------------------------------------------------------------------------------
?>
