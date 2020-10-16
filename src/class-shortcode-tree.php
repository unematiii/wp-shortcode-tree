<?php
/**
 * Implementation of Shortcode and ShortcodeTree classes
 *
 * @package WordPress
 */

namespace WordPress;

/**
 * Represents a single shortcode together with its attributes and siblings
 */
class Shortcode {
	/**
	 * Shortcode (tag) name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Shortcode attributes (associative array of key/value pairs)
	 *
	 * @var array
	 */
	protected $atts;

	/**
	 * Shortcode content
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * Parent node of this shortcode. Null, if shortcode has no parent
	 *
	 * @var \WordPress\ShortCode|NULL
	 */
	protected $parent;

	/**
	 * Array of siblings as instances of \WordPress\ShortCode
	 *
	 * @var array
	 */
	protected $shortcodes = array();

	/**
	 * Determines if shortcode has a closing tag
	 *
	 * @var boolean
	 */
	protected $closed = false;

	/**
	 * Construct new ShortCode
	 *
	 * @param string                    $name Shortcode name.
	 * @param array                     $atts Shortcode attributes (associative array of key/value pairs).
	 * @param string                    $content Content of shortcode (text).
	 * @param array                     $shortcodes Array of siblings as instances of \WordPress\ShortCode.
	 * @param \WordPress\ShortCode|NULL $parent Parent node of this shortcode. Null, if shortcode has no parent.
	 */
	public function __construct($name = '', $atts = array(), $content = '', $shortcodes = array(), $parent = null) {
		$this->name       = $name;
		$this->atts       = $atts;
		$this->content    = $content;
		$this->shortcodes = $shortcodes;

		$this->parent = $parent;
	}

	/**
	 * Gets shortcode name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets shortcode name
	 *
	 * @param string $name Shortcode name.
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Gets/sets attribute value:
	 *  if $value is not provided, returns attributes value
	 *  if $value is provided, sets attributes value
	 *
	 * @param string $name Attribute name.
	 * @param string $value Attribute value.
	 * @return void|string
	 */
	public function attr($name = null, $value = null) {
		if (isset( $this->atts [ $name ] ) && is_null( $value )) {
			return $this->atts [ $name ];
		} else {
			$this->atts [ $name ] = $value;
		}
	}

	/**
	 * Returns associative array of shortcode attributes as a reference
	 *
	 * @return array
	 */
	public function &atts() {
		return $this->atts;
	}

	/**
	 * Returns true if shortcode has a closing tag, false otherwise
	 *
	 * @return boolean
	 */
	public function getClosed() {
		return $this->closed;
	}

	/**
	 * Marks shortcode as closing or self-closing
	 *
	 * @param boolean $state If true, shortcode will be serialized with closing tag.
	 * @return void
	 */
	public function setClosed($state = true) {
		$this->closed = $state;
	}

	/**
	 * Gets shortcode content
	 *
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Sets shortcode content
	 *
	 * @param string $content Text content.
	 * @return void
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * Gets shortcode parent. Returns NULL if this short has no root node.
	 *
	 * @return \WordPress\ShortCode|NULL
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Sets shortcode parent
	 *
	 * @param \WordPress\ShortCode $parent Parent node.
	 * @return void
	 */
	public function setParent($parent) {
		if ($parent instanceof Shortcode) {
			$this->parent = $parent;
		}
	}

	/**
	 * Adds another shortcode as a sibling and marks itself as a root of that shortcode
	 *
	 * @param \WordPress\ShortCode $shortcode Shortcode.
	 * @return void
	 */
	public function addShortcode($shortcode) {
		if ($shortcode instanceof Shortcode) {
			$shortcode->setParent( $this );
			array_push( $this->shortcodes, $shortcode );
		}
	}

	/**
	 * Returns array of shortcodes as a reference
	 *
	 * @return array
	 */
	public function &shortcodes() {
		return $this->shortcodes;
	}

	/**
	 * Find the n-th occurrence of the shortcode in the tree, where n (= $index) is zero-based
	 *
	 * @param string $shortcode_name Shortcode name.
	 * @param number $index n-th occurrence (zero-based).
	 * @param number $c Optional. Counter for determining occurrences found so far.
	 * @return \WordPress\ShortCode|NULL
	 */
	public function findNthOccurrence($shortcode_name, $index, &$c = 0) {
		$index = (int) $index;

		if ($shortcode_name === $this->name) {
			if ($c === $index) {
				return $this;
			} else {
				++ $c;
			}
		}

		if ( ! count( $this->shortcodes )) {
			return null;
		}

		$occurrences = array();
		foreach ( $this->shortcodes as $shortcode ) {
			array_push( $occurrences, $shortcode->findNthOccurrence( $shortcode_name, $index, $c ) );
		}

		$occurrences = array_filter(
			$occurrences,
			function ($o) {
				return ! is_null( $o );
			}
		);
		$occurrence  = reset( $occurrences );

		return $occurrence ? $occurrence : null;
	}

