<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace qinoa\html;

/**
 *	This class implements a lightweight HTML parser that replaces 'var' tags with content stored in renderer array.
 *
 *
 */
class HtmlTemplate {
	protected $template;	// string containing some html 
	protected $renderer;	// array of functions for rendering template (functions associated with var tags ids)
	protected $params;		// array containing values required by rendering functions
	
	/**
	 * Returns html part specified by $attributes, given some additional parameters.
	 * This method is called for each 'var' tag. As parameter, it should receive an array with tag attributes (at least 'id'). 
	 * Return value should be a string containing html. 
	 * To customize its behavior, this method might be overriden in inherited classes
	 *
	 * @param array $attributes	tag attributes
	 */
	protected function decorator($attributes, $innerHTML='') {
        $result = '';
        // handle conditional statement
        // note: if statement should only be used with parameters, not with renderer keys
        if(isset($attributes['if'])) {
            $condition = $attributes['if'];
            // inside conditional statement, replace matching strings with values defined in params
            foreach($this->params as $param => $value) {
                // skip arrays and objects
                if(is_string($value) || is_integer($value)) {
                    $condition = str_replace($param, "'{$value}'", $condition);
                }
            }
            $condition = html_entity_decode($condition);
            $res = eval('return ('.$condition.');');
            if(!$res) return '';
        }
        // if id attribute is present, use it to retrieve related value
		if(isset($attributes['id'])) {
            if(isset($this->renderer[$attributes['id']])) {
                $result = $this->renderer[$attributes['id']]($this->params, $attributes);
            }
            else {
                // handle 'dot' notation
                $array = $this->params;
                $key = $attributes['id'];                
                while(strpos($key, '.')) {
                    list($key, $remain) = explode('.', $key, 2);
                    if(!isset($array[$key])) return '';
                    $array = $array[$key];
                    $key = $remain;
                }
                $result = $array[$key];
            }
        }
        else {
            $result = $innerHTML;
        }
		return $result;
	}
	
	public function __construct($template, $renderer, $params=null) {
		$this->setTemplate($template);
		$this->setRenderer($renderer);
		$this->setParams($params);	
	}
	
	public function setTemplate($template) {
		$this->template = $template;
	}

	public function setRenderer($renderer) {
		$this->renderer = $renderer;
	}

	public function setParams($params) {
		$this->params = $params;
	}

	
	/**
	 * Replaces 'var' tags with content specified by the decorator method.
	 *
	 *
	 * @param  string $template	Some HTML to parse
	 * @return string        	HTML resulting from the processed template 
	 */
	public function getHtml() {
		$previous_pos = 0;
		$html = '';
		// use regular expression to locate all 'var' tags in the template
		preg_match_all("/<var([^>]*)>(.*)<\/var>/iUs", $this->template, $matches, PREG_OFFSET_CAPTURE);
		// replace each 'var' tags with its associated content
		for($i = 0, $j = count($matches[1]); $i < $j; ++$i) {
			// 0) get inner HTML
            $innerHTML = trim($matches[2][$i][0]);
            // 1) get tag attributes
            preg_match_all('/([^ ]*)="([^"]*)"/iU', $matches[1][$i][0], $matches2);
			$attributes = array();            
            for($k = 0, $l = count($matches2[0]); $k < $l; ++$k) {
                $attribute = $matches2[1][$k];
                $attributes[$attribute] = $matches2[2][$k];                
            }
			// 2) get content pointed by var tag
			$pos = $matches[0][$i][1];
			$len = strlen($matches[0][$i][0]);
			// replace tag with content and build resulting html
			$html .= substr($this->template, $previous_pos, ($pos-$previous_pos)).$this->decorator($attributes, $innerHTML);
			$previous_pos = $pos + $len;
		}
		// add trailer
		$html .= substr($this->template, $previous_pos);
		return trim($html);
	}	
}
