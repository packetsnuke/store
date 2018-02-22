<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

/**
 * HTML creation class.
 */
final class TM_EPO_HTML_base {

	protected static $_instance = NULL;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function __construct( $args = array() ) {
	}

	/**
	 *    Ouputs a fontawesome icon
	 */
	public function tm_icon( $id = "", $echo = 1 ) {
		$id = "<i class='tcfa tcfa-$id'></i>";
		if ( $echo != 1 ) {
			return $id;
		} else {
			echo $id;
		}
	}

	/**
	 *    Creates an image tag
	 */
	public function tm_make_img( $path, $alt = "", $imagename = "", $border = 0, $align = "middle", $extra = "" ) {
		$idtag = ($imagename !== "") ? " id=\"$imagename\"" : "";
		$nametag = ($imagename !== "") ? " name=\"$imagename\"" : "";
		$altttag = " alt=\"$alt\"";
		$titletag = ($alt !== "") ? " title=\"$alt\"" : "";
		$bbordertag = ($border !== "") ? " border=\"$border\"" : "";
		$aligntag = ($align !== "") ? " align=\"$align\"" : "";
		$extratag = ($extra !== "") ? " $extra" : "";
		$tag = $idtag . $nametag . $altttag . $titletag . $bbordertag . $aligntag . $extratag;
		$img = "<img src=\"$path\" $tag />";

		return $img;
	}

	/**
	 *    Creates a button
	 */
	public function tm_make_button( $args, $e = 0 ) {
		if ( empty( $args ) || !is_array( $args ) ) {
			return;
		}
		$img = "";
		if ( isset( $args["img"] ) && is_array( $args["img"] ) ) {
			$extra = "vertical-align:middle;width:" . $args["img"][1] . ";height:" . $args["img"][2] . ";";
			if ( isset( $args["imgstyle"] ) ) $extra = $args["imgstyle"];
			$img = tm_make_img( $args["img"][0], "", "", 0, "", "style=\"$extra\"" );
		}
		$icon = "";
		if ( isset( $args["icon"] ) ) {
			$icon = tm_icon( $args["icon"], 0 );
		}
		$tags = array();
		if ( is_array( $args["tags"] ) ) {
			if ( !isset( $args["tags"]['type'] ) ) {
				$args["tags"]['type'] = "button";
			}
			foreach ( $args["tags"] as $k => $v ) {
				$tags[] = "$k='$v'";
			}
		}

		$tags = join( " ", $tags );
		$a = "<button $tags>" . $img . $icon . $args["text"] . "</button>";
		if ( empty( $e ) ) {
			return $a;
		} else {
			echo $a;
		}
	}

