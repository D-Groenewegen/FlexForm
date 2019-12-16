<?php
# @Author: Sen-Sai
# @Date:   15-05-2018
# @Last modified by:   Charlot
# @Last modified time: 04-07-2018 -- 10:03:36
# @License: Mine
# @Copyright: 2018

//error_reporting( -1 );
//ini_set( 'display_errors', 1 );




/**
 * Class WSFormHooks
 *
 * Hooks for WSForm extension
 *
 * @author Sen-Sai
 */
class WSFormHooks {



	/**
	 * List that returns an array of all WSForm hooks
	 *
	 * @return array with all WSForm hooks
	 */
	public static function availableHooks() {
		$data = array(
			'wsform',
			'wsfield',
			'wsfieldset',
			'wslegend',
			'wslabel',
			'wsselect',
			'wstoken',
			'wstoken2',
			'wsedit',
			'wscreate',
			'wsemail'
		);

		return $data;
	}


	/**
	 * Implements AdminLinks hook from Extension:Admin_Links.
	 *
	 * @param ALTree &$adminLinksTree
	 * @return bool
	 */
	public static function addToAdminLinks( ALTree &$adminLinksTree ) {
        global $wgServer;

		$generalSection = $adminLinksTree->getSection( wfMessage( 'adminlinks_general' )->text() );
		$extensionsRow = $generalSection->getRow( 'extensions' );
		if ( is_null( $extensionsRow ) ) {
			$extensionsRow = new ALRow( 'extensions' );
			$generalSection->addRow( $extensionsRow );
		}
		$extensionsRow->addItem( ALItem::newFromExternalLink( $wgServer.'/index.php/Special:WSForm/Docs', 'WSForm documentation' ) );
		return true;
	}

	/**
	 * MediaWiki hook when WSForm extension is initiated
	 *
	 * @param Parser $parser Sets a list of all WSForm hooks
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {

		include( 'classes/loader.php' );
		\wsform\classLoader::register();

		$parser->setHook( 'wsform', 'WSFormHooks::WSForm' );
		$parser->setHook( 'wsfield', 'WSFormHooks::WSField' );
		$parser->setHook( 'wsfieldset', 'WSFormHooks::WSFieldset' );
		$parser->setHook( 'wslegend', 'WSFormHooks::WSLegend' );
		$parser->setHook( 'wslabel', 'WSFormHooks::WSLabel' );
		$parser->setHook( 'wsselect', 'WSFormHooks::WSSelect' );
		$parser->setHook( 'wstoken', 'WSFormHooks::WSToken' );
        $parser->setHook( 'wstoken2', 'WSFormHooks::WSToken2' );
        $parser->setHook( 'wstoken3', 'WSFormHooks::WSToken3' );
		$parser->setHook( 'wsedit', 'WSFormHooks::WSEdit' );
		$parser->setHook( 'wscreate', 'WSFormHooks::WSCreate' );
		$parser->setHook( 'wsemail', 'WSFormHooks::WSEmail' );


	}






	/**
	 * @brief Function to render an input field.
	 *
	 * This function will look for the type of input field and will call its subfunction render_<inputfield>
	 *
	 * @param string $input Parser Between beginning and end
	 * @param array $args Arguments for the field
	 * @param Parser $parser MediaWiki Parser
	 * @param PPFrame $frame MediaWiki PPFrame
	 *
	 * @return array send to the MediaWiki Parser
	 * @return string send to the MediaWiki Parser with the message not a valid function
	 */
	public static function WSField( $input, array $args, Parser $parser, PPFrame $frame ) {
		if ( isset( $args['type'] ) ) {
			$type = $args['type'];

			if ( wsform\validate\validate::validInputTypes( $type ) ) {
                $parsePost = false;
                if( isset( $args['parsepost'] ) && isset( $args['name'] )) {
                    $parsePost = true;
                    $parseName = $args['name'];
                    unset( $args['parsepost'] );
                }
				$type = "render_" . $type;
				unset( $args['type'] );
				$noParse = false;
				if ( method_exists( 'wsform\field\render', $type ) ) {

					foreach ( $args as $k => $v ) {
						if ( ( strpos( $v, '{' ) !== false ) && ( strpos( $v, '}' ) !== false ) ) {
							$args[ $k ] = $parser->recursiveTagParse( $v, $frame );
						}
                        if( $k === 'noparse' ) {
                            $noParse = true;
                        }
                    }

					//Test to see if this gets parsed
                    if( $noParse === false ) {
                        $input = $parser->recursiveTagParse($input, $frame);
                    }
					//End test
					if ( $type == 'render_option' || $type == 'render_file' || $type == 'render_submit' || $type == 'render_text') {
						$ret = wsform\field\render::$type( $args, $input, $parser, $frame );
					} else {
						$ret = wsform\field\render::$type( $args, $input );
					}
				} else {
					$ret = $type . " is unkown";
				}

                if( $parsePost === true ) {
                    $ret .= '<input type="hidden" name="wsparsepost[]" value="' . $parseName . "\">\n";
                }

				return array( $ret, "markerType" => 'nowiki');
			}
		} else {
			return "Non valid fieldtype";
		}

	}

	/**
	 * @brief Function to render the Page Edit options.
	 *
	 * This function will call its subfunction render_edit()
	 *
	 * @param string $input Parser Between beginning and end
	 * @param array $args Arguments for the field
	 * @param Parser $parser MediaWiki Parser
	 * @param PPFrame $frame MediaWiki PPFrame
	 *
	 * @return array send to the MediaWiki Parser
	 * @return string send to the MediaWiki Parser with the message not a valid function
	 */
	public static function WSEdit( $input, array $args, Parser $parser, PPFrame $frame ) {

		foreach ( $args as $k => $v ) {
			if ( ( strpos( $v, '{' ) !== false ) && ( strpos( $v, "}" ) !== false ) ) {
				$args[ $k ] = $parser->recursiveTagParse( $v, $frame );
			}
		}

		$ret = wsform\edit\render::render_edit( $args );

		return array( $ret, 'noparse' => true, "markerType" => 'nowiki' );
	}


    /**
     * @brief Function to render the Page Create options.
     *
     * This function will call its subfunction render_create()
     *
     * @param string $input Parser Between beginning and end
     * @param array $args Arguments for the field
     * @param Parser $parser MediaWiki Parser
     * @param PPFrame $frame MediaWiki PPFrame
     *
     * @return array send to the MediaWiki Parser
     * @return string send to the MediaWiki Parser with the message not a valid function
     */
	public static function WSCreate( $input, array $args, Parser $parser, PPFrame $frame ) {

		foreach ( $args as $k => $v ) {
			if ( ( strpos( $v, '{' ) !== false ) && ( strpos( $v, "}" ) !== false ) ) {
				$args[ $k ] = $parser->recursiveTagParse( $v, $frame );
			}
		}
		$ret = wsform\create\render::render_create( $args );

		return array( $ret, 'noparse' => true, "markerType" => 'nowiki' );
	}



    /**
     * @brief Function to render the email options.
     *
     * This function will call its subfunction render_mail()
     *
     * @param string $input Parser Between beginning and end
     * @param array $args Arguments for the field
     * @param Parser $parser MediaWiki Parser
     * @param PPFrame $frame MediaWiki PPFrame
     *
     * @return array send to the MediaWiki Parser or
     * @return string send to the MediaWiki Parser with the message not a valid function
     */
	public static function WSEmail( $input, array $args, Parser $parser, PPFrame $frame ) {
		$args['content'] = base64_encode( $parser->recursiveTagParse( $input, $frame ) );
		foreach ( $args as $k => $v ) {
			if ( ( strpos( $v, '{' ) !== false ) && ( strpos( $v, "}" ) !== false ) ) {
				$args[ $k ] = $parser->recursiveTagParse( $v, $frame );
			}
		}
		$ret = wsform\mail\render::render_mail( $args );

		return array( $ret, 'noparse' => true, "markerType" => 'nowiki' );
	}

	/**
	 * @brief Function to render the Form itself.
	 *
	 * This function will call its subfunction render_form()
	 * It will also add the JavaScript on the loadscript variable
	 * \n Additional parameters
	 * \li loadscript
	 * \li showmessages
	 * \li restrictions
	 * \li no_submit_on_return
	 * \li action
	 * \li changetrigger
	 *
	 * @param string $input Parser Between beginning and end
	 * @param array $args Arguments for the field
	 * @param Parser $parser MediaWiki Parser
	 * @param PPFrame $frame MediaWiki PPFrame
	 *
	 * @return array send to the MediaWiki Parser or
	 * @return string send to the MediaWiki Parser with the message not a valid function
	 */
	public static function WSForm( $input, array $args, Parser $parser, PPFrame $frame ) {

		global $wgUser, $wgEmailConfirmToEdit, $IP, $wgScript;

		$ret = '';

		// Set i18n general messages
		wsform\wsform::$msg_unverified_email = wfMessage( "wsform-unverified-email1" )->text() . wfMessage( "wsform-unverified-email2" )->text();
		wsform\wsform::$msg_anonymous_user = wfMessage( "wsform-anonymous-user" )->text();

		// Do we have messages to show
		if ( isset( $args['showmessages'] ) ) {

			if ( isset ( $_COOKIE['wsform'] ) ) {

				$ret = '<div class="alert alert-' . $_COOKIE['wsform']['type'] . '">' . $_COOKIE['wsform']['txt'] . '</div>';
				setcookie( "wsform[type]", "", time() - 3600, '/' );
				setcookie( "wsform[txt]", "", time() - 3600, '/' );

				return array( $ret, 'noparse' => true, "markerType" => 'nowiki' );
			} else {
				return "";
			}
		}

		if ( isset( $args['restrictions'] ) && $args['restrictions'] == 'lifted' ) {
			$anon = true;
		} else {
			$anon = false;
		}

        if ( ! $wgUser->isLoggedIn() && $anon === false ) {
            $ret = wsform\wsform::$msg_anonymous_user;
            return $ret;
        }

		if ( isset( $args['loadscript'] ) && $args['loadscript'] !== '' ) {
			if(! wsform\wsform::isLoaded($args['loadscript'])) {
				if ( file_exists( $IP . '/extensions/WSForm/modules/customJS/loadScripts/' . $args['loadscript'] . '.js' ) ) {
					$ls = file_get_contents( $IP . '/extensions/WSForm/modules/customJS/loadScripts/' . $args['loadscript'] . '.js' );
					if ( $ls !== false ) {
						$loadScript = "<script>" . $ls . "</script>\n";
						wsform\wsform::addAsLoaded( $args['loadscript'] );
					}
				}
			}
		} else {
			$loadScript = false;
		}

/* No idea why this is in here, but makes no sense.
		if ( isset( $args['action'] ) && $args['action'] == 'get' ) {
			$anon = true;
		}
*/



		$noEnter = false;
		if ( isset( $args['no_submit_on_return'] ) ) {
			if(! wsform\wsform::isLoaded('keypress') ) {
				$noEnter = "<script>$(document).on('keyup keypress', 'form input[type=\"text\"]', function(e) {
            if(e.keyCode == 13) {
              e.preventDefault();
              return false;
            }
          });$(document).on('keyup keypress', 'form input[type=\"search\"]', function(e) {
            if(e.keyCode == 13) {
              e.preventDefault();
              return false;
            }
          });$(document).on('keyup keypress', 'form input[type=\"password\"]', function(e) {
            if(e.keyCode == 13) {
              e.preventDefault();
              return false;
            }
          })</script>";
                wsform\wsform::addAsLoaded( 'keypress' );
			}
		}

		if ( isset( $args['action'] ) && $args['action'] == 'addToWiki' && $anon === false ) {
			if ( $wgEmailConfirmToEdit === true && ! $wgUser->isEmailConfirmed() ) {
				$ret = wsform\wsform::$msg_unverified_email;

				return $ret;
			}
		}
        if ( isset( $args['changetrigger'] ) && $args['changetrigger'] !== '' && isset($args['id'])) {
            $onchange = "";
            $changeId = $args['id'];
            $changeCall = $args['changetrigger'];
            $onchange = "<script>$('#" . $changeId . "').change(" . $changeCall . "(this));</script>";
        } else $onchange = false;
		

		$output = $parser->recursiveTagParse( $input, $frame );
		foreach ( $args as $k => $v ) {
			if ( ( strpos( $v, '{' ) !== false ) && ( strpos( $v, "}" ) !== false ) ) {
				$args[ $k ] = $parser->recursiveTagParse( $v, $frame );
			}
		}
		if (wsform\wsform::getRun() === false) {
		    $realUrl = str_replace( '/index.php', '', $wgScript );
			$ret = '<script type="text/javascript" charset="UTF-8" src="' . $realUrl . '/extensions/WSForm/WSForm.general.js"></script>' . "\n";
			wsform\wsform::setRun(true);
		}
		$ret .= wsform\form\render::render_form( $args, $parser->getTitle()->getLinkURL() );
		$ret .= $output . '</form>';

		if ( $noEnter !== false ) {
			$ret = $ret . $noEnter;
		}
        if ( $onchange !== false ) {
            $ret = $ret . $onchange;
        }
        if( $loadScript !== false ) {
			$ret .= $loadScript;
        }

		return array( $ret, "markerType" => 'nowiki' );

	}

    /**
     * @brief This is the initial call from the MediaWiki parser for the WSFieldset
     *
     * @param string $input Received from parser from begin till end
     * @param array $args List of argmuments for the Fieldset
     * @param Parser $parser MediaWiki parser
     * @param PPFrame $frame MediaWiki pframe
     *
     * @return array with full rendered html for the parser to add
     */
	public static function WSFieldset( $input, array $args, Parser $parser, PPFrame $frame ) {
		$ret = '<fieldset ';
		foreach ( $args as $k => $v ) {
			if ( wsform\validate\validate::validParameters( $k ) ) {
				$ret .= $k . '="' . $v . '" ';
			}
		}
		$output = $parser->recursiveTagParse( $input, $frame );
		$ret    .= '>' . $output . '</fieldset>';

		return array( $ret, "markerType" => 'nowiki' );


	}
    /**
     * @brief This is the initial call from the MediaWiki parser for the WSSelect
     *
     * @param $input string Received from parser from begin till end
     * @param array $args List of argmuments for the selectset
     * @param Parser $parser MediaWiki parser
     * @param PPFrame $frame MediaWiki pframe
     *
     * @return array with full rendered html for the parser to add
     */
	public static function WSSelect( $input, array $args, Parser $parser, PPFrame $frame ) {

		$ret = '<select ';


		foreach ( $args as $k => $v ) {
			if ( wsform\validate\validate::validParameters( $k ) ) {
				if ( $k == "name" && strpos( $v, '[]' ) === false ) {
					$name = $v;
					$v    .= '[]';
				}
				$ret .= $k . '="' . $parser->recursiveTagParse( $v, $frame ) . '" ';
			}
		}
		$output = $parser->recursiveTagParse( $input, $frame );

		$ret .= '>' . $output . '</select>';

		return array( $ret, "markerType" => 'nowiki' );


	}

	/**
	 * @brief This is the initial call from the MediaWiki parser for the WSToken
	 *
	 * @param $input string Received from parser from begin till end
	 * @param array $args List of argmuments for the Fieldset
	 * @param Parser $parser MediaWiki parser
	 * @param PPFrame $frame MediaWiki pframe
	 *
	 * @return array with full rendered html for the parser to add
	 */
	public static function WSToken( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgOut, $IP, $wgDBname, $wgDBprefix;

        if( isset ( $wgDBprefix ) && !empty($wgDBprefix) ) {
            $prefix = '_' . $wgDBprefix;
        } else $prefix = '';

		$parser->disableCache();
		//$parser->getOutput()->addModules( 'ext.wsForm.select2.kickstarter' );
		$ret         = '<select data-inputtype="ws-select2"';
		$placeholder = false;
		$allowtags = false;
		$onlyone = false;
		foreach ( $args as $k => $v ) {
			if ( wsform\validate\validate::validParameters( $k ) ) {
				if ( $k == 'placeholder' ) {
					$placeholder = $parser->recursiveTagParse( $v, $frame );
				}    else {
					$ret .= $k . '="' . $parser->recursiveTagParse( $v, $frame ) . '" ';
				}
			}
		}

		$output = $parser->recursiveTagParse( $input );
		$id   = $parser->recursiveTagParse( $args['id'], $frame );
		$ret    .= '>' . $output . '</select>' . "\n";
		$out    = "";
		$out    .= '<input type="hidden" id="select2options-' . $id . '" value="';

		$out .= "$('#" . $id . "').select2({";

		$callb = '';

        $mwdb = $wgDBname . $prefix;

        if ( $placeholder !== false ) {
            $out .= "placeholder: '" . $placeholder . "',";
        }

		if ( isset( $args['json'] ) && isset( $args['id'] ) ) {
			if( strpos( $args['json'], 'semantic_ask' ) ) {
				$json = $args['json'];
			} else {
				$json = $parser->recursiveTagParse( $args['json'], $frame );
			}



			$out .= "\ntemplateResult: testSelect2Callback,\n";
			$out .= "\najax: { url: '" . $json . "', dataType: 'json',"."\n";
			$out .= "\ndata: function (params) { var queryParameters = { q: params.term, mwdb: '".$mwdb."' }\n";
			$out .= "\nreturn queryParameters; }}";
			$callb= '';
			if ( isset( $args['callback'] ) ) {
				if ( isset( $args['template'] ) ) {
					$templ = ", '" . $args['template'] . "'";
				} else $templ = '';
				$cb  = $parser->recursiveTagParse( $args['callback'], $frame );
				$callb = "$('#" . $id . "').on('select2:select', function(e) { " . $cb . "('" . $id . "'" . $templ . ")});\n";
				$callb = "$('#" . $id . "').on('select2:unselect', function(e) { " . $cb . "('" . $id . "'" . $templ . ")});\n";
			}
		}
		if( isset( $args['allowtags'] ) ) {
			if ( isset( $args['json'] ) && isset( $args['id'] ) ) {
				$out .= ",\ntags: true";
			} else {
				$out .= "\ntags: true";
			}
		}
		if( isset( $args['allowclear'] ) ) {
			if ( ( isset( $args['json'] ) && isset( $args['id'] ) ) || isset( $args['allowtags'] ) ) {
				$out .= ",\nallowClear: true";
			} else {
				$out .= "\nallowClear: true";
			}
		}

		$out .= '});"';
		$out .= $callb . ' />';
		if(isset($args['loadcallback'])) {
			if(! wsform\wsform::isLoaded($args['loadcallback'] ) ) {
				if ( file_exists( $IP . '/extensions/WSForm/modules/customJS/wstoken/' . $args['callback'] . '.js' ) ) {
					$lf  = file_get_contents( $IP . '/extensions/WSForm/modules/customJS/wstoken/' . $args['callback'] . '.js' );
					$out .= "<script>$lf</script>\n";
					wsform\wsform::addAsLoaded( $args['loadcallback'] );
				}
			}
		}
		$wgOut->addHTML( $out );


		return array( $ret, "markerType" => 'nowiki' );
	}

    /**
     * testing..
     */
    public static function WSToken2( $input, array $args, Parser $parser, PPFrame $frame ) {
        global $wgOut, $IP;
        $parser->disableCache();
        $end = "\n";
        //$parser->getOutput()->addModules( 'ext.wsForm.select2.kickstarter' );
        $ret         = '<select ';
        $placeholder = false;
        $allowtags = false;
        $onlyone = false;
        foreach ( $args as $k => $v ) {
            if ( self::validParameters( $k ) ) {
                if ( $k == 'placeholder' ) {
                    $placeholder = $parser->recursiveTagParse( $v, $frame );
                }    else {
                    $ret .= $k . '="' . $parser->recursiveTagParse( $v, $frame ) . '" ';
                }
            }
        }
        $output = $parser->recursiveTagParse( $input );
        $id   = $parser->recursiveTagParse( $args['id'], $frame );
        $ret .= '>' . $output . '</select>' . "\n";
        $css = "<style>".file_get_contents($IP.'/extensions/WSForm/modules/Selectr/dist/selectr.min.css')."</style>";
        $jsLoad = '<script type="text/javascript" charset="UTF-8" src="/extensions/WSForm/modules/Selectr/do-token.js"></script>'."\n";
        $javaScript = "<script>".$end."function doTokenSetup(){";

        if ( isset( $args['json'] ) && isset( $args['id'] ) ) {
            if( strpos( $args['json'], 'semantic_ask' ) ) {
                $json = $args['json'];
            } else {
                $json = $parser->recursiveTagParse( $args['json'], $frame );
            }

            //$json = $args['json'];
            //$json = Sanitizer::decodeChar($json);
            $javaScript .= "var selector = new Selectr(document.getElementById('". $id . "', {";
            $js2 = '';
            if ( $placeholder !== false ) {
                $javaScript .= "placeholder: '" . $placeholder . "',".$end;
            }
            //$out .= "\ntemplateResult: testSelect2Callback,\n";
            //$out .= "\najax: { url: '" . $json . "', dataType: 'json' }});"."\n";

            $javaScript .= '});'.$end.'}'.$end;

            if ( isset( $args['callback'] ) ) {
                if ( isset( $args['template'] ) ) {
                    $templ = ", '" . $args['template'] . "'";
                } else $templ = '';
                $cb  = $parser->recursiveTagParse( $args['callback'], $frame );
                $js2 = $end."selector.on('selectr.select', function(option) { ".$end . $cb . "('" . $id . "', option)});\n";
                $js2 .= $end."selector.on('selectr.deselect', function(option) { ".$end . $cb . "('" . $id . "', option)});\n";
                //$js2 = "$('#" . $id . "').on('select2:select', function(e) { " . $cb . "('" . $id . "'" . $templ . ")});\n";
                //$js2 .= "$('#" . $id . "').on('select2:unselect', function(e) { " . $cb . "('" . $id . "'" . $templ . ")});\n";
            }
        } else {
            $javaScript .= "var selector = new Selectr(document.getElementById('". $id . "'));".$end.'}'.$end;
            //$out .= "$('#" . $parser->recursiveTagParse( $args['id'], $frame ) . "').select2();" . "\n";
        }

        $javaScript .= $js2.'</script>';
        if(isset($args['loadcallback'])) {
            if(file_exists($IP.'/extensions/WSForm/modules/customJS/wstoken/'.$args['callback'].'.js')) {
                $lf = file_get_contents($IP.'/extensions/WSForm/modules/customJS/wstoken/'.$args['callback'].'.js');
                $javaScript .="<script>$lf</script>\n";
            }
        }
        //$wgOut->addHTML( $javaScript );
        $ret = $css . $jsLoad . $ret . $javaScript;
        //echo "<BR><BR><BR><pre>";
        //echo $ret;
        //echo "</pre>";
        return array( $ret, "markerType" => 'nowiki' );
        //return array($ret, "noparse" => 'true', 'isHTML' => true);

    }

    /**
     * testing..
     */
    public static function WSToken3( $input, array $args, Parser $parser, PPFrame $frame ) {
        global $wgOut, $IP;
        $parser->disableCache();
        $end = "\n";
        $ret         = '<input ';
        $placeholder = false;
        $allowtags = false;
        $onlyone = false;
        foreach ( $args as $k => $v ) {
            if ( self::validParameters( $k ) ) {
                if ( $k == 'placeholder' ) {
                    $placeholder = $parser->recursiveTagParse( $v, $frame );
                }    else {
                    $ret .= $k . '="' . $parser->recursiveTagParse( $v, $frame ) . '" ';
                }
            }
        }
        $output = $parser->recursiveTagParse( $input );
        $id   = $parser->recursiveTagParse( $args['id'], $frame );
        $ret .= '>' . $output . '</select>' . "\n";
        $css = "<style>".file_get_contents($IP.'/extensions/WSForm/modules/tokens3/jquery.inputpicker.css')."</style>";
        $jsLoad = '<script type="text/javascript" charset="UTF-8" src="/extensions/WSForm/modules/tokens3/do-token.js"></script>'."\n";
        $javaScript = "<script>".$end."function doTokenSetup(){ " . $end;
        $javaScript .= "$('#". $id . "').inputpicker({ //";
        if ($placeholder !== false) {
            $javaScript .= ",\nplaceholder: '" . $placeholder . "'";
        }
        if ( isset( $args['json'] ) && isset( $args['id'] ) ) {
            if (strpos($args['json'], 'semantic_ask')) {
                $json = $args['json'];
            } else {
                //$json = $parser->recursiveTagParse($args['json'], $frame);
                $json = $args['json'];
            }

            $js2 = '';

            $javaScript .= ",\nurl: '" . $json . "'";
            $javaScript .= ",\nfields: ['id', 'name', 'hasc'] ";
            $javaScript .= ",\nfieldText: 'name'";
            $javaScript .= ",\nfieldValue: 'id'";
            $javaScript .= ",\nfilterOpen: true";
            $javaScript .= ",\nselectMode: 'creatable'";
            $javaScript .= ",\nmultiple: true";
            $javaScript .= ",\nheadShow: true";

        }
        $javaScript .= $end.'});' . $end . '}' . $end;
        if ( isset( $args['callback'] ) ) {
            if ( isset( $args['template'] ) ) {
                $templ = ", '" . $args['template'] . "'";
            } else $templ = '';
            $cb  = $parser->recursiveTagParse( $args['callback'], $frame );
            $js2 = $end."$('#".$id."').change(function(input) { ".$end . $cb . "('" . $id . "', input)});\n";

        }


        $javaScript .= $js2.'</script>';
        if(isset($args['loadcallback'])) {
            if(file_exists($IP.'/extensions/WSForm/modules/customJS/wstoken/'.$args['callback'].'.js')) {
                $lf = file_get_contents($IP.'/extensions/WSForm/modules/customJS/wstoken/'.$args['callback'].'.js');
                $javaScript .="<script>$lf</script>\n";
            }
        }
        $ret = $css . $jsLoad . $ret . $javaScript;
        return array( $ret, "markerType" => 'nowiki' );

    }


    /**
     * @brief renderes the html legend (for use with fieldset)
     *
     * @param string $input Received from parser from begin till end
     * @param array $args List of argmuments for the Legend
     * @param Parser $parser MediaWiki parser
     * @param PPFrame $frame MediaWiki pframe
     *
     * @return array with full rendered html for the parser to add
     */
	public static function WSLegend( $input, array $args, Parser $parser, PPFrame $frame ) {
		$ret = '<legend ';
		if ( isset( $args['class'] ) ) {
			$ret .= ' class="' . $args['class'] . '" ';
		}
		if ( isset( $args['align'] ) ) {
			$ret .= ' align="' . $args['align'] . '"';
		}
		$ret .= '>' . $input . '</legend>';

		return array( $ret, "markerType" => 'nowiki' );

	}

    /**
     * @brief renders the html label
     *
     * @param string $input Received from parser from begin till end
     * @param array $args List of arguments for a Label
     * @param Parser $parser MediaWiki parser
     * @param PPFrame $frame MediaWiki pframe
     *
     * @return array with full rendered html for the parser to add
     */
	public static function WSLabel( $input, array $args, Parser $parser, PPFrame $frame ) {
		$ret = '<label ';
		foreach ( $args as $k => $v ) {
			if ( wsform\validate\validate::validParameters( $k ) ) {
                if ( ( strpos( $v, '{' ) !== false ) && ( strpos( $v, '}' ) !== false ) ) {
                    $v = $parser->recursiveTagParse( $v, $frame );
                }
				$ret .= $k . '="' . $v . '" ';
			}
		}

		$output = $parser->recursiveTagParse( $input, $frame );
		$ret    .= '>' . $output . '</label>';

		return array( $ret, "markerType" => 'nowiki' );

	}


	/**
	 * Converts an array of values in form [0] => "name=value" into a real
	 * associative array in form [name] => value. If no = is provided,
	 * true is assumed like this: [name] => true
	 *
	 * @param array $options
	 *
	 * @return array $results
	 */
	public static function extractOptions( array $options ) {
		$results = array();
		foreach ( $options as $option ) {
			$pair = explode( '=', $option, 2 );
			if ( count( $pair ) === 2 ) {
				$name             = trim( $pair[0] );
				$value            = trim( $pair[1] );
				$results[ $name ] = $value;
			}

			if ( count( $pair ) === 1 ) {
				$name             = trim( $pair[0] );
				$results[ $name ] = true;
			}
		}
		return $results;
	}

}
