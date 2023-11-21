<?php
//------------------------------------------------------------------------------------------------------------------------
// SVG Node - base class for node
//------------------------------------------------------------------------------------------------------------------------
namespace svg_int;
//------------------------------------------------------------------------------------------------------------------------
require_once('.root');

use svg_int\SVGNodeFactory;
//------------------------------------------------------------------------------------------------------------------------
class SVGNode {
	
	private $document = NULL;	// pointer to DomDocumetn
	private $node = NULL;		// pointer to DomNode
	public $is_transformable = False;

	public function __construct(&$node, &$document) {
		// Constructor
		$this->node = $node;
		$this->document = $document;
	}

	public function __destruct() {
		// destructor
		unset($this->document);
		unset($this->node);
	}

	public function node() {
		// return node
		return $this->node;
	}

	public function attributes() {
		// return node attributes
		if($this->node) {
			return $this->node->attributes;
		}
	}

	public function attribute(string $name, bool $ci = True) {
		// return node attribute value by name
		if($this->attributes()) {
			foreach($this->attributes() as $attribute) {
				if($ci && mb_strtolower($attribute->nodeName) == mb_strtolower($name)) return $attribute->nodeValue;
				elseif($attribute->nodeName == $name) return $attribute->nodeValue;
			}
		}
		return NULL;
	}

	public function has_attribute(string $name) {
		// check if node has attribute with $name
		return ($this->node() ? $this->node()->hasAttribute($name) : False);
	}

	public function set_attribute(string $name, $value, string $namespace=NULL) {
		// set attribute to node, if attribute not exists - it creates
		if($namespace) $this->node()->setAttributeNS($namespace, $name, $value);
		else $this->node()->setAttribute($name, $value);
	}

	public function name(): string {
		// return node name
		return $this->node()->nodeName;
	}

	public function tag_name(): string {
		// return node tagName
		return isset($this->node()->tagName) ? $this->node()->tagName : '';
	}

	public function parent() {
		// return parent node of this node
		return $this->node()->parentNode;
	}

	public function type() {
		// return node type of this node
		return $this->node()->nodeType;
	}

	public function children(): array {
		// return all child nodes
		if($child_nodes = $this->node()->childNodes) {
			return array_map(
				fn($node) => SVGNodeFactory::node($node, $this->document),
				iterator_to_array($child_nodes)
			);
		} else return [];
	}

	public function children_as_group($exclude=[]) {
		// return all child nodes wrapped to group <g>...</g>
		// $exclude = ['defs', 'style'] - list of nodes tagName not to add to the group
        $group = $this->document->createElement('g');
		foreach($this->children() as $node) {
			if(!in_array($node->name(), $exclude)) {
				$group->appendChild($node->node());
			}
		}
		return SVGNodeFactory::node($group, $this->document);
	}

	public function create_node(string $node_name) {
		// create new subnode
		if($node_name) {
			$node_element = $this->document->createElement($node_name);
			$node = SVGNodeFactory::node($node_element, $this->document);
			$node = $this->append_node($node);
			return SVGNodeFactory::node($node, $this->document);
		}
	}

	public function append_node($node) {
		// append new node
		return $this->node()->appendChild(
			$this->document->importNode($node->node(), true)
		);
	}

	public function remove_node($node) {
		// remove node from .svg
		return $this->node()->removeChild($node->node());
	}

	public function replace_node($old_node, $new_node) {
		// replace old_node with new_node
		return $old_node->parent()->replaceChild(
			$this->document->importNode($new_node->node(), true),
			$old_node->node()
		);
	}
}
//------------------------------------------------------------------------------------------------------------------------
?>