	/**
	 *    Creates a select box
	 */
	public function tm_make_select( $selectArray, $optionArray, $selectedvalue = "/n", $label = 1, $ech = 1 ) {
		if ( !is_array( $selectArray ) ) {
			return "";
		}
		if ( !is_array( $optionArray ) ) {
			return "";
		}

		$multiple = (isset( $selectArray['multiple'] )) ? " multiple='multiple'" : "";
		$size = (isset( $selectArray['size'] )) ? " size='" . $selectArray['size'] . "'" : "";
		$class = (!empty( $selectArray['class'] )) ? " class='" . $selectArray['class'] . "'" : "";
		$id = (!empty( $selectArray['id'] )) ? " id='" . $selectArray['id'] . "'" : "";
		$name = (!empty( $selectArray['name'] )) ? " name='" . $selectArray['name'] . "'" : "";
		$extra = (!empty( $selectArray['extra'] )) ? " " . $selectArray['extra'] : "";

		$returnstr = "";
		if ( !empty( $id ) && !empty( $label ) ) {
			$returnstr = "<label for='" . $selectArray['id'] . "'>";
		}
		$returnstr .= "<select" . $class . $id . $name . $size . $multiple . $extra . ">";
		for ( $i = 0; $i < count( $optionArray ); $i++ ) {
			$sel = "";
			if ( $selectedvalue != "/n" && !is_array( $selectedvalue ) ) {
				if ( $selectedvalue == $optionArray[ $i ]['value'] ) {
					$sel = " selected='selected'";
				}
			} else {
				if ( is_array( $selectedvalue ) && in_array( $optionArray[ $i ]['value'], $selectedvalue ) ) {
					$sel = " selected='selected'";
				}
			}
			$oat1 = (isset( $optionArray[ $i ]['title'] )) ? " title='" . $optionArray[ $i ]['title'] . "'" : "";
			$oat2 = (isset( $optionArray[ $i ]['id'] )) ? " id='" . $optionArray[ $i ]['id'] . "'" : "";
			$oat3 = (isset( $optionArray[ $i ]['class'] )) ? " class='" . $optionArray[ $i ]['class'] . "'" : "";
			$oat4 = (isset( $optionArray[ $i ]['extra'] )) ? " " . $optionArray[ $i ]['extra'] : "";

			$returnstr .= "<option" . $oat1 . $oat2 . $oat3 . $oat4 . $sel . " value='" . $optionArray[ $i ]['value'] . "'>" . $optionArray[ $i ]['text'] . "</option>";
		}
		$returnstr .= "</select>";
		if ( !empty( $id ) && !empty( $label ) ) {
			$returnstr .= "</label>";
		}
		if ( $ech == 1 ) {
			echo $returnstr;
		} else {
			return $returnstr;
		}
	}

