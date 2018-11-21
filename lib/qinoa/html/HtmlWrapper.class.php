<?php
namespace qinoa\html;

class HtmlTag {
	protected $id;
	protected $tag_name;
	protected $style;
	protected $attributes;

	public function __construct($id, $tag_name, $style, $attributes, $children) {
		$this->setId($id);
		$this->setTagName($tag_name);
		$this->style = array();
		$this->attributes = array();
		$this->children = $children;
		$this->setStyle($style);
		$this->setAttributes($attributes);
	}

	public final function getHTML() {
        $result = "<{$this->tag_name}";
        if(!empty($this->id)) $result .= " id=\"{$this->id}\"";
        if(!empty($this->style)) {
        	$result .= ' style="';
	        foreach($this->style as $property => $value) $result .= "$property: $value;";
        	$result .= '"';
		}
        foreach($this->attributes as $attribute => $value) $result .= " $attribute=\"$value\"";
		//if(empty($this->children)) $result .= " />";
//		else {
			$result .= ">";
			if(!is_array($this->children)) $result .= $this->children;
			else foreach($this->children as $child) $result .= $child;
			$result .= "</{$this->tag_name}>";
//		}
		return $result;
	}

	public final function __toString() {
		return $this->getHTML();
	}

	public final function setId($id) {$this->id = $id;}

	public function setTagName($tag) {$this->tag_name = $tag;}

	public final function getTagName() {return $this->tag_name;}
	
	public final function setStyle($style) {
		if(empty($style)) return;
		foreach($style as $property => $value) $this->style[$property] = $value;
	}

	public final function setAttributes($attributes) {
		if(empty($attributes)) return;
		foreach($attributes as $attribute => $value) $this->attributes[$attribute] = $value;
	}

	public function add($child) {
    	if(!isset($this->children)) $this->children = array();
    	$this->children[] = $child;
	}

}

class HtmlInline extends HtmlTag{

	public function __construct($id, $tag_name=null, $style=null, $attributes=null, $children=null) {
		if(empty($tag_name)) $tag_name = 'span';
		parent::__construct($id, $tag_name, $style, $attributes, $children);
	}

	public function add($child) {
		if(is_string($child) || (is_a($child, 'HtmlTag') && !is_a($child, 'HtmlBlock'))) {
			parent::add($child);
			return true;
		}
		return false;
	}

	public function setTagName($tag) {
		// todo : to complete, inline html tags
		if(in_array($tag, array('a', 'br', 'code', 'dfn', 'em', 'i', 'img', 'input', 'li', 'option', 'span', 'strong', 'u')))
			parent::setTagName($tag);
	}
}

class HtmlBlock extends HtmlTag{

	public function __construct($id, $tag_name=null, $style=null, $attributes=null, $children=null) {
		if(empty($tag_name)) $tag_name = 'div';
		parent::__construct($id, $tag_name, $style, $attributes, $children);
	}

	public function add($child) {
		if(is_string($child) || is_a($child, 'HtmlTag')) {
			parent::add($child);
			return true;
		}
		return false;
	}

	public function setTagName($tag) {
		// todo : to complete, block html tags
		if(in_array($tag, array('body', 'div', 'pre', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ol', 'ul', 'select', 'table', 'tr', 'td')))
			parent::setTagName($tag);
	}

}


class HtmlWrapper {
	private $js_files;
	private $css_files;
	private $metas;
	private $script;
	private $style;
	private $charset;
	private $htmlBody;

	public function __construct() {
		$this->js_files = array();
		$this->css_files = array();
		$this->metas = array();
		$this->script = '';
		$this->style = '';
		$this->charset = 'UTF-8';
		$this->htmlBody = null;
   	}

	public function __toString() {
		$html = "<!DOCTYPE HTML>\n<html>\n<head>\n";
		foreach($this->metas as $name => $content) $html .= "<meta name=\"{$name}\" content=\"$content\" />\n";
		$html .= "<meta charset=\"{$this->charset}\" />\n";
		foreach($this->css_files as $file) $html .= "<link rel=\"stylesheet\" href=\"$file\" type=\"text/css\" />\n";
		foreach($this->js_files as $file) $html .= "<script language=\"javascript\" type=\"text/javascript\" src=\"$file\"></script>\n";
		if(!empty($this->style)) $html .= '<style type="text/css">'.$this->style."</style>\n";
		if(!empty($this->script)) $html .= '<script language="javascript" type="text/javascript">'.$this->script."</script>\n";
		$html .= "</head>\n".$this->htmlBody."\n</html>\n";
        $html .= "\n";
		return $html;
	}

	public function setCharset($chasert) {
		$this->charset = $charset;
	}

	/**
	 * Add meta tag
	 *
	 * @param string $name valid meta name (author, keywords, ...)
	 * @param string $content the meta value
	 */
	function addMeta($name, $content) {
		$this->metas[$name] = $content;
		return $this;		
	}

	function addJSFile($file) {
		$this->js_files[] = $file;
		return $this;	
	}

	function addCSSFile($file) {
		$this->css_files[] = $file;
		return $this;
	}

	function addScript($script) {
		$this->script .= $script;
		return $this;
	}

	function addStyle($style) {
		$this->style .= $style;
		return $this;	
	}

	function add($html) {
		if(is_null($this->htmlBody)) {
			if(is_a($html, 'HtmlTag') && $html->getTagName() == 'body') {
				$this->htmlBody = $html;
				return $this;
			}
			else $this->htmlBody = new HtmlBlock(0, 'body');
		}
		$this->htmlBody->add($html);
		return $this;
	}

	/**
	* deprecated : Use 'add' method instead
	*
	* @deprecated
	*/
	function setBody($html) {
		if(is_string($html) || is_a($html, 'HtmlTag')) {
			$this->htmlBody = &$html;
			return true;
		}
		return false;
	}

}