	/**
	 * Traverses child nodes recursively and finds all shortcodes that match specified name
	 *
	 * @param string $shortcode_name Name to look up shortcodes by.
	 * @return array
	 */
	public function findAll ($shortcode_name) {
		$result = array();
		if ($shortcode_name === $this->name) {
			array_push( $result, $this );
		}

		if (count( $this->shortcodes )) {
			foreach ($this->shortcodes as $shortcode) {
				$result = array_merge( $result, $shortcode->findAll( $shortcode_name ) );
			}
		}

		return $result;
	}

	/**
	 * Parses string of shortcodes into array of \Wordpress\ShortCode.
	 * For single rooted hierarchy, array only contains one root node.
	 *
	 * @param string $shortcode Input string to parse.
	 * @return array
	 */
	public static function fromString($shortcode) {
		$pattern = get_shortcode_regex();
		$nodes   = array();

		if (preg_match_all( "/$pattern/s", $shortcode, $matches )) {
			$node_count = count( $matches [0] );

			for ($node_index = 0; $node_index < $node_count; $node_index ++) {
				if (strlen( $matches [2] [ $node_index ] )) {
					/* Name */
					$name = $matches [2] [ $node_index ];

					/* Attributes */
					$atts = shortcode_parse_atts( $matches [3] [ $node_index ] );

					/* Instantiate */
					$node = new Shortcode( $name, '' === $atts ? array() : $atts );

					/* Siblings */
					$siblings = array();
					if (strlen( $matches [5] [ $node_index ] )) {
						if (0 === strpos( trim( $matches [5] [ $node_index ] ), '[' )) {
							$siblings = self::fromString( $matches [5] [ $node_index ] );
						} else {
							$node->setContent( $matches [5] [ $node_index ] );
						}
					}

					foreach ( $siblings as $sibling ) {
						$node->addShortcode( $sibling );
					}

					if (0 === count( $node->shortcodes )) {
						if (preg_match( '~\[/' . $name . '\]\s*$~', trim( $matches[0][ $node_index ] ) )) {
							$node->setClosed( true );
						}
					}

					/* Processed */
					array_push( $nodes, $node );
				}
			}
		}

		return $nodes;
	}

	/**
	 * Serializes shortcode into string
	 *
	 * @return string
	 */
	public function __toString() {
		$is_tag            = (bool) strlen( trim( $this->name ) );
		$is_nested         = (bool) count( $this->shortcodes );
		$needs_closing_tag = $this->getClosed();
		$has_content       = (bool) strlen( $this->content );

		$out  = '';
		$atts = $this->atts;

		if ($is_tag) {
			$out .= '[' . $this->name;
		}

		if (count( $this->atts )) {
			$out .= ' ' . implode(
				' ',
				array_map(
					function ($key) use($atts) {
						$value = $atts[ $key ];
						if (is_int( $key )) {
							return $value;
						}

						return $key . '="' . $atts [ $key ] . '"';
					},
					array_keys( $this->atts )
				)
			);
		}

		if ($is_tag) {
			$out .= ']';
		}

		if ($is_nested || $has_content) {
			if ($is_nested) {
				$out .= implode(
					'',
					array_map(
						function ($s) {
							return (string) $s;
						},
						$this->shortcodes
					)
				);
			} else {
				$out .= $this->content;
			}

			if ($is_tag) {
				$needs_closing_tag = true;
			}
		}

		if ($needs_closing_tag) {
			$out .= '[/' . $this->name . ']';
		}

		return $out;
	}
}

/**
 * Represents a single rooted shortcode tree
 */
class ShortcodeTree {
	/**
	 * Root node of the tree
	 *
	 * @var \WordPress\ShortCode|NULL
	 */
	protected $root;

	/**
	 * Constructs new \WordPress\ShortcodeTree
	 *
	 * @param \WordPress\ShortCode|NULL $root Root node.
	 */
	public function __construct($root = null) {
		if ( ! is_null( $root )) {
			$this->setRoot( $root );
		}
	}

	/**
	 * Gets the root node of the tree
	 *
	 * @return \WordPress\ShortCode|NULL
	 */
	public function getRoot() {
		return $this->root;
	}

	/**
	 * Sets the root node of the tree
	 *
	 * @param \WordPress\ShortCode $root Root node.
	 * @return void
	 */
	public function setRoot($root) {
		if ($root instanceof Shortcode) {
			$this->root = $root;
		}
	}

	/**
	 * Constructs ShortCodeTree from string.
	 * For non single rooted hierarchy, a dummy parent node is constructed.
	 *
	 * @param string $shortcode Shortcode(s) to parse.
	 * @return \WordPress\ShortCodeTree
	 */
	public static function fromString($shortcode) {
		$tree  = new ShortcodeTree();
		$nodes = Shortcode::fromString( $shortcode );

		if (count( $nodes )) {
			if (count( $nodes ) > 1) {
				$root = new Shortcode();
				foreach ( $nodes as $node ) {
					$root->addShortcode( $node );
				}

				$tree->setRoot( $root );
			} else {
				$tree->setRoot( $nodes[0] );
			}
		}

		return $tree;
	}

	/**
	 * Serializes shortcode tree into string
	 *
	 * @return string
	 */
	public function __toString() {
		return (string) $this->root;
	}
}