	/**
	 *    Creates a form field
	 */
	public function tm_make_field( $args, $e = 0 ) {
		if ( !is_array( $args ) ) {
			return;
		}
		if ( isset( $args["noecho"] ) ) {
			return;
		}
		$a = "";
		$tags = array();
		if ( isset( $args["tags"] ) && is_array( $args["tags"] ) ) {
			if ( isset( $args["type"] ) && ($args["type"] == "range" || $args["type"] == "text" || $args["type"] == "number" || $args["type"] == "hidden") ) {
				$args["tags"]["value"] = $args["default"];
				if ( $args["type"] == "number" ) {
					if ( !isset( $args["tags"]["step"] ) ) {
						$args["tags"]["step"] = "any";
					}
				}
			}
			if ( isset( $args["type"] ) && $args["type"] == "range" && !isset( $args["tags"]["class"] ) ) {
				$args["tags"]["class"] = "range";
			} elseif ( isset( $args["type"] ) && $args["type"] == "range" && isset( $args["tags"]["class"] ) ) {
				$args["tags"]["class"] = "range " . $args["tags"]["class"];
			}
			if ( isset( $args["tags"]["value"] ) ) {
				$args["tags"]["value"] = esc_attr( $args["tags"]["value"] );
			}
			$args["tags_original"] = $args["tags"];
			foreach ( $args["tags"] as $k => $v ) {
				$tags[] = "$k='$v'";
			}
		}
		if ( !empty( $args["disabled"] ) ) {
			if ( isset( $args["message0x0_class"] ) ) {
				$args["message0x0_class"] .= ' tm-setting-row-disabled';
			} else {
				$args["message0x0_class"] = 'tm-setting-row-disabled';
			}
			if ( isset( $args["tags"] ) && is_array( $args["tags"] ) ) {
				if ( isset( $args["tags"]["class"] ) ) {
					$args["tags"]["class"] .= ' tm-wmpl-disabled';
				} else {
					$args["tags"]["class"] = 'tm-wmpl-disabled';
				}
			}
		}

		$tags = join( " ", $tags );
		$divid = $divstyle = "";
		if ( isset( $args["divid"] ) ) {
			$divid = ' id="' . $args["divid"] . '"';
		}
		if ( isset( $args["divstyle"] ) ) {
			$divstyle = ' style="' . $args["divstyle"] . '"';
		}
		if ( isset( $args["tags"] ) ) {
			if ( empty( $args["nodiv"] ) ) {
				if ( empty( $args["nostart"] ) ) {
					if ( empty( $args["nowrap_start"] ) ) {
						$a .= '<div' . $divid . $divstyle . ' class="message0x0 tc-clearfix' . (isset( $args["message0x0_class"] ) ? " " . $args["message0x0_class"] : "") . '">';
					}
					if ( !empty( $args["nowrap_start"] ) && !empty( $args["noclear"] ) ) {
						$a .= "<div class=\"clear\">&nbsp;</div>";
					}
					if ( isset( $args["wrap_div"] ) && is_array( $args["wrap_div"] ) ) {
						$_wrap_div = array();
						foreach ( $args["wrap_div"] as $k => $v ) {
							$_wrap_div[] = "$k='$v'";
						}
						$_wrap_div = join( " ", $_wrap_div );
						$a .= '<div ' . $_wrap_div . '>';
					}
					if ( empty( $args["nolabel"] ) && !empty( $args["label"] ) ) {
						$a .= '<div class="message2x1' . (!empty( $args["leftclass"] ) ? " " . $args["leftclass"] : "") . '"><label for="' . $args["tags"]["id"] . '"><span>' . $args["label"] . '</span></label>';
						if ( !empty( $args["desc"] ) ) {
							$a .= "<div class='messagexdesc'>";
							$a .= $args["desc"] . "</div>";
						}
						$a .= '</div>';
					}
				} else {
					$a .= '<label for="' . $args["tags"]["id"] . '"><span>' . $args["label"] . '</span></label>';
				}
			}
		} else {
			if ( empty( $args["nodiv"] ) ) {
				if ( empty( $args["nostart"] ) ) {
					if ( empty( $args["nowrap_start"] ) ) {
						$a .= '<div' . $divid . $divstyle . ' class="message0x0 tc-clearfix' . (isset( $args["message0x0_class"] ) ? " " . $args["message0x0_class"] : "") . '">';
					}
					if ( !empty( $args["nowrap_start"] ) ) {
						$a .= "<div class=\"clear\">&nbsp;</div>";
					}
					if ( isset( $args["wrap_div"] ) && is_array( $args["wrap_div"] ) ) {
						$_wrap_div = array();
						foreach ( $args["wrap_div"] as $k => $v ) {
							$_wrap_div[] = "$k='$v'";
						}
						$_wrap_div = join( " ", $_wrap_div );
						$a .= '<div ' . $_wrap_div . '>';
					}
					if ( empty( $args["nolabel"] ) && !empty( $args["label"] ) ) {
						$a .= '<div class="message2x1' . (!empty( $args["leftclass"] ) ? " " . $args["leftclass"] : "") . '"><span>' . $args["label"] . '</span>';
						if ( !empty( $args["desc"] ) ) {
							$a .= "<div class='messagexdesc'>";
							$a .= $args["desc"] . "</div>";
						}
						$a .= '</div>';
					}
				}
			}
		}
		if ( empty( $args["nodiv"] ) && empty( $args["nostart"] ) ) {
			$a .= "<div class='message2x2" . (!empty( $args["rightclass"] ) ? " " . $args["rightclass"] : "") . "'>";
		}
		if ( isset( $args["prepend_element_html"] ) ) {
			$a .= $args["prepend_element_html"];
		}
		$disabled = '';
		if ( !empty( $args["disabled"] ) ) {
			$disabled = 'disabled="disabled" ';
		}
		if ( !empty( $args["html_before_field"] ) ) {
			$a .= $args["html_before_field"];
		}
		if ( isset( $args["type"] ) ) {
			switch ( $args["type"] ) {
				case "custom":
					$a .= isset( $args["html"] ) ? $args["html"] : "";
					break;
				case "hidden":
					$a .= "<input " . $disabled . "type='hidden' $tags />";
					break;
				case "text":
				case "number":
					$a .= "<input " . (isset( $args["style"] ) ? $args["style"] : "") . " " . $disabled . "type=\"" . $args["type"] . "\" $tags />";
					break;
				case "range":
					$a .= "<div class=\"rangewrapper\"><input " . $disabled . "type=\"text\" $tags /></div>";
					break;

				case "textarea":
					$a .= "<textarea " . (isset( $args["style"] ) ? " " . $args["style"] : "") . " " . $disabled . $tags . ">" . $args["default"] . "</textarea>";
					break;
				case "checkbox":
					$sel = ($args["default"] == $args["tags"]["value"]) ? " checked='checked'" : "";
					$a .= "<input " . $disabled . "type=\"checkbox\" $tags $sel />";
					break;
				case "radio":
					foreach ( $args["options"] as $tx => $vl ) {
						$sel = ($args["default"] == $vl["value"]) ? " checked='checked'" : "";
						$idd = 'id="' . $args["tags"]["id"] . $tx . '" ';
						$a .= "<label for='" . $args["tags"]["id"] . $tx . "'><input " . $idd . "type=\"radio\" " . $disabled . "name=\"" . $args["tags"]["name"] . "\" value=\"" . $vl["value"] . "\" $sel />&nbsp;" . $vl["text"] . "</label>";
						$a .= "<br />";
					}
					break;
				case "select":
					$selectArray = array(
						"class" => isset( $args["tags"]["class"] ) ? $args["tags"]["class"] : "",
						"id"    => $args["tags"]["id"],
						"name"  => $args["tags"]["name"],
						"extra" => (isset( $args["style"] ) ? $disabled . $args["style"] : $disabled . "style=\"max-width:100%;min-width:20%;\"") );
					if ( isset( $args["multiple"] ) ) {
						$selectArray["multiple"] = $args["multiple"];
					}
					if ( isset( $args["size"] ) ) {
						$selectArray["size"] = $args["size"];
					}
					$a .= $this->tm_make_select( $selectArray, $args["options"], $args["default"], 0, 0 );
					break;
				case "select2":
					$selectArray = array(
						"class" => $args["tags"]["class"],
						"id"    => $args["tags"]["id"],
						"name"  => $args["tags"]["name"] );
					if ( !empty( $disabled ) ) {
						$selectArray["extra"] = $disabled;
					}
					if ( isset( $args["multiple"] ) ) {
						$selectArray["multiple"] = $args["multiple"];
					}
					if ( isset( $args["size"] ) ) {
						$selectArray["size"] = $args["size"];
					}
					$a .= $this->tm_make_select( $selectArray, $args["options"], $args["default"], 1, 0 );
					break;
				case "select3":
					$selectArray = array(
						"class" => isset( $args["tags"]["class"] ) ? $args["tags"]["class"] : "",
						"id"    => $args["tags"]["id"],
						"name"  => $args["tags"]["name"],
						"extra" => (isset( $args["style"] ) ? $disabled . $args["style"] : $disabled . "") );
					if ( isset( $args["multiple"] ) ) {
						$selectArray["multiple"] = $args["multiple"];
					}
					if ( isset( $args["size"] ) ) {
						$selectArray["size"] = $args["size"];
					}
					$a .= '<span class="select_wrap' . (isset( $args["multiple"] ) ? ' multiple' : '') . '"><span class="select_style">' . $this->tm_make_select( $selectArray, $args["options"], $args["default"], 0, 0 )
						. '<span class="select_value"></span><span class="select_icon"></span></span></span>';
					break;
			}
		}
		if ( !empty( $args["html_after_field"] ) ) {
			$a .= $args["html_after_field"];
		}
		if ( isset( $args["extra"] ) ) {
			$a .= $args["extra"];
		}
		if ( empty( $args["nodiv"] ) && empty( $args["noend"] ) ) {
			$a .= "</div>";
			if ( isset( $args["wrap_div"] ) && is_array( $args["wrap_div"] ) ) {
				$a .= "</div>";
			}
			if ( empty( $args["nowrap_end"] ) ) {
				$a .= "</div>";
			}
		}
		if ( empty( $e ) ) {
			return $a;
		} else {
			echo $a;
		}
	}

}


