<?php
/**
 * 内容输出格式化
 */
class form_format {
    var $modelid;
    var $fields;
    var $formdata;
    var $extdata;//扩展数据，用于额外的参数传递，赋值方法：$form_add->extdata['mykey'] = 'data'
    var $id;//内容id

	function __construct($modelid) {
        $this->db = load_class('db');
        $this->tablepre = $this->db->tablepre;
        $this->modelid = $modelid;
        $this->fields = get_cache('field_'.$modelid,'model');
        $this->extdata = '';
    }
	function execute($formdata) {
        $this->formdata = $formdata;
		$this->id = $formdata['id'];
		$info = array();
        foreach($formdata as $field=>$value) {
            $field_config = $this->fields[$field];
            $func = $field_config['formtype'];
            $value = $formdata[$field];
            $result = method_exists($this, $func) ? $this->$func($field, $formdata[$field]) : $formdata[$field];
            if($result !== false) {
                $info[$field]['name'] = isset($field_config['name']) ? $field_config['name'] : $field;
                $info[$field]['data'] = $result;
            }
        }

		return $info;
	}
    function set_config($modelid) {
        $this->modelid = $modelid;
        $this->fields = get_cache('field_'.$modelid,'model');
    }

	private function baidumap($field, $value) {
		$value = p_htmlentities($value);
		return $value;
	}

    function box($field, $value) {
        extract($this->fields[$field]['setting']);
        if($outputtype) {
            return $value;
        } else {
            $options = explode("\n",$options);
            foreach($options as $_k) {
                $v = explode("|",$_k);
                $k = trim($v[1]);
                $option[$k] = $v[0];
            }
            $string = '';
            switch($boxtype) {
                case 'radio':
                    $string = $option[$value];
                    break;

                case 'checkbox':
                    $value_arr = explode(',',$value);
                    foreach($value_arr as $_v) {
                        if($_v) $string .= $option[$_v].' 、';
                    }
                    break;

                case 'select':
                    $string = $option[$value];
                    break;

                case 'multiple':
                    $value_arr = explode(',',$value);
                    foreach($value_arr as $_v) {
                        if($_v) $string .= $option[$_v].' 、';
                    }
                    break;
            }
            return $string;
        }
    }

    function box_sql($field, $value) {
        extract($this->fields[$field]['setting']);
        if($outputtype) {
            return $value;
        } else {
            $options = explode("\n",$options);
            foreach($options as $_k) {
                $v = explode("|",$_k);
                $k = trim($v[1]);
                $option[$k] = $v[0];
            }
            $string = '';
            switch($boxtype) {
                case 'radio':
                    $string = $option[$value];
                    break;

                case 'checkbox':
                    $value_arr = explode(',',$value);
                    foreach($value_arr as $_v) {
                        if($_v) $string .= $option[$_v].' 、';
                    }
                    break;

                case 'select':
                    $string = $option[$value];
                    break;

                case 'multiple':
                    $value_arr = explode(',',$value);
                    foreach($value_arr as $_v) {
                        if($_v) $string .= $option[$_v].' 、';
                    }
                    break;
            }
            return $string;
        }
    }

	private function content_group($field, $value) {
		$value = string2array($value);
		return $value;
	}

	private function copyfrom($field, $value){
		if (is_numeric($value)) {
			$r = $this->db->get_one('copyfrom', array('fromid' => $value));
			if ($r['logo']) {
				return '<a href="'.$r['url'].'" target="_blank"><span class="logo_ly"><img src="' . $r['logo'] . '"></span> ' . $r['name'] . '</a>';
			} else {
				return '<a href="'.$r['url'].'" target="_blank">' . $r['name'] . '</a>';
			}
		} elseif (is_string($value)) {
			if (strpos($value, '|') === false) {
				return $value;
			} else {
				$values = explode('|', $value);
				$values[1] = 'http://' . ltrim($values[1], 'http://');
				return '<a href="' . $values[1] . '" target="_blank">' . $values[0] . "</a>";
			}
			return $value;
		}
	}

	private function datetime($field, $value) {
		$setting = $this->fields[$field]['setting'];
		if($setting) extract($setting);
		if($fieldtype=='date' || $fieldtype=='datetime') {
			return $value;
		} else {
			$format_txt = $format;
		}
		if(strlen($format_txt)<6) {
			$isdatetime = 0;
		} else {
			$isdatetime = 1;
		}
		if(!$value) $value = SYS_TIME;
		$value = date($format_txt,$value);
		return $value;
	}

    private function downfile($field, $value) {
        if(empty($value)) return '';
        $setting = $this->fields[$field]['setting'];
        if($setting['linktype']) {
            if($setting['downloadtype']) {
                return $value;
            } else {
                return private_file($value);
            }
        } else {
            return WEBURL.'index.php?f=down&v=filedown&str='.urlencode(encode($setting['downloadtype'].$value));
        }
    }

	private function downfiles($field, $value) {
		if(empty($value)) return '';
		$values = string2array($value);
		$setting = $this->fields[$field]['setting'];
		if($setting['linktype']) {
			if($setting['downloadtype']) {
				return $values;
			} else {
				foreach($values as $k=>$v) {
					$values[$k]['url'] = private_file($v['url']);
				}
				return $values;
			}
		} else {
			foreach($values as $k=>$v) {
				$values[$k] = WEBURL.'index.php?f=down&v=filedown&str='.urlencode(encode($setting['downloadtype'].$v['url']));
			}
			return $values;
		}
	}

	private function images($field, $value) {
		$value = string2array($value);
		return $value;
	}

	private function keyword($field, $value) {
	    if($value == '') return '';
        $value = p_htmlentities($value);
        $tags = explode(',', $value);
		return $tags;
	}

    function text_select($field, $value) {
        extract($this->fields[$field]['setting']);
        if($outputtype) {
            return $value;
        } else {
            $options = explode("\n",$options);
            foreach($options as $_k) {
                $v = explode("|",$_k);
                $k = trim($v[1]);
                $option[$k] = $v[0];
            }
            $string = '';
            switch($boxtype) {
                case 'radio':
                    $string = $option[$value];
                    break;

                case 'checkbox':
                    $value_arr = explode(',',$value);
                    foreach($value_arr as $_v) {
                        if($_v) $string .= $option[$_v].' 、';
                    }
                    break;

                case 'select':
                    $string = $option[$value];
                    break;

                case 'multiple':
                    $value_arr = explode(',',$value);
                    foreach($value_arr as $_v) {
                        if($_v) $string .= $option[$_v].' 、';
                    }
                    break;
            }
            return $string;
        }
    }

	private function title($field, $value) {
		$value = p_htmlentities($value);
		return $value;
	}

    function topic($field, $value) {
        extract($this->fields[$field]['setting']);
        if($outputtype) {
            return $value;
        } else {
            $options = explode("\n",$options);
            foreach($options as $_k) {
                $v = explode("|",$_k);
                $k = trim($v[1]);
                $option[$k] = $v[0];
            }
            $string = '';
            switch($boxtype) {
                case 'radio':
                    $string = $option[$value];
                    break;

                case 'checkbox':
                    $value_arr = explode(',',$value);
                    foreach($value_arr as $_v) {
                        if($_v) $string .= $option[$_v].' 、';
                    }
                    break;

                case 'select':
                    $string = $option[$value];
                    break;

                case 'multiple':
                    $value_arr = explode(',',$value);
                    foreach($value_arr as $_v) {
                        if($_v) $string .= $option[$_v].' 、';
                    }
                    break;
            }
            return $string;
        }
    }

	private function relation($field, $value) {

		return $value;
	}

} ?>