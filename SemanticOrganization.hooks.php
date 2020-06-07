<?php
/**
 * Hooks for the Semantic Organization Extension
 */
class SemanticOrganizationHooks {

	static $options;
	static $listvalues = array( 'email', 'homepage', 'workphone', 'tag' );

	/**
	 *  Enforce use of Bootstrap 4, apply custom styles
	 */
	static function onBeforeInitialize( \Title &$title, \Article &$article = null, \OutputPage &$output, \User &$user, \WebRequest $request, \MediaWiki $mediaWiki ) {
		$GLOBALS['wgTweekiSkinUseBootstrap4'] = true;
		$GLOBALS['wgTweekiSkinUseExternallinkStyles'] = false;

		if( $GLOBALS['wgSemorgUseCustomTweekiFiles'] !== false ) {
			$GLOBALS['wgTweekiSkinCustomScriptModule'] = 'ext.semorg.tweeki.scripts';
			$GLOBALS['wgTweekiSkinCustomStyleModule'] = 'ext.semorg.tweeki.styles';
		}
	}

	/**
	 * Setup
	 */
	static function onParserFirstCallInit( &$parser ) {
		$parserfunctions = [
			'person' => 'renderPerson',
			'person-ref' => 'renderPersonReference',
			'form' => 'renderForm',
			'field' => 'renderField',
			'fields' => 'renderFields',
			'field-rows' => 'renderFieldRows',
			'set' => 'set',
			'set-list' => 'setList',
			'table' => 'renderDetailTable',
			'subobject' => 'subobject',
			'network' => 'network',
			'cooperation' => 'cooperation',
			'circles' => 'circles',
			'toggle' => 'toggle',
			'list' => 'renderList',
			'user-create' => 'renderUserCreateLink',
			'formlink' => 'renderFormlink',
			'forminput' => 'renderForminput',
			'meetings' => 'renderMeetings',
			'properties' => 'renderProperties',
			'values' => 'renderValues',
			'tabs' => 'renderTabs',
			'tabs-card' => 'renderTabsCard',
			'user' => 'getUser',
			'true' => 'isTrue',
			'detail' => 'renderDetail',
			'documentation' => 'renderDocumentation',
			'schedule' => 'renderSchedule',
			'dashboard' => 'renderDashboard',
			'role-link' => 'renderRoleLink',
			'datediff' => 'getDateDiff',
			'datediff-human' => 'getDateDiffHuman',
			'round' => 'getRound',
		];
		foreach( $parserfunctions as $key => $method ) {
			$parser->setFunctionHook( 'semorg-' . $key, 'SemanticOrganizationHooks::' . $method );
		}
	}


