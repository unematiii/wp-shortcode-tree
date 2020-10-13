<?php

namespace WordPress;

class Shortcode {
	protected $name;
	protected $atts;
	protected $content;
	protected $parent;
	protected $shortcodes = array ();
	
	public function __construct($name = '', $atts = array(), $content = '', $shortcodes = array(), $parent = null) {
		$this->name = $name;
		$this->atts = $atts;
		$this->content = $content;
		$this->shortcodes = array ();
		
		$this->parent = $parent;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}

	public function attr($name = null, $value = null) {
		if (isset ( $this->atts [$name] )) {
			if (is_null ( $value ))
				return $this->atts [$name];
			else
				$this->atts [$name] = $value;
		}
	}
	
	public function &atts() {
		return $this->atts;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function setContent($content) {
		$this->content = $content;
	}
	
	public function getParent() {
		return $this->parent;
	}
	
	public function setParent($parent) {
		if ($parent instanceof Shortcode) {
			$this->parent = $parent;
		}
	}
	
	public function add_shortcode($shortcode) {
		if ($shortcode instanceof Shortcode) {
			$shortcode->setParent ( $this );
			array_push ( $this->shortcodes, $shortcode );
		}
	}
	
	public function &shortcodes() {
		return $this->shortcodes;
	}
	
	/**
	 * Find the n-th occurence of the shortcode in the tree, where n (= $index) is zero-based
	 * 
	 * @param string $shortcode_name
	 * @param number $index
	 * @param number $c
	 * @return \WordPress\ShortCode|NULL
	 */
	public function findNthOccurrence($shortcode_name, $index, &$c = 0) {
		$index = (int) $index;

		if ($shortcode_name === $this->name)
			if ($c === $index)
				return $this;
			else
				++ $c;
		
		if (! count ( $this->shortcodes ))
			return null;
		
		$occurrences = array ();
		foreach ( $this->shortcodes as $shortcode )
			array_push ( $occurrences, $shortcode->findNthOccurrence ( $shortcode_name, $index, $c ) );
		
		$occurrences = array_filter ( $occurrences, function ($o) {
			return ! is_null ( $o );
		} );
		
		return $occurrences[0];
	}
	
	public function findAll ($shortcode_name) {
		$result = array();
		if ($shortcode_name === $this->name)
			array_push($result, $this);
		
		if (count ( $this->shortcodes ))
			foreach ($this->shortcodes as $shortcode) {
				$result = array_merge($result, $shortcode->findAll($shortcode_name));
			}
		
		return $result;
	}
	
	public static function fromString($shortcode) {
		$pattern = get_shortcode_regex ();
		$nodes = array ();
		
		if (preg_match_all ( "/$pattern/s", $shortcode, $matches )) {
			$node_count = count ( $matches [0] );
			
			for($node_index = 0; $node_index < $node_count; $node_index ++) {
				if (strlen ( $matches [2] [$node_index] )) {
					// Name
					$name = $matches [2] [$node_index];
					
					// Attributes
					$atts = shortcode_parse_atts ( $matches [3] [$node_index] );
									
					// Instantiate
					$node = new Shortcode ( $name, '' === $atts ? array () : $atts );
					
					// Siblings
					$siblings = array ();
					if (strlen ( $matches [5] [$node_index] )) {
						if(0 === strpos( trim( $matches [5] [$node_index] ), '[' ))
							$siblings = Shortcode::fromString ( $matches [5] [$node_index] );
						else
							$node->setContent($matches [5] [$node_index]);
					}
					
					foreach ( $siblings as $sibling )
						$node->add_shortcode ( $sibling );
						
					// Processed
					array_push ( $nodes, $node );
				}
			}
		}
		
		return $nodes;
	}
	
	public function __toString() {
		$is_tag = ( bool ) strlen( trim ( $this->name ) );
		$is_nested = ( bool ) count ( $this->shortcodes );
		$has_content = ( bool ) strlen ( $this->content );
		
		$out = '';
		$atts = $this->atts;
		
		if ($is_tag)
			$out .= '[' . $this->name;
		
		if (count ( $this->atts ))
			$out .= ' ' . implode ( ' ', array_map ( function ($key) use($atts) {
				return $key . '="' . $atts [$key] . '"';
			}, array_keys ( $this->atts ) ) );
		
		if ($is_tag)
			$out .= ']';
		
		if ($is_nested || $has_content) {
			if ($is_nested) {
				$out .= implode ( '', array_map ( function ($s) {
					return ( string ) $s;
				}, $this->shortcodes ) );
			} else {
				$out .= $this->content;
			}
			
			if ($is_tag)
				$out .= '[/' . $this->name . ']';
		}
		
		return $out;
	}
}

class ShortcodeTree {
	protected $root;
	
	public function __construct($root = null) {
		if (! is_null ( $root ))
			$this->setRoot ( $root );
	}
	
	public function getRoot() {
		return $this->root;
	}
	
	public function setRoot($root) {
		if ($root instanceof Shortcode)
			$this->root = $root;
	}
	
	/**
	 * 
	 * @param string $shortcode
	 * @return \WordPress\ShortCodeTree
	 */
	public static function fromString($shortcode) {
		$tree = new ShortcodeTree ();
		$nodes = Shortcode::fromString ( $shortcode );
		
		if (count ( $nodes )) {
			if (count ( $nodes ) > 1) {
				$root = new Shortcode ();
				foreach ( $nodes as $node )
					$root->add_shortcode ( $node );
				
				$tree->setRoot ( $root );
			} else {
				$tree->setRoot ( $nodes[0] );
			}
		}
		
		return $tree;
	}
	
	public function __toString() {
		return (string) $this->root;
	}
}