	/**
	 * Load resources
	 */
	static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$out->addModules( [ 'ext.semorg' ] );
	}


	/**
	 * Get name of the current user
	 */
	static function getUser( &$parser ) {
		return $parser->getUser()->getUserPage()->getFullText();
	}


	/**
	 * Return difference of two given datetimes
	 *
	 * @param String First date
	 * @param String Second date
	 * @param String Unit (default: hours)
	 */
	static function getDateDiff( &$parser ) {
		$start = new Datetime( func_get_arg( 1 ) );
		$end = new Datetime( func_get_arg( 2 ) );

		if( func_num_args() > 3 ) {
			switch( func_get_arg( 3 ) ) {
				case 'days':
					$divisor = 60 * 60 * 24;
					break;
				case 'hours':
					$divisor = 60 * 60;
					break;
				case 'minutes':
					$divisor = 60;
					break;
				case 'seconds':
					$divisor = 1;
					break;
			}
		} else {
			$divisor = 60 * 60;
		}

		$diff = round( ( $end->format('U') - $start->format('U') ) / $divisor );

		return $diff;
	}


	/**
	 * Return difference of a given datetime to the current time as readable for humans (“x ago”)
	 *
	 * @param String Date
	 */
	static function getDateDiffHuman( &$parser ) {
		$start = new Datetime( func_get_arg( 1 ) );
		$end = new Datetime();

		$diff = ( $end->format('U') - $start->format('U') ) / ( 60*60*24 );
		if( $diff < 1 ) {
			$diff_human = wfMessage( 'semorg-datediff-human-recently' )->plain();
		} elseif( $diff < 60 ) {
			$diff_human = wfMessage( 'semorg-datediff-human-days', round( $diff ) )->plain();
		} elseif( $diff < 730 ) {
			$diff_human = wfMessage( 'semorg-datediff-human-months', round( $diff/30 ) )->plain();
		} else {
			$diff_human = wfMessage( 'semorg-datediff-human-years', round( $diff/365 ) )->plain();
		}

		return $diff_human;
	}


	/**
	 * Round a number
	 */
	static function getRound( &$parser ) {
		$lang = $parser->getTargetLanguage()->getCode();
		setlocale( LC_MONETARY, $lang );
		setlocale( LC_MONETARY, 'de_DE' );
		$currency_string = money_format( '%.2n', func_get_arg( 1 ) );
		return $currency_string;
		$precision = 0;
		if( func_num_args() > 2 ) {
			$precision = func_get_arg( 2 );
		}

		$rounded = round( func_get_arg( 1 ), $precision );

		return $rounded;
	}


	/**
	 * Test whether first argument evaluates to Boolean true;
	 * return second argument if true, third argument if false
	 */
	static function isTrue( &$parser ) {
		$return = '';
		$true_words = explode( ',', wfMessage( 'Smw true words' )->plain() );
		$test_word = strtolower( func_get_arg( 1 ) );
		if( in_array( $test_word, $true_words ) ) {
			if( func_num_args() > 2 ) {
				$return = func_get_arg( 2 );
			}
		} else {
			if( func_num_args() > 3 ) {
				$return = func_get_arg( 3 );
			}
		}
		return $return;
	}


	/**
	 * Render toggle
	 */
	static function toggle( &$parser ) {
		$keyvalues = self::extractOptions( array_slice(func_get_args(), 1) );

		if( !isset( $keyvalues['class'] ) ) {
			return 'parameter <code>class</code> missing';
		}
		if( !isset( $keyvalues['original-text'] ) ) {
			return 'parameter <code>original-text</code> missing';
		}
		if( !isset( $keyvalues['toggle-text'] ) ) {
			return 'parameter <code>toggle-text</code> missing';
		}

		$originalLink = '<button class="btn btn-secondary btn-xs semorg-toggle-original">' . $keyvalues['original-text'] . '</button>';
		$toggleLink = '<button class="btn btn-secondary btn-xs semorg-toggle-toggle" style="display:none">' . $keyvalues['toggle-text'] . '</button>';
		$toggle = '<div class="semorg-toggle" data-semorg-toggle="' . $keyvalues['class'] . '">' . $originalLink . $toggleLink . '</div>';

		return array( $toggle, 'noparse' => true, 'isHTML' => true );
	}


	/**
	 * Render detail table
	 */
	static function renderDetailTable( &$parser ) {
		$template = func_get_args()[1];
		$keyvalues = self::extractOptions( array_slice(func_get_args(), 2) );

		$table = '';

		foreach( $keyvalues as $key => $value ) {
			$fullelement = $template . '-' . $key;
			$table .= '<div class="row semorg-details-row">';
			$table .= '<div class="col-lg-4 semorg-details-field-name">{{semorg-field-name|' . $fullelement . '}}</div>';
			$table .= '<div class="col-lg-8">
';
			$table .= is_array( $value ) ? implode( ', ', $value ) : $value;
			$table .= '
</div>';
 			$table .= '</div>';
		}

		$table = '<div class="semorg-detail-table semorg-details container-fluid">' . $table . '</div>';

		return [ $table, 'noparse' => false ];
	}


	/**
	 * Render link to create a new user account if it doesn't exist already
	 */
	static function renderUserCreateLink( &$parser ) {
		$usertitle = func_get_args()[1];
		$title = Title::newFromText( $usertitle, NS_USER );
		$username = $title->getText();

		if( is_null( $user = User::idFromName( $username ) ) ) {
			$linktext = wfMessage( 'semorg-user-create-link-text', $username )->plain();
			$link = '<div class="semorg-user-create-link">[{{fullurl:Special:CreateAccount|wpName=' . urlencode( $username ) . '&wpCreateaccountMail=true&email={{#show:' . $title . '|?semorg-person-email#}}}} ' . $linktext . ']</div>';
			return [ $link, 'noparse' => false ];
		} else {
			return false;
		}
	}


	/**
	 * Render formlink
	 */
	static function renderFormlink( &$parser ) {
		$template = func_get_args()[1];
		$options = self::extractOptions( array_slice(func_get_args(), 2) );

		if( wfMessage('semorg-' . $template . '-form')->exists() ) {
			$form = wfMessage('semorg-' . $template . '-form')->plain();
		} else {
			$form = 'semorg-' . $template;
		}

		$parameters = ['form' => $form ];
		$messages = [
			'link text' => 'link-text',
			'link type' => 'link-type',
			'target' => 'target',
			'tooltip' => 'tooltip',
			'popup' => 'popup',
			'new window' => 'new-window',
			'query string' => 'query-string',
			'returnto' => 'returnto',
			'preload' => 'preload',
		];
		foreach( $messages as $option => $message_key ) {
			if( isset( $options[$option] ) ) {
				$parameters[$option] = $options[$option];
			} elseif( wfMessage('semorg-formlink-' . $template . '-' . $message_key)->exists() ) {
				$parameters[$option] = wfMessage('semorg-formlink-' . $template . '-' . $message_key)->plain();
			}
		}

		if( !isset( $parameters['link text'] ) ) {
			if( wfMessage('semorg-' . $template . '-entity-name' )->exists() ) {
				$entity_name = wfMessage('semorg-' . $template . '-entity-name')->plain();
				$parameters['link text'] = wfMessage('semorg-formlink-generic-link-text', $entity_name)->plain();
			} else {
				$parameters['link text'] = '';
			}
		}
		$class = $options['class'] ?? 'btn btn-secondary btn-sm d-print-none';
		$parameters['link text'] = '<span class="' . $class . '">' . $parameters['link text'] . '</span>';

		$formlink = '{{#formlink:';
		foreach( $parameters as $parameter => $value ) {
			$formlink .= '|' . $parameter . '=' . $value;
		}
		$formlink .= '}}';

		return [ $formlink, 'noparse' => false ];
	}

	/**
	 * Render forminput
	 */
	static function renderForminput( &$parser ) {
		$template = func_get_args()[1];
		$options = self::extractOptions( array_slice(func_get_args(), 2) );

		if( wfMessage('semorg-' . $template . '-form')->exists() ) {
			$form = wfMessage('semorg-' . $template . '-form')->plain();
		} else {
			$form = 'semorg-' . $template;
		}

		$parameters = ['form' => $form ];
		$messages = [
			'size' => 'size',
			'default value' => 'default-value',
			'button text' => 'button-text',
			'query string' => 'query-string',
			'autocomplete on category' => 'autocomplete-on-category',
			'autocomplete on namespace' => 'autocomplete-on-namespace',
			'placeholder' => 'placeholder',
			'popup' => 'popup',
			'returnto' => 'returnto'
		];
		foreach( $messages as $option => $message_key ) {
			if( isset( $options[$option] ) ) {
				$parameters[$option] = $options[$option];
			} elseif( wfMessage('semorg-forminput-' . $template . '-' . $message_key)->exists() ) {
				$parameters[$option] = wfMessage('semorg-forminput-' . $template . '-' . $message_key)->plain();
			}
		}

		$forminput = '{{#forminput:';
		foreach( $parameters as $parameter => $value ) {
			$forminput .= '|' . $parameter . '=' . $value;
		}
		$forminput .= '|no autofocus}}';

		return [ $forminput, 'noparse' => false ];
	}


	/**
	 * Set semantic values
	 *
	 * @todo: create a solution that works with separators
	 */
	static function set( &$parser ) {
		$template = func_get_args()[1];
		$keyvalues = self::extractOptions( array_slice(func_get_args(), 2) );

		$set = '';

		foreach( $keyvalues as $key => $value ) {
			if( is_array( $value ) ) {
				$set .= '|semorg-' . $template . '-' . $key . '=' . implode( ',', $value ) . '|+sep=,';
			} else {
				$set .= '|semorg-' . $template . '-' . $key . '=' . $value;
			}
		}

		$set = '{{#set:' . $set . '}}';

		return [ $set, 'noparse' => false ];

	}


	/**
	 * Set semantic value lists
	 *
	 * @todo: would be unnecessary if set() would work with separators
	 */
	static function setList( &$parser ) {
		$template = func_get_args()[1];
		$keyvalues = self::extractOptions( array_slice(func_get_args(), 2) );

		$set = '';

		foreach( $keyvalues as $key => $value ) {
			$set .= '|semorg-' . $template . '-' . $key . '=' . $value . '|+sep=,';
		}

		$set = '{{#set:' . $set . '}}';

		return [ $set, 'noparse' => false ];

	}


	/**
	 * Show list of properties
	 */
	static function renderProperties( &$parser ) {
		$template = func_get_args()[1];
		$property_array = SemanticOrganizationProperties::getPropertiesForTemplate( $template );
		$properties = is_array( $property_array ) ? implode( ', ', array_keys( $property_array )) : '';

		return [ $properties, 'noparse' => false ];
	}


	/**
	 * Set subobjects
	 *
	 * @todo: create a solution that works with separators
	 */
	static function subobject( &$parser ) {
		$template = func_get_args()[1];
		$keyvalues = self::extractOptions( array_slice(func_get_args(), 2) );

		$set = '';

		foreach( $keyvalues as $key => $value ) {
			$set .= '|semorg-' . $template . '-' . $key . '=' . $value;
		}

		$set = '{{#subobject:' . $set . '}}';

		return [ $set, 'noparse' => false ];

	}


	/**
	 * Show header for detail page
	 */
	static function renderDetail( &$parser ) {
		$template = func_get_args()[1];
		$detailoptions = self::extractOptions( array_slice(func_get_args(), 2) );

		$heading = $detailoptions['heading'] ?? $parser->getTitle()->getText();

		$overview_page = $detailoptions['overview page'] ?? wfMessage( 'Semorg-' . $template . '-page-name' )->plain();
		$entity_name = wfMessage( 'semorg-' . $template . '-entity-name' )->plain();
		$badge = '<div class="semorg-detail-badge">[[' . $overview_page . '|<i class="fa fa-angle-left"></i>]]<span class="semorg-badge">' . strtoupper( $entity_name ) . '</span></div>';

		$header = '<div class="semorg-detail-heading">' . $heading . '</div>';
		$card = '<div class="semorg-detail">' . $badge . $header . '</div>';

		$card .= '{{#tweekiHide:firstHeading}}';
		if( isset( $detailoptions['heading'] ) ) {
			$card .= '{{DISPLAYTITLE:' . $detailoptions['heading'] . '}}';
		}

		return [ $card, 'noparse' => false ];
	}


	/**
	 * Show tabs in card
	 */
	static function renderTabsCard( &$parser ) {
		$template = func_get_args()[1];
		$taboptions = self::extractOptions( array_slice(func_get_args(), 2) );

		$taboptions['class'] = 'card-header-tabs';

		$heading = $taboptions['heading'] ?? $parser->getTitle()->getText();

		$tabs = self::getTabs( $template, $taboptions );

		$overview_page = wfMessage( 'Semorg-' . $template . '-page-name' )->plain();
		$entity_name = wfMessage( 'semorg-' . $template . '-entity-name' )->plain();
		$badge = '[[' . $overview_page . '|<span class="badge badge-secondary">' . $entity_name . '</span>]]';

		$header = '<h3>' . $heading . '</h3>';
		$card = $badge . '<div class="card semorg-card"><div class="card-header">' . $header . $tabs['tablinks'] . '</div></div>' . $tabs['tabcontents'];

		$card .= '{{#tweekiHide:firstHeading}}';

		return [ $card, 'noparse' => false ];
	}


	/**
	 * Show tabs
	 */
	static function renderTabs( &$parser ) {
		$template = func_get_args()[1];
		$taboptions = self::extractOptions( array_slice(func_get_args(), 2) );

		if( isset( $taboptions['stacked'] ) ) {
			$taboptions['type'] = 'pills';
			$taboptions['class'] .= ' nav-stacked';
			$stacked = true;
			$stack_width = is_numeric( $taboptions['stacked'] ) ? $taboptions['stacked'] : '3';
		} else {
			$stacked = false;
		}

		$tabs = self::getTabs( $template, $taboptions );

		if( $stacked ) {
			$tabs = '<div class="container-fluid"><div class="row"><div class="col-md-' . $stack_width . '">' . $tabs['tablinks'] . '</div><div class="col-md-' . ( 12-$stack_width ) . '">' . $tabs['tabcontents'] . '</div></div></div>';
		} else {
			$tabs = $tabs['tablinks'] . $tabs['tabcontents'];
		}

		return [ $tabs, 'noparse' => false ];
	}


	static function getTabs( $template, $taboptions ) {
		$stacked = false;
		$type = $taboptions['type'] ?? 'tabs';
		$class = 'nav nav-' . $type;
		if( isset( $taboptions['class'] ) ) {
			$class .= ' ' . $taboptions['class'];
		}
		$tabs = [
			'tablinks' => self::getTabLinks( $template, $taboptions, $class ),
			'tabcontents' => self::getTabContents( $template, $taboptions ),
		];
		return $tabs;
	}


	static function getTabLinks( $template, $taboptions, $class ) {
		$first = true;

		$tablinks = '';
		foreach( $taboptions as $id => $content ) {
			if( substr( $id, 0, 1 ) === '?' ) {
				$id = substr( $id, 1 );
				$tabbtn = '<btn data-toggle="tab" class="nav-link' . ($first ? ' active' : '') . '" role="tab" id="' . $id . '-label" aria-controls="' . $id . '" aria-selected="' . ($first ? 'true' : 'false') . '">#' . $id . '|' . wfMessage( 'semorg-tab-' . $template . '-' . $id ) . '</btn>';
				$tablinks .= '<li class="nav-item">' . $tabbtn . '</li>';
				$first = false;
			}
		}

		$tablinks = '<ul class="' . $class . '" role="tablist">' . $tablinks . '</ul>';

		return $tablinks;
	}


	static function getTabContents( $template, $taboptions ) {
		$first = true;

		$tabcontents = '';
		foreach( $taboptions as $id => $content ) {
			if( substr( $id, 0, 1 ) === '?' ) {
				$id = substr( $id, 1 );

				// content comes from template
				if( $content === true ) {
					$content = '{{semorg-' . $template . '-' . $id . '-tab}}';
				}

				$tabcontents .= '<div id="' . $id . '" class="tab-pane fade' . ($first ? ' active show' : '' ) . '" role="tabpanel" aria-labelledby="' . $id . '-label">' . $content . '</div>'; 
				$first = false;
			}
		}

		$tabcontents = '<div class="tab-content">' . $tabcontents . '</div>';

		return $tabcontents;
	}


	/**
	 * Liste anzeigen
	 */
	static function renderList( &$parser ) {
		global $wgSemorgListLimit;

		$parser->disableCache();

		$list = '';
		$template = func_get_args()[1];
		$listoptions = self::extractOptions( array_slice(func_get_args(), 2) );
		$request = $parser->getUser()->getRequest();
		if( $template != '' ) {
			$parameters = [];
			$row_template = $template;
			if( wfMessage('semorg-list-' . $template . '-row-template' )->exists() ) {
				$row_template = wfMessage('semorg-list-' . $template . '-row-template' )->text();
			}
			if( isset( $listoptions['row template'] ) ) {
				$row_template = $listoptions['row template'];
			}

			// if a message for the custom headers is defined for the row-template, use custom headers
			if( isset( $listoptions['headers'] ) ) {
				$headers = $listoptions['headers'];
			} elseif( $row_template != $template && wfMessage('semorg-list-' . $row_template . '-headers' )->exists() ) {
				$headers = wfMessage('semorg-list-' . $row_template . '-headers' )->text();
			} else {
				$headers = wfMessage('semorg-list-' . $template . '-headers' )->text();
			}

			if( isset( $listoptions['tableclass'] ) ) {
				$tableclass = $listoptions['tableclass'];
			} elseif( $row_template != $template && wfMessage('semorg-list-' . $row_template . '-tableclass' )->exists() ) {
				$tableclass = wfMessage('semorg-list-' . $row_template . '-tableclass' )->text();
			} elseif( wfMessage('semorg-list-' . $template . '-tableclass' )->exists() ) {
				$tableclass = wfMessage('semorg-list-' . $template . '-tableclass' )->text();
			} else {
				$tableclass = 'table table-sm table-bordered sortable';
				$tableclass = 'table table-condensed table-sm table-bordered sortable'; // for backwards-compatibility
			}

			$fields = SemanticOrganizationProperties::getPropertiesForTemplate( $template );
			if( $fields === false) {
				return [ wfMessage( 'semorg-error-list-no-fields' )->text(), 'noparse' => true ];
			}

			if( isset( $listoptions['category'] ) ) {
				// use custom parameter if it wasn't used to explicitly unset the category
				if( $listoptions['category'] != '-' ) {
					$query_string = '[[Category:' . $listoptions['category'] . ']]';
				} else {
					$query_string = '';
				}
			} else {
				// use standard category as main query parameter
				$query_string = '[[Category:{{int:semorg-' . $template . '-category}}]]';
			}

			// Custom query parameters
			if( isset( $listoptions['query'] ) ) {
				$query_string .= $listoptions['query'];
			}

			// Get implicit filters (filter links)
			if( isset( $listoptions['filter links'] ) ) {
				$filter_links = explode( ',', $listoptions['filter links'] );
			}

			// Filters
			$applied_filters = [];
			if( isset( $listoptions['filters'] ) || isset( $listoptions['filter links'] ) ) {
				$filters = [];
				$filter_defaults = [];
				$filter_string = '';
				if( isset( $listoptions['filters'] ) ) {
					$filters = explode(',', $listoptions['filters']);
				}
				// add filters that have implicitly been set with filter links
				if( isset( $filter_links ) ) {
					$filters = array_merge( $filters, $filter_links );
				}
				foreach( $filters as $filter ) {
					$filter_value = false;
					$filter = explode( '=', $filter );
					$filter_property = $filter[0];

					// had the filter been set explicitly in the query string?
					if( $request->getCheck( $filter_property ) ) {
						$filter_value = $request->getText( $filter_property );

					// or do we have a default value in the list definition?
					} elseif( isset( $filter[1] ) ) {
						$filter_value = $filter[1];
					}

					if( isset( $filter[1] ) && $filter[1] == $filter_value ) {
						$filter_defaults[$filter_property] = true;
					}

					// apply filter
					if( $filter_value !== false && $filter_value !== '') {
						$query_string .= '[[semorg-' . $filter_property . '::' . $filter_value . ']]';
						$applied_filters[$filter_property] = $filter_value;
					}
					if( $filter_value === '') {
						$applied_filters[$filter_property] = $filter_value;
					}
				}
			}

			// Set defaults for parameters
			$parameters['limit'] = $wgSemorgListLimit;
			$parameters['default'] = wfMessage('semorg-list-default');
			$parameters['intro'] = '';
			$parameters['outro'] = '';
			
			// Parameters for sorting, ordering, default (queries without results), intro, outro, limit, userparam
			foreach( ['sort', 'order', 'default', 'intro', 'outro', 'limit', 'userparam'] as $parameter ) {

				// set by a message?
				if( wfMessage('semorg-list-' . $row_template . '-' . $parameter )->exists() ) {
					$parameters[$parameter] = wfMessage('semorg-list-' . $row_template . '-' . $parameter )->parse();
				}
				// explicitly set by parser function parameter?
				if( isset( $listoptions[$parameter] ) ) {
					$parameters[$parameter] = $listoptions[$parameter];
				}
				// explicitly set by query parameter?
				if( !is_null( $request->getVal( $parameter ) ) && $request->getVal( $parameter ) > 0 ) {
					$parameters[$parameter] = $request->getVal( $parameter );
				}
			}

			// Apply parameters...
			foreach( $parameters as $parameter => $value ) {
				if( $value != '' ) {
					$query_string .= '|' . $parameter . '=' . $value;
				}
			}

			// Page number, limit, offset
			$count = (int) $parser->recursiveTagParse( '{{#ask:' . $query_string . '|format=count}}' );
			$limit = $parameters['limit'];
			$page = 1;
			if( $request->getInt( 'page' ) > 1 ) {
				$page = min( $request->getInt( 'page' ), ceil( $count / $limit ) );
			}
			if( $request->getInt( 'page' ) > 1 ) {
				$parameters['offset'] = ($page-1) * $limit;
			}
			
			// Sums
			$sums = '';
			if( isset( $listoptions['sums'] ) ) {
				$sums = '<tr class="semorg-sums sortbottom">';
				foreach( explode(',', $listoptions['sums'] )  as $sum ) {
					$sums .= '<td style="text-align:right">';
					if( $sum != '' ) {
						$sum_query = '{{#ask:' . $query_string . '|?semorg-' . $sum . '|format=sum|limit=1000}}';
						if( wfMessage('semorg-list-' . $sum . '-sum-template' )->exists() ) {
							$sums .= '{{' . wfMessage('semorg-list-' . $sum . '-sum-template' )->text() . '|' . $sum_query . '}}';
						} else {
							$sums .= $sum_query;
						}
					}
					$sums .= '</td>';
				}
				$sums .= '<td class="semorg-showedit"></td>';
				$sums .= '</tr>';
			}
			
			$query = '{{#ask:' . $query_string;

			// Fields
			$query .= '|mainlabel=target';
			foreach( $fields as $field => $attributes ) {
				if( $attributes['type'] = 'dat' ) {
					$query .= '|?semorg-' . $template . '-' . $field . '#ISO=' . $field;
				} else {
					$query .= '|?semorg-' . $template . '-' . $field . '=' . $field;
				}
			}

			// Custom fields
			foreach( $listoptions as $option => $value ) {
				if( substr( $option, 0, 1 ) === '?' ) {
					$query .= '|' . $option . '=' . $value;
				}
			}

			$query .= '|link=none|named args=yes|format=template';
			$query .= '|template=semorg-' . $row_template . '-row';
			$parameters['intro'] .= '{{semorg-list-intro|columns=' . $headers . '|tableclass=' . $tableclass . '}}';
			$parameters['outro'] = $sums . '{{semorg-list-outro}}' . $parameters['outro'];
			
			// apply parameters...
			foreach( $parameters as $parameter => $value ) {
				if( $value != '' ) {
					$query .= '|' . $parameter . '=' . $value;
				}
			}

			$query .= '|searchlabel=' . ( $listoptions['searchlabel'] ?? '' );
			$query .= '}}';

			$list = $parser->recursiveTagParse( $query );


			if( isset( $listoptions['csv'] ) ) {
				$download = self::getDownload( $parser, $query_string, $template, $listoptions['csv'] );
				$list = '<div class="semorg-csv-download">' . $parser->recursiveTagParse( $download ) . '</div>' . $list;
			}

			// Filterbox
			if( isset( $filter_links ) ) {
				$filter_links_values = [];
				foreach( $filter_links as $filter ) {
					$filter = explode( '=', $filter );
					$filter_property = $filter[0];

					// get all existing values
					$filter_values = array_unique( array_map( 'trim', explode( ',', self::getValues( $parser, $filter_property, $query_string ) ) ) );
					sort( $filter_values );
					$filter_links_values[$filter_property] = $filter_values;
				}
				$filterbox = self::getFilterbox( $parser, $filter_links_values, $applied_filters, $filter_defaults );
				$list = $parser->recursiveTagParse( $filterbox ) . $list;
			}
		}

		// Links
		if( isset( $listoptions['links'] ) ) {
			$list = '<div class="semorg-list-links">' . $parser->recursiveTagParse( $listoptions['links'] ) . '</div>' . $list;
		}

		// Header
		if( isset( $listoptions['title'] ) || isset( $listoptions['formlink'] ) ) {
			$list_header = '';

			// Title
			if( isset( $listoptions['title'] ) ) {
				$list_header .= '<div class="semorg-list-title">' . $parser->recursiveTagParse( $listoptions['title'] ) . '</div>';
			}

			// Formlink
			if( isset( $listoptions['formlink'] ) ) {
				$list_header .= '<div class="semorg-list-formlink">' . $listoptions['formlink'] . '</div>';
			}

			$list = '<div class="semorg-list-header">' . $list_header . '</div>' . $list;
		}

		// Heading
		if( isset( $listoptions['heading'] ) ) {
			$help_link = '';
			if( wfMessage( 'semorg-' . $template . '-help-page-name' )->exists() ) {
				$help_page = wfMessage( 'semorg-' . $template . '-help-page-name' );
				$help_link = '<btn class="semorg-detail-help-link" wrapper="" title="' . $help_page . '" data-toggle="tooltip">' . $help_page . '|<i class="far fa-question-circle"></i></btn>';
			}
			$heading = '<div class="semorg-detail-heading">' . $listoptions['heading'] . $help_link . '</div>';
			$heading = '<div class="semorg-detail">' . $heading . '</div>';

			$heading .= '{{#tweekiHide:firstHeading}}';
			$heading .= '{{DISPLAYTITLE:' . $listoptions['heading'] . '|noerror}}';
			$list = $parser->recursiveTagParse( $heading ) . $list;
		}

		// Footer
		if( isset( $listoptions['footer'] ) ) {
			$list .= '<div class="semorg-list-footer">' . $parser->recursiveTagParse( $listoptions['footer'] ) . '</div>';
		}

		// Pagination
		if( $template != '' && !isset( $listoptions['nopagination'] ) ) {
			$list .= '<div class="semorg-pagination d-print-none clearfix">';
			if( $page > 1 || $count > $limit ) {
				$list .= self::getPagination( $parser, $applied_filters, $count, $limit, $page );
			}
			$list .= self::getPagesizeBox( $parser, $applied_filters, $count, $limit, $page );
			$list .= '</div>';
		}

		$list = '<div class="semorg-list">' . $list . '</div>';

		return [ $list, 'noparse' => true, 'isHTML' => true ];
	}


	/**
	 * Get markup for csv download
	 *
	 * @todo: allow for nested properties
	 */
	static function getDownload( &$parser, $query_string, $template, $csv_fields ) {
		$fields = SemanticOrganizationProperties::getPropertiesForTemplate( $template );
		$csv = '{{#ask:' . $query_string;
		$csv .= '|mainlabel=-';
		foreach( explode( ',', $csv_fields ) as $field ) {
			$field = trim( $field );
			if( isset( $fields[$field] ) ) {
				$title = wfMessage( 'semorg-field-' . $template . '-' . $field . '-name' )->plain();
				if( $fields[$field]['type'] = 'dat' ) {
					$csv .= '|?semorg-' . $template . '-' . $field . '#ISO=' . $title;
				} else {
					$csv .= '|?semorg-' . $template . '-' . $field . '=' . $title;
				}
			}
		}
		$csv .= '|format=csv';
		$csv .= '|sep=;';
		$csv .= '|limit=1000';
		$csv .= '|searchlabel=<i class="fa fa-download"></i> ' . wfMessage( 'semorg-download-csv-text' )->plain();
		$csv .= '}}';
		return $csv;
	}


	/**
	 * Show a link to create a new meeting and the lists of planned and past meetings
	 *
	 * @todo: für Subvarianten von Meetings konfigurierbar machen (Parameter category und row template)
	 */
	static function renderMeetings( &$parser ) {
		$template = func_get_args()[1];
		$options = self::extractOptions( array_slice(func_get_args(), 2) );
		
		$group = '{{FULLPAGENAME}}';
		if( isset( $options['group'] ) ) {
			$group = $options['group'];
		}
		$query = '[[semorg-meeting-' . $template . '::' . $group . ']]';
		$meetings_page_name = wfMessage( 'semorg-meeting-' . $template . '-page-name' )->text();

		$meetings = '';

		$title = $options['title'] ?? $meetings_page_name;
		$links = '{{#semorg-formlink:meeting-' . $template;
		if( $group != '+' ) {
			$links .= '|query string=semorg-meeting-' . $template . '[' . $template . ']=' . $group;
		}
		$default_agenda = Title::newFromText( $parser->getTitle()->getPrefixedText() . '/' . wfMessage( 'semorg-form-default-agenda-page-name' )->text() );
		if( $default_agenda->exists() ) {
			$links .= '|preload=' . $default_agenda->getPrefixedText();
		}
		$links .= '
		}}';
		$links .= ' {{semorg-formlink-default-agenda}}';
		if( isset( $options['links'] ) ) {
			$links .= ' ' . $options['links'];
		}

		$limit = $options['limit'] ?? 1000;

		/* current meetings */
		$meetings .= '<div class="semorg-meetings-current mt-4 mb-4">{{#semorg-list:meeting
		  |title={{int:semorg-list-meeting-current-heading|' . $meetings_page_name . '}}
	      |formlink=' . $links . '
		  |query=' . $query . '[[Semorg-meeting-date::≥{{CURRENTYEAR}}-{{CURRENTMONTH}}-{{CURRENTDAY}}]]
		  |category=semorg-meeting-' . $template . '
		  |row template=meeting-' . $template . '
		  |sort=Semorg-meeting-date
		  |default={{int:semorg-list-meeting-default}}
		  |limit=' . $limit .'
		  |nopagination
		}}</div>';

		/* past meetings */
		if( !isset( $options['only current'] ) || $options['only current'] != true ) {
			$past_query = $query . '[[Semorg-meeting-date::<<{{CURRENTYEAR}}-{{CURRENTMONTH}}-{{CURRENTDAY}}]]';
			$past_count = $parser->recursiveTagParse( '{{#ask:' . $past_query . '|format=count}}' );
			$show_all_link = '[{{fullurl:{{int:semorg-meeting-' . $template . '-past-page-name}}|meeting-' . $template . '={{urlencode:' . $group . '}}}} {{int:semorg-list-meeting-all-link-text|' . $past_count . '}}]';
			$meetings .= '<div class="semorg-meetings-past">{{#semorg-list:meeting
			  |title={{int:semorg-list-meeting-past-heading|' . $meetings_page_name . '}}
			  |query=' . $past_query . '
			  |category=semorg-meeting-' . $template . '
			  |row template=meeting-' . $template . '
			  |sort=Semorg-meeting-date
			  |order=desc
			  |default={{int:semorg-list-meeting-default-past}}
			  |limit=10
			  |nopagination
			  |footer={{#ifexpr:' . $past_count . ' > 10|' . $show_all_link . '}}
			}}</div>';
		}

		return [ $meetings, 'noparse' => false ];
	}


	/**
	 * Formular anzeigen
	 *
	 * @todo: obsolete?
	 */
	static function renderForm( &$parser ) {
		die( 'renderForm is not obsolete' );
		/*
		$for_template = func_get_args()[1];
		$formoptions = self::extractOptions( array_slice(func_get_args(), 2) );
		$standard_inputs = true;
		
		// wurde template gesetzt?
		if( !isset( $formoptions['template'] ) ) {
			return wfMessage( 'semorg-form-notemplate' )->text();
		}
		$template = $formoptions['template'];

		// welche Elemente wurden gesetzt?
		if( !isset( $formoptions['elements'] ) ) {
			return wfMessage( 'semorg-form-noelements' )->text();
		}
		$elements = explode( ',', $formoptions['elements'] );

		$form = '';

		// Form-Info
		$info = '';
		if( !wfMessage( 'semorg-form-' . $for_template . '-info' )->isDisabled() ) {
			$info .= '|' . wfMessage( 'semorg-form-' . $for_template . '-info' )->text();
		}
		foreach( ['edit-title','create-title'] as $formparameter ) {
			if( !wfMessage( 'semorg-form-' . $for_template . '-' . $formparameter )->isDisabled() ) {
				$info .= '|' . str_replace('-',' ',$formparameter) . '=' . wfMessage( 'semorg-form-' . $for_template . '-' . $formparameter )->text();
			}
		}
		if( $template == 'person' ) {
			$info .= '|page name=<semorg-person[firstname]> <semorg-person[lastname]>';
		}
		if( !wfMessage( 'semorg-form-' . $template . '-page-name' )->isDisabled() ) {
			$info .= '|page name=' . wfMessage( 'semorg-form-' . $for_template . '-page-name' )->text();
		}
		if( $info != '' && !( isset( $formoptions['noinfo'] ) ) ) {
			$form .= '<nowiki>{{{info' . $info . '}}}</nowiki>';
		}

		// Template-Info
		$templateinfo = 'semorg-' . $for_template;
		if( !wfMessage( 'semorg-form-' . $for_template . '-parameters' )->isDisabled() ) {
			$templateinfo .= '|' . wfMessage( 'semorg-form-' . $for_template . '-parameters' )->text();
		}
		if( isset( $formoptions['embed in field'] ) ) {
			$templateinfo .= '|embed in field=' . $formoptions['embed in field'];
			$standard_inputs = false;
		}
		if( isset( $formoptions['add button text'] ) ) {
			$templateinfo .= '|add button text=' . $formoptions['add button text'];
		}
		if( isset( $formoptions['display'] ) ) {
			$templateinfo .= '|display=' . $formoptions['display'];
		}
		$form .= '<nowiki>{{{for template|' . $templateinfo . '}}}</nowiki>';

		$form .= '<table class="formtable">';

		foreach( $elements as $element ) {
			$element = trim( $element );
			$form .= self::getFieldRow( $template, $element );
		}

		$form .= '</table>';

		$form .= '<nowiki>{{{end template}}}</nowiki>';
		if( $standard_inputs ) {
			$form .= '<br><br><nowiki>{{{standard input|save}}} {{{standard input|cancel}}}</nowiki>';
		}
		return [ $form, 'noparse' => false ];
		*/
	}


	/**
	 * Formularfelder ausgeben
	 */
	static function renderFields( &$parser ) {
		$elements = explode(',',func_get_args()[1]);
		$fieldoptions = self::extractOptions( array_slice(func_get_args(), 2) );
		
		// wurde template gesetzt?
		if( !isset( $fieldoptions['template'] ) ) {
			return wfMessage( 'semorg-form-notemplate', '#semorg-fields' )->text();
		}
		$template = $fieldoptions['template'];

		$fields = '';
		foreach( $elements as $element ) {
			$element = trim( $element );
			$fields .= self::getField( $template, $element );
		}
		return [ $fields, 'noparse' => false ];
	}


	/**
	 * Formularfeld ausgeben
	 */
	static function renderField( &$parser ) {
		$element = func_get_args()[1];
		$fieldoptions = self::extractOptions( array_slice(func_get_args(), 2) );
		
		// wurde template gesetzt?
		if( !isset( $fieldoptions['template'] ) ) {
			return wfMessage( 'semorg-form-notemplate', '#semorg-field' )->text();
		}
		$template = $fieldoptions['template'];

		$field = self::getField( $template, $element );
		return [ $field, 'noparse' => false ];
	}


	/**
	 * Formularzeilen ausgeben
	 */
	static function renderFieldRows( &$parser ) {
		$elements = explode(',',func_get_args()[1]);
		$fieldoptions = self::extractOptions( array_slice(func_get_args(), 2) );
		
		// wurde template gesetzt?
		if( !isset( $fieldoptions['template'] ) ) {
			return wfMessage( 'semorg-form-notemplate', '#semorg-field-rows' )->text();
		}
		$template = $fieldoptions['template'];

		$fieldrows = '';
		foreach( $elements as $element ) {
			$element = trim( $element );
			if( $element != '' ) {
				$fieldrows .= self::getFieldRow( $template, $element );
			}
		}
		return [ $fieldrows, 'noparse' => false ];
	}


	/**
	 * Formularfeld erstellen
	 */
	static function getField( $template, $element ) {
		$fullelement = 'semorg-field-' . $template . '-' . $element;
		$field = $element;

		/* Construct the field */
		if( !wfMessage($fullelement . '-parameters')->isDisabled() ) {
			$field .= '|' . wfMessage($fullelement . '-parameters')->text();
		}
		foreach( [
			'placeholder',
			'input-type',
			'values',
			'mapping-template',
			'default'
		] as $parameter ) {
			if( !wfMessage($fullelement . '-' . $parameter)->isDisabled() ) {
				$field .= '|' . str_replace('-', ' ', $parameter) . '=' . wfMessage($fullelement . '-' . $parameter)->text();
			}
		}

		/* special class? */
		$class = !wfMessage($fullelement . '-class')->isDisabled() ? ( ' ' . wfMessage($fullelement . '-class')->text() ) : '';

		/* Apply standard classes */
		$field .= '|class=semorg-field ' . $fullelement . $class;

		$field = '<nowiki>{{{field|' . $field . '}}}</nowiki>';

		/* Text before and after the field */
		if( !wfMessage($fullelement . '-prefix')->isDisabled() ) {
			$field = '<span class="semorg-field-prefix ' . $fullelement . '-prefix">' . wfMessage($fullelement . '-prefix')->text() . '</span>' . $field;
		}
		if( !wfMessage($fullelement . '-suffix')->isDisabled() ) {
			$field .= '<span class="semorg-field-suffix ' . $fullelement . '-suffix">' . wfMessage($fullelement . '-suffix')->text() . '</span>';
		}

		return $field;
	}


	/**
	 * Create a row for the form
	 */
	static function getFieldRow( $template, $element ) {
		$fullelement = 'semorg-field-' . $template . '-' . $element;
		$heading = '';
		$help = '';
		$row_class = '';

		/* get the heading if it exists */
		if( !wfMessage($fullelement . '-name')->isDisabled() ) {
			$heading = wfMessage($fullelement . '-name')->text();
		}

		/* get the help message if it exists */
		if( !wfMessage($fullelement . '-help')->isDisabled() ) {
			$help = '{{semorg-msg|field-' . $template . '-' . $element . '-help}}';
			$help = '<small class="form-text text-muted semorg-help">' . $help . '</small>';
		}

		/* get inline help message if it exists */
		if( !wfMessage($fullelement . '-help-inline')->isDisabled() ) {
			$help = '{{semorg-msg|field-' . $template . '-' . $element . '-help-inline}}';
			$help = '<small class="text-muted ml-2 semorg-help">' . $help . '</small>';
		}

		/* get the row class if it exists */
		if( !wfMessage($fullelement . '-row-class')->isDisabled() ) {
			$row_class = wfMessage($fullelement . '-row-class')->text();
		}

		/* is this a single field or a group of fields? */
		if( !wfMessage($fullelement . '-fields')->isDisabled() ) {
			$fields = explode( ',', wfMessage($fullelement . '-fields')->text() );
			foreach( $fields as &$field ) {
				$field = trim( $field );
				$field = self::getField( $template, $field );
			}
			$items = implode( ' ', $fields );

			/* Text before and after the field */
			if( !wfMessage($fullelement . '-prefix')->isDisabled() ) {
				$items = '<span class="semorg-field-prefix ' . $fullelement . '-prefix">' . wfMessage($fullelement . '-prefix')->text() . '</span>' . $items;
			}
			if( !wfMessage($fullelement . '-suffix')->isDisabled() ) {
				$items .= '<span class="semorg-field-suffix ' . $fullelement . '-suffix">' . wfMessage($fullelement . '-suffix')->text() . '</span>';
			}
		} else {
			$items = self::getField( $template, $element );

			/* is it a hidden field? */
			if( !wfMessage($fullelement . '-parameters')->isDisabled() && wfMessage($fullelement . '-parameters')->text() == 'hidden' ) {
				return $items;
			}
		}

		if( !wfMessage($fullelement . '-name')->exists() || wfMessage($fullelement . '-name')->plain() != '-' ) {
			$row = '<th>' . $heading . '</th><td>' . $items . $help . '</td>';
		} else {
			$row = '<td colspan="2">' . $items . $help . '</td>';
		}
			
		return '<tr class="semorg-row-' . $template . '-' . $element . ' ' . $row_class . '">' . $row . '</tr>';
	}


	/**
	 * Handling for the 'semorg-person-ref' parser function
	 *
	 * @param Parser $parser
	 * @return array $output
	 *
	 * @todo: obsolete?
	 */
	static function renderPersonReference( &$parser ) {
		//die( 'renderPersonReference is not obsolete' );
		$tableclass = self::getTableclass();

		$output = '';
		$parser->disableCache();

		$output = '{{#subobject:';
		$values = self::extractOptions( array_slice(func_get_args(), 1) );
		foreach( $values as $key => $value ) {
			$output .= '|semorg-person-ref-' . $key . '=' . ( is_array( $value ) ? implode( ',', $value ) : $value ) . '|+sep=,';
		}
		$output .= '}}';
		$names = explode(' ', $values['name'], 2);
		$listitem = '<td>{{#formredlink:form=semorg-person
			|query string=semorg-person[firstname]=' . $names[0] . '&semorg-person[lastname]=' . $names[1] . '&returnto={{FULLPAGENAMEE}}
			|link text=' . $values['name'] . ' <i>(Klicken, um die Person neu anzulegen)</i>
			|target=' . $values['name'] . '
		}}</td>';
		$listitem .= '<td>';
		if( isset( $values['role'] ) ) {
			$listitem .= '<span class="semorg-person-ref-role">' . $values['role'] . '</span>';
		}
		$listitem .= '</td>';
		$listitem .= '<td>';
		if( isset( $values['tag'] ) && is_array( $values['tag'] ) ) {
			foreach( $values['tag'] as $tag ) {
				$listitem .= ' <span class="semorg-person-ref-tag">' . trim( $tag ) . '</span>';
			}
		}
		$listitem .= '</td>';
		$listitem .= '{{#ifexist:' . $values['name'] . '|{{showedit
			|form=semorg-person
			|target=' . $values['name'] . '
		}}|<td class="showedit"></td>}}';
		$listitem = '<tr>' . $listitem . '</tr>';
		$output .= $listitem;
		return [ $output, 'noparse' => false ];
	}


	/**
	 * Tabellenklasse ermitteln
	 *
	 * @todo: custom tableclass via configuration setting
	 */
	static function getTableclass() {
		$skinname = RequestContext::getMain()->getSkin()->getSkinName();

		$tableclass = 'wikitable';
		if( $skinname == 'tweeki' ) {
			$tableclass = 'table';
		}
		return $tableclass;
	}


	/**
	 * Handling for the 'semorg-person' parser function
	 *
	 * @param Parser $parser
	 * @return array $output
	 */
	static function renderPerson( &$parser ) {
		$tableclass = self::getTableclass();

		$output = '';
		$parser->disableCache();
		self::$options = self::extractOptions( array_slice(func_get_args(), 1) );
		foreach( self::$options as $key => $value ) {
			if( is_array( $value ) ) {
				foreach( $value as &$singlevalue ) {
					$output .= '{{#set:semorg-person-' . $key . '=' . $singlevalue . '}}';
				}
			} else {	
				$output .= '{{#set:semorg-person-' . $key . '=' . $value . '}}';
			}
		}

		$output .= '<table class="' . $tableclass . '">';
		$output .= '<tr><th>' . wfMessage( 'semorg-field-person-name-name' )->plain() . '</th><td>' . self::propt('prefix') . ' ' . self::propt('firstname') . ' ' . self::propt('lastname') . ' ' . self::propt('suffix') . '</td></tr>';
		if( isset( self::$options['workstreet'] ) || isset( self::$options['workpostalcode'] ) || isset ( self::$options['worklocality'] ) ) {
			$output .= '<tr><td><i class="fa fa-home"></i></td><td>' . ( self::propt('workstreet') ? self::propt('workstreet') . ', ' : '' ) . self::propt('workpostalcode') . ' ' . self::propt( 'worklocality' ) . '</td></tr>';
		}
		if( isset( self::$options['email'] ) ) {
			$output .= '<tr><td><i class="fa fa-envelope"></i></td><td>';
			foreach( self::$options['email'] as $email ) {
				$output .= '[mailto:' . $email . ' ' . $email . ']<br>';
			}
			$output .= '</td></tr>';
		}
		if( isset( self::$options['workphone'] ) ) {
			$output .= '<tr><td><i class="fa fa-phone"></i></td><td>';
			foreach( self::$options['workphone'] as $phone ) {
				$output .= '[tel:' . str_replace( ' ', '', $phone ) . ' ' . $phone . ']<br>';
			}
			$output .= '</td></tr>';
		}
		if( isset( self::$options['homepage'] ) ) {
			$output .= '<tr><td><i class="fa fa-home"></i></td><td>';
			foreach( self::$options['homepage'] as $homepage ) {
				$output .= $homepage . '<br>';
			}
			$output .= '</td></tr>';
		}
		if( isset( self::$options['birthday'] ) ) {
			$output .= '<tr><th>' . wfMessage( 'semorg-field-person-birthday-name' )->plain() . '</th><td>{{semorg-date|' . self::$options['birthday'] . '|long}}</td></tr>';
		}
		foreach( [ 'organization', 'note', 'ssn', 'vat', 'tax-number', 'legal-form', 'legal-registry', 'iban', 'profession' ] as $field ) {
			if( isset( self::$options[$field] ) ) {
				$output .= '<tr><th>' . wfMessage( 'semorg-field-person-' . $field . '-name' )->plain() . '</th><td>' . self::$options[$field] . '</td></tr>';
			}
		}
		$output .= '</table>';
		$output .= '<div class="vcard">{{#ask:[[{{FULLPAGENAME}}]]
			|?semorg-person-name=name
			|?semorg-person-firstname=firstname
			|?semorg-person-lastname=lastname
			|?semorg-person-email=email
			|?semorg-person-cellphone=cellphone
			|?semorg-person-workstreet=workstreet
			|?semorg-person-worklocality=worklocality
			|?semorg-person-workregion=workregion
			|?semorg-person-workpostalcode=workpostalcode
			|?semorg-person-workcountry=workcountry
			|?semorg-person-workphone=workphone
			|format=vcard
			|searchlabel=vCard
		}}</div>';

		return [ $output, 'noparse' => false ];
	}


	/**
	 * Print option - if it is set
	 *
	 * @param string $option
	 */
	static function propt( $option ) {
		return ( isset( self::$options[$option] ) ? self::$options[$option] : '' );
	}	


	static function network( Parser $parser ) {
		$options = self::extractOptions( array_slice(func_get_args(), 1) );
		foreach( [ 'node1', 'node2', 'links' ] as $required ) {
			if( !isset( $options[$required] ) || $options[$required] == '' ) {
				return wfMessage( 'semorg-error-missing-parameter', 'semorg-network', $required );
			}
		}

		$parser->getOutput()->addModules( 'ext.network' );

		$network_data = [ 
				'nodes' => [], 
				'links' => [] 
			];

		/* NODES */

		foreach( ['node1', 'node2' ] as $node ) {
			$node_options = explode( ',', $options[$node] );
			if( count( $node_options ) < 1 ) {
				return wfMessage( 'semorg-error-missing-data', 'semorg-network', $node );
			}
			$node_category = $node_options[0];
			$node_text_property = isset( $node_options[1] ) ?  'semorg-' . $node_options[1] : '';
			$node_query = $node_options[2] ?? '';

			$node_query = "{{#ask:[[Category:semorg-" . $node_category . "]]" . $node_query . "|?" . $node_text_property . "|format=array|sep=<NODE>}}";
			$node_results = $parser->RecursiveTagParse( $node_query );
			$node_results = explode( '&lt;NODE&gt;', $node_results );
			foreach( $node_results as $node_result ) {
				$node_result = explode( '&lt;PROP&gt;', $node_result );
				$node_id = $node_text = $node_result[0];
				if( isset( $node_result[1] ) && $node_result[1] !== '' ) {
					$node_text = $node_result[1];
				}
				$network_data['nodes'][] = [ 'id' => $node_id, 'group' => $node_options[0], 'text' => $node_text, 'category' => $node_category ];
			}
		}

		$nodes = [];
		foreach( $network_data['nodes'] as $node ) {
			$nodes[$node['id']] = 0;
		}

		/* LINKS */

		$links_options = explode( ',', $options['links'] );
		if( count( $links_options ) < 2 ) {
			return wfMessage( 'semorg-error-missing-data', 'semorg-network', 'links' );
		}
		$links_property1 = 'semorg-' . $links_options[0];
		$links_property2 = 'semorg-' . $links_options[1];
		$links_query = "{{#ask:[[" . $links_property1 . "::+]][[" . $links_property2 . "::+]]|mainlabel=-|?" . $links_property1 . "|?" . $links_property2 . "|format=array|sep=<LINK>}}";
		$links = $parser->RecursiveTagParse( $links_query );
		$links = explode( '&lt;LINK&gt;', $links );
		foreach( $links as $link ) {
			$link = explode( '&lt;PROP&gt;', $link );
			if( key_exists( $link[0], $nodes ) && key_exists( $link[1], $nodes ) ) {
				$network_data['links'][] = [ 'source' => $link[0], 'target' => $link[1], 'value' => 5 ];
				$nodes[$link[0]]++;
				$nodes[$link[1]]++;
			}
		}

		if( isset( $options['threshold'] ) ) {
			foreach( $network_data['nodes'] as $key => $node ) {
				if( $nodes[$node['id']] < $options['threshold'] ) {
					unset( $network_data['nodes'][$key] );
				}
			}
		}

		$network_data['nodes'] = array_values( $network_data['nodes'] );

		$id = 'semorg-network-' . explode(',', $options['node1'] )[0] . '-' . explode(',', $options['node2'] )[0];
		if( isset( $options['id'] ) ) {
			$id = $options['id'];
		}
		$id = str_replace( '-', '_', $id );

		$width = $options['width'] ?? 960;
		$height= $options['height'] ?? 600;

		$out = '<div id="' . $id . '" class="semorg-network"><svg width="' . $width . '" height="' . $height . '"></svg></div>';
		$out .= '<script>var ' . $id . '=' . json_encode( $network_data ) . ';</script>';
		return array( $out, 'noparse' => true, 'isHTML' => true );
	}


	static function cooperation( Parser $parser ) {
		$options = self::extractOptions( array_slice(func_get_args(), 1) );

		$parser->getOutput()->addModules( 'ext.network' );

		$network_data = [ 
				'nodes' => [], 
				'links' => [] 
			];

		$nodes = [];
		foreach( $network_data['nodes'] as $node ) {
			$nodes[] = $node['id'];
		}

		/* LINKS */
		$personenliste = [];
		$kooperationsliste = [];

		$projekte_query = "{{#ask:[[Kategorie:Projekt]]
			|?-Projekt.Projektteam
			|format=array
			|sep=<PROJEKT>
		}}";
		$projekte = $parser->RecursiveTagParse( $projekte_query );
		$projekte = explode( '&lt;PROJEKT&gt;', $projekte );
		foreach( $projekte as $projekt ) {
			$projekt = explode( '&lt;PROP&gt;', $projekt );
			$personen = explode( '&lt;MANY&gt;', $projekt[1] );
			if( count( $personen ) < 2 ) {
				continue;
			}
			sort( $personen );
			$projekt = $projekt[0];
			for( $i=0; $i < count( $personen )-1; $i++ ) {
				if( !isset( $kooperationsliste[$personen[$i]] ) ) {
					$kooperationsliste[$personen[$i]] = [];
				}
				for( $j=$i+1; $j < count( $personen ); $j++ ) {
					if( !isset( $kooperationsliste[$personen[$i]][$personen[$j]] ) ) {
						$kooperationsliste[$personen[$i]][$personen[$j]] = [];
					}
					$kooperationsliste[$personen[$i]][$personen[$j]][] = $projekt;
				}
			}
			$personenliste = array_merge( $personenliste, $personen );
		}

		$personenliste = array_unique( $personenliste );
		foreach( $personenliste as $person ) {
			$network_data['nodes'][] = [ 'id' => $person, 'group' => 1, 'text' => str_replace( 'Benutzer:', '', $person ) ];
		}

		foreach( $kooperationsliste as $source => $kooperationen ) {
			foreach( $kooperationen as $target => $kooperation ) {
				if( count( $kooperation ) > 1 ) {
					$network_data['links'][] = [ 'source' => $source, 'target' => $target, 'value' => pow( 3, count( $kooperation ) ) ];
				}
			}
		}

		$id = 'cooperation';
		if( isset( $options['id'] ) ) {
			$id = $options['id'];
		}

		$out = '<div id="' . $id . '" class="semorg-network"><svg width="960" height="600"></svg></div>';
		$out .= '<script>var ' . $id . '=' . json_encode( $network_data ) . ';</script>';
		return array( $out, 'noparse' => true, 'isHTML' => true );
	}


	/**
	 * circles
	 *
	 */
	static function circles( Parser $parser ) {
		$options = self::extractOptions( array_slice(func_get_args(), 1) );

		$parser->getOutput()->addModules( 'ext.circles' );

		$hierarchy = [
			"name" => "Koordinationskreis",
			"children" => []
		];

		//$group_query = "{{#ask:[[-Gremium::+]][[-Gremium.Rollentitel::+]]|format=array|sep=<GROUP>}}";
		$group_query = "{{#ask:[[-Gremium::+]][[!Koordinationskreis]][[-Gremium.Rollentitel::+]]|format=array|sep=<GROUP>}}";
		$groups = $parser->RecursiveTagParse( $group_query );
		$groups = explode( '&lt;GROUP&gt;', $groups );
		foreach( $groups as $group ) {
			/* alle Mitglieder eines Gremiums */
            $members_query = "{{#ask:[[" . $group . "]]|mainlabel=-|?Mitglied|format=array}}";

			/* nur Mitglieder mit Rollen in einem Gremium */
			//$members_query = "{{#ask:[[-Inhaber::+]][[-Inhaber.Gremium::" . $group . "]]|format=array}}";

			$members = $parser->RecursiveTagParse( $members_query );
			if( $members != '' ) {
				$members = explode( '&lt;MANY&gt;', $members );
				//$members = explode( ',', $members );
				$hierarchy_members = [];
				foreach( $members as $member ) {

					/* default: show siblings and parent */
					if( !isset( $options['hide siblings'] ) || $options['hide siblings'] != 'true' ) {
						if( !isset( $options['hide parent'] ) || $options['hide parent'] != 'true') {
							$group_selector = '+';
						} else {
							$group_selector = '!Koordinationskreis';
						}
					} else {
						if( !isset( $options['hide parent'] ) || $options['hide parent'] != 'true') {
							$group_selector = $group . '||Koordinationskreis';
						} else {
							$group_selector = $group;
						}
					}

					$roles_query = "{{#ask:[[Inhaber::" . $member . "]][[aktiv::wahr]][[Gremium::" . $group_selector . "]]|?Rollentitel|?Gremium|?Inhaber|format=array|sep=<ROLE>}}";
					$roles = $parser->RecursiveTagParse( $roles_query );
					if( $roles != '' ) {
						$roles = explode( '&lt;ROLE&gt;', $roles );
						$hierarchy_roles = [];
						foreach( $roles as $role ) {
							$role = explode( '&lt;PROP&gt;', $role );
							$hours_query = "{{#ask:[[Rolle::" . $role[0] . "]]|?Stundenaufwand|format=sum|default=1}}";
							$role_members_count = count( explode( '&lt;MANY&gt;', $role[3] ) );
							$hours = $parser->RecursiveTagParse( $hours_query );
							if( $role[2] != 'Koordinationskreis' ) {
								$hours = $hours / $role_members_count;
							}
							//$hierarchy_roles[] = [ "name" => $role[1], "link" => $role[0], "type" => "role", "size" => $hours ];
							$hierarchy_roles[] = [ "name" => $role[1], "link" => $role[0], "type" => "role", "size" => $hours, "group_role" => $role[2], "group_member" => $group ];
						}
						$hierarchy_members[] = [ "name" => str_replace( 'Benutzer:', '', $member ), "type" => "member", "children" => $hierarchy_roles ];
					} else {
						$hierarchy_members[] = [ "name" => str_replace( 'Benutzer:', '', $member ), "type" => "member", "size" => 5 ];
					}
				}
				$hierarchy['children'][] = [ "name" => $group, "type" => "group", "children" => $hierarchy_members ];
			}
		}

		$id = 'circles';
		if( isset( $options['id'] ) ) {
			$id = $options['id'];
		}

		$out = '<div id="' . $id . '" class="semorg-circles"><svg width="600" height="600"></svg></div>';
		$out .= '<script>var ' . $id . '=' . json_encode( $hierarchy ) . ';</script>';
		return array( $out, 'noparse' => true, 'isHTML' => true );
	}

	/**
	 * Render all values for a specific property
	 */
	static function renderValues( &$parser ) {
		$property = func_get_args()[1];
		return self::getValues( $parser, $property );
	}


	/**
	 * Get all values for a specific property
	 *
	 * @param Parser $parser Parser
	 * @param string $property Name of the property
	 * @param string $query_string Additional query parameters
	 *
	 * @return string Comma-separated list of values
	 */
	static function getValues( $parser, $property, $query_string = '' ) {
		$property_parts = explode( '.', $property );
		$query = '{{#ask: [[semorg-' . $property_parts[0] . '::+]]' . $query_string . ' |mainlabel=- |headers=hide |limit=1000 |searchlabel= |?semorg-' . $property . '#|default=- }}';
		$values = $parser->recursiveTagParse( $query );
		return $values;
	}


	/**
	 * Create filterbox
	 *
	 * @param Array $filter_links_values List of filters and their values
	 * @param Array $applied_filters List of applied filters and the applied value
	 * @param Array $filter_defaults List of filters with default value applied
	 *
	 * @return string HTML code for filterbox
	 *
	 * @todo: implement limits and offsetting
	 */
	static function getFilterbox( $parser, $filter_links_values, $applied_filters, $filter_defaults ) {
		$filterbox = '';
		// add invisible filters that have been applied in order to make them dropable
		foreach( $applied_filters as $filter_property => $value ) {
			if( !isset( $filter_links_values[$filter_property] ) ) {
				$filter_links_values[$filter_property] = [ $value ];
			}
		}
		foreach( $filter_links_values as $filter_property => $values ) {
			$filterbox .= '<div class="semorg-filterbox-filter">';
			$filter_properties = explode( '.', $filter_property );

			// allow costumization for nested property names via 'semorg-feature-field-feature-field-name'
			$filter_name_msg = 'semorg-field-' . str_replace( '.semorg', '', $filter_property ) . '-name';
			if( !wfMessage( $filter_name_msg )->exists() ) {
				$filter_name_msg = 'semorg-field-' . str_replace( 'semorg-', '', end( $filter_properties  ) ) . '-name';
			}
			$filter_name = wfMessage( $filter_name_msg );
			$filterbox .= '<span class="semorg-filterbox-filter-name">' . $filter_name . ': </span>';
			foreach( $values as &$value ) {
				if( $value != '' ) {
					if( $value != '-' ) {
						if( isset( $applied_filters[$filter_property] ) && $applied_filters[$filter_property] == $value ) {
							if( isset( $filter_defaults[$filter_property] ) ) {
								$drop_filter_url = self::getFilterURL( $parser, array_merge( array_diff_key( $applied_filters, [ $filter_property => $value ] ), [ $filter_property => '' ] ) );
							} else {
								$drop_filter_url = self::getFilterURL( $parser, array_diff_key( $applied_filters, [ $filter_property => $value ] ) );
							}
							$drop_filter_link = '<span title="' . wfMessage('semorg-filterbox-drop-filter')->plain() . '" data-toggle="tooltip">[' . $drop_filter_url . ' &times;]</span>';
							$value = '<span class="semorg-filterbox-filter-value semorg-filterbox-filter-value-applied">' . $value . ' ' . $drop_filter_link . '</span>';
						} else {
							$filter_url = self::getFilterURL( $parser, array_merge( $applied_filters, [ $filter_property => $value ] ) );
							$value = '<span class="semorg-filterbox-filter-value">[' . $filter_url . ' ' . $value . ']</span>';
						}
					} else {

						// if there are no results it might be because of default value - needs to be dropable
						if( isset( $filter_defaults[$filter_property] ) ) {
							$drop_filter_url = self::getFilterURL( $parser, array_merge( array_diff_key( $applied_filters, [ $filter_property => $value ] ), [ $filter_property => '' ] ) );
							$drop_filter_link = '<span title="' . wfMessage('semorg-filterbox-drop-filter')->plain() . '" data-toggle="tooltip">[' . $drop_filter_url . ' &times;]</span>';
							$value = '<span class="semorg-filterbox-filter-value semorg-filterbox-filter-value-applied">' . $applied_filters[$filter_property] . ' ' . $drop_filter_link . '</span>';
						}
					}
				}
			}
			$filterbox .= implode( ' · ', $values );
			$filterbox .= '</div>';
		}
		$filterbox_title = '<div class="semorg-filterbox-title">' . wfMessage( 'semorg-filterbox-title' ) . '</div>';
		$filterbox = '<div class="semorg-filterbox">' . $filterbox_title . $filterbox . '</div>';
		return $filterbox;
	}


	/**
	 * Get pagination
	 *
	 * @param Parser $parser Parser
	 * @param Array $applied_filters List of applied filters and the applied value
	 * @param Integer $count Number of rows
	 * @param Integer $limit Page size
	 * @param Integer $page Page number
	 * 
	 * @return string HTML code for pagination
	 */
	static function getPagination( $parser, $applied_filters, $count, $limit, $page = 1 ) {
		$pagination = '';

		$previous_url = self::getFilterURL( $parser, array_merge( $applied_filters, [ 'page' => $page-1 ] ) );
		if( $page > 1 ) {
			$pagination .= '<li class="page-item"><a class="page-link" href="' . $previous_url . '" aria-label="' . wfMessage( 'semorg-pagination-previous-label' ) . '"><span aria-hidden="true">&laquo;</span></a></li>';
		} else {
			$pagination .= '<li class="page-item disabled"><a class="page-link" href="' . $previous_url . '" tabindex="-1" aria-disabled="true" aria-label="' . wfMessage( 'semorg-pagination-previous-label' ) . '"><span aria-hidden="true">&laquo;</span></a></li>';
		}

		$start = max( $page-2, 1 );
		$ende = min( $start+5, ceil( $count / $limit ) ); 
		for( $i = $start; $i <= $ende; $i++ ) {
			$page_url = self::getFilterURL( $parser, array_merge( $applied_filters, [ 'page' => $i ] ) );
			if( $page == $i ) {
				$pagination .= '<li class="page-item active" aria-current="page"><a class="page-link" href="' . $page_url . '">' . ( $i ) . '<span class="sr-only">(current)</span></a></li>';
			} else {
				$pagination .= '<li class="page-item"><a class="page-link" href="' . $page_url . '">' . ( $i ) . '</a></li>';
			}
		}

		$next_url = self::getFilterURL( $parser, array_merge( $applied_filters, [ 'page' => $page+1 ] ) );
		if( $page < $count / $limit ) {
			$pagination .= '<li class="page-item"><a class="page-link" href="' . $next_url . '" aria-label="' . wfMessage( 'semorg-pagination-next-label' ) . '"><span aria-hidden="true">&raquo;</span></a></li>';
		} else {
			$pagination .= '<li class="page-item disabled"><a class="page-link" href="' . $next_url . '" tabindex="-1" aria-disabled="true" aria-label="' . wfMessage( 'semorg-pagination-next-label' ) . '"><span aria-hidden="true">&raquo;</span></a></li>';
		}

		$pagination = '<nav aria-label="' . wfMessage( 'semorg-pagination-label' )->text() . '"><ul class="pagination pagination-sm">' . $pagination . '</ul></nav>';
		$pagination = '<div class="semorg-pagination-pagination">' . $pagination . '</div>';
		return $pagination;
	}


	/**
	 * Get page size box
	 *
	 * @param Parser $parser Parser
	 * @param Array $applied_filters List of applied filters and the applied value
	 * @param Integer $count Number of rows
	 * @param Integer $limit Page size
	 * @param Integer $page Page number
	 * 
	 * @return string HTML code for page size selection
	 */
	static function getPagesizeBox( $parser, $applied_filters, $count, $limit, $page = 1 ) {
		if( $count == 0 ) {
			return '';
		}

		$pagesize = '';

		$sizes = [ 20, 50, 100 ];
		if( $sizes[0] > $count ) {
			
			// don't show page size selection if total number is smaller than current page size
			if( (int) $limit > $count ) {
				return '';

			// show only first available standard page size if total number is smaller than it but larger than current page size
			} else {
				$sizes = [ $sizes[0] ];
			}
		}
		$sizes = array_unique( array_merge( $sizes, [ (int) $limit ] ) );
		sort( $sizes );
		foreach( $sizes as $size ) {
			$pagesize_url = self::getFilterURL( $parser, array_merge( $applied_filters, [ 'limit' => $size ] ) );
			if( $size != $limit ) {
				$pagesize .= '<li class="page-item"><a class="page-link" href="' . $pagesize_url . '">' . $size . '</a></li>';
			} else {
				$pagesize .= '<li class="page-item active" aria-current="pagesize"><a class="page-link" href="' . $pagesize_url . '">' . $size . '<span class="sr-only">(current)</span></a></li>';
			}
		}

		$pagesize = '<nav aria-label="' . wfMessage( 'semorg-pagesize-label' )->text() . '"><ul class="pagination pagination-sm">' . $pagesize . '</ul></nav>';
		$pagesize = '<div class="semorg-pagination-pagesize">' . $pagesize . '</div>';
		return $pagesize;
	}


	/**
	 * Get filter URL
	 *
	 * @param Array $filters_to_apply List of filters to apply
	 *
	 * @return string Wiki markup for the URL part of the filter link
	 */
	static function getFilterURL( $parser, $filters_to_apply ) {
		$query_string = '';

		// Apply limit
		if( isset( $filters_to_apply['limit'] ) ) {

			// explicit unsetting
			if( $filters_to_apply['limit'] == '' ) {
				unset( $filters_to_apply['limit'] );
			}
		} else {

			// use request query if available and limit hasn't been explicitly set
			if( $parser->getUser()->getRequest()->getInt( 'limit' ) > 0 ) {
				$filters_to_apply['limit'] = $parser->getUser()->getRequest()->getInt( 'limit' );
			}
		}

		// Add filters to query string for URL
		foreach( $filters_to_apply as $filter_property => $value ) {
			$query_string .= $filter_property . '=' . urlencode( $value ) . '&';
		}
		$query_string = substr( $query_string, 0, -1 );
		$title = $parser->getTitle();
		$filter_url = $title->getFullURL( $query_string );
		return $filter_url;
	}


	/**
	 * Render documentation for semorg's templates and forms
	 */
	static function renderDocumentation( &$parser ) {
		$documentation = '';

		$title = $parser->getTitle();
		$namespace = $title->getNamespace();
		$name = $title->getText();

		if( $namespace == 10 ) { // templates
			if( wfMessage( $name . '-template' )->exists() ) {
				$documentation = '{{semorg-help-template}}';
			}
		} elseif( $namespace == 14 ) { // categories
		} elseif( $namespace == 106 ) { // forms
		}

		return array( $documentation, 'noparse' => false );
	}


	/**
	 * Render a year's schedule for a task
	 */
	static function renderSchedule( &$parser ) {
		$schedule = '';
		$months = explode( ',', func_get_args()[1] );

		for( $i = 1; $i <= 12; $i++ ) {
			$date = new DateTime('1970-' . $i . '-01');
			$schedule .= '<td class="semorg-month' . ( date('m') == $i ? ' semorg-month-current' : '' ) . '">';
			$schedule .= in_array( $i, $months ) ? '<span class="semorg-month-full">' . date('M', $date->format('U') ) . '</span>' : '<span class="month-empty">·</span>';
			$schedule .= '</td>';
		}

		return array( $schedule, 'noparse' => false );
	}


	/**
	 * Render a dashboard element
	 */
	static function renderDashboard( &$parser ) {
		$dashboard = '';
		$template = func_get_args()[1];
		$dashboardoptions = self::extractOptions( array_slice(func_get_args(), 2) );

		$title = '';
		$links = '';
		if( wfMessage( 'semorg-' . $template . '-page-name' )->exists() ) {
			$page_name = wfMessage( 'semorg-' . $template . '-page-name' )->text();
			$title = wfMessage( 'semorg-dashboard-title', $page_name );
			$links .= '[[' . $page_name . '|<span class="btn btn-sm btn-secondary">' . wfMessage( 'semorg-dashboard-link-all', $page_name ) . '</span>]]';
		}
		if( wfMessage( 'semorg-form-' . $template . '-page-name' )->exists() ) {
			$links .= '{{#semorg-formlink:' . $template . '|returnto={{FULLPAGENAME}}}}';
		}
		if( wfMessage( 'semorg-' . $template . '-dashboard-title' )->exists() ) {
			$title = wfMessage( 'semorg-' . $template . '-dashboard-title' )->text();
		}
		if( isset( $dashboardoptions['title'] ) ) {
			$title = $dashboardoptions['title'];
		}
		if( isset( $dashboardoptions['links'] ) ) {
			// explicit unsetting
			if( $dashboardoptions['links'] == '-' ) {
				$links = '';
			} else {
				$links = $dashboardoptions['links'];
			}
		}
		$dashboard .= '|title=' . $title;
		$dashboard .= '|links=' . $links;

		if( isset( $dashboardoptions['tables'] ) ) {
			$tables = $dashboardoptions['tables'];
		} else {
			// wrap default value in div.semorg-dashboard-default
			if( !isset( $dashboardoptions['default'] ) ) {
				$dashboardoptions['default'] = wfMessage('semorg-list-default');
			}
			$dashboardoptions['default'] = '<div class="semorg-dashboard-default">' . $dashboardoptions['default'] . '</div>';
			
			$tableparameters = '';
			
			// do not use title and links for both dashboard and list
			unset( $dashboardoptions['title'] );
			unset( $dashboardoptions['links'] );

			foreach( $dashboardoptions as $option => $value ) {
				$tableparameters .= '|' . $option . '=' . $value;
			}

			// switch off pagination by default
			if( !isset( $dashboardoptions['pagination'] ) ) {
				$tableparameters .= '|nopagination';
			}
			$tables = '{{#semorg-list:' . $template . $tableparameters . '}}';
		}

		if( $tables != '-' ) {
			$dashboard .= '|tables=' . $tables;
		}

		if( isset( $dashboardoptions['body'] ) ) {
			$dashboard .= '|body=' . $dashboardoptions['body'];
		}

		$dashboard = '{{semorg-card' . $dashboard . '}}';
		return array( $dashboard, 'noparse' => false );
	}


	/**
	 * Render a link to a role
	 */
	static function renderRoleLink( &$parser ) {
		$rolelink = '';
		$parameter = func_get_args()[1];
		$parameter = trim( $parameter );
		if( $parameter != '' ) {
			$isRoleTitle = false;
			$parametertitle = Title::newFromText( $parameter );

			if( $parametertitle->exists() ) {
				$parameterpage = WikiPage::factory( $parametertitle );
				foreach( $parameterpage->getCategories() as $cat ) {
					if( lcfirst( $cat->getText() ) === wfMessage('semorg-role-category')->text() ) {
						$isRoleTitle = true;
					}
				}
			}

			// is the parameter the name of a page in the roles' category?
			if( $isRoleTitle ) {
				return [ '[[' . $parameter . '|{{#show:' . $parameter . '|?semorg-role-name}}]]', 'noparse' => false ];
			} else {

				// is the parameter the value of a page's semorg-role-name attribute?
				$parameterrole = $parser->recursiveTagParse( '{{#ask:[[semorg-role-name::' . $parameter . ']]|link=none|limit=1|searchlabel=}}' );
				if( $parameterrole != '' ) {
					return [ $parameterrole, 'noparse' => false ];

				// show a link to create a new role with that name
				} else {
					$newlink = Title::newFromText( 'Special:FormEdit/' . wfMessage( 'semorg-role-form' ) )->getFullURL( wfMessage('semorg-role-template') . '[name]=' . $parameter );
					if( wfMessage('semorg-formlink-role-link-text')->exists() ) {
						$newlink_tooltip = wfMessage('semorg-formlink-role-link-text')->text();
					} else {
						$newlink_tooltip = wfMessage('semorg-formlink-generic-link-text')->params( wfMessage('semorg-role-entity-name')->text() )->text();
					}
					return [ '<a href="' . $newlink . '" class="new" data-toggle="tooltip" title="' . $newlink_tooltip . '">' . htmlentities( $parameter ) . '</a>', 'noparse' => true, 'isHTML' => true ];
				}
			}
		}
	}


	/**
	 * Converts an array of values in form [0] => "name=value" into a real
	 * associative array in form [name] => value. If no = is provided,
	 * true is assumed like this: [name] => true
	 * taken from https://www.mediawiki.org/wiki/Manual:Parser_functions#Named_parameters
	 *
	 * @param array string $options
	 * @return array $results
	 */
	static function extractOptions( array $options ) {
		$results = array();

		foreach ( $options as $option ) {
			$pair = explode( '=', $option, 2 );
			if ( count( $pair ) === 2 ) {
				$name = trim( $pair[0] );
				$value = trim( $pair[1] );
				// don't store empty values
				if( $value !== '' ) {
					$results[$name] = $value;
				}
			}

			if ( count( $pair ) === 1 ) {
				$name = trim( $pair[0] );
				if( $name !== '' ) {
					$results[$name] = true;
				}
			}
		}

		foreach( self::$listvalues as $listvalue ) {
			if( isset( $results[$listvalue] ) ) {
				$results[$listvalue] = explode( ',', $results[$listvalue] );
				foreach( $results[$listvalue] as &$singlevalue ) {
					$singlevalue = trim( $singlevalue );
				}
			}
		}

		return $results;
	}



}
