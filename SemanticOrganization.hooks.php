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
			'table' => 'table',
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

		$originalLink = '<button class="btn btn-default btn-xs semorg-toggle-original">' . $keyvalues['original-text'] . '</button>';
		$toggleLink = '<button class="btn btn-default btn-xs semorg-toggle-toggle" style="display:none">' . $keyvalues['toggle-text'] . '</button>';
		$toggle = '<div class="semorg-toggle" data-semorg-toggle="' . $keyvalues['class'] . '">' . $originalLink . $toggleLink . '</div>';

		return array( $toggle, 'noparse' => true, 'isHTML' => true );
	}


	/**
	 * Render table
	 */
	static function table( &$parser ) {
		$template = func_get_args()[1];
		$keyvalues = self::extractOptions( array_slice(func_get_args(), 2) );

		$table = '';

		foreach( $keyvalues as $key => $value ) {
			$fullelement = $template . '-' . $key;
			$table .= '<tr>';
			$table .= '<th>{{semorg-field-name|' . $fullelement . '}}</th>';
			if( is_array( $value ) ) {
				$table .= '<td>
' . implode( ', ', $value ) . '
					</td>';
			} else {
				$table .= '<td>
' . $value . '
					</td>';
			}
 			$table .= '</tr>';
		}

		$table = '<table class="table semorg-detail-table">' . $table . '</table>';

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
			'returnto' => 'returnto'
		];
		foreach( $messages as $option => $message_key ) {
			if( isset( $options[$option] ) ) {
				$parameters[$option] = $options[$option];
			} elseif( wfMessage('semorg-formlink-' . $template . '-' . $message_key)->exists() ) {
				$parameters[$option] = wfMessage('semorg-formlink-' . $template . '-' . $message_key)->plain();
			}
		}

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
			'default value' => 'default value',
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
		$taboptions = self::extractOptions( array_slice(func_get_args(), 2) );

		$heading = $taboptions['heading'] ?? $parser->getTitle()->getText();

		$overview_page = wfMessage( 'Semorg-' . $template . '-page-name' )->plain();
		$entity_name = wfMessage( 'semorg-' . $template . '-entity-name' )->plain();
		$badge = '<div class="semorg-detail-badge">[[' . $overview_page . '|<i class="fa fa-angle-left"></i>]]<span class="semorg-badge">' . strtoupper( $entity_name ) . '</span></div>';

		$header = '<div class="semorg-detail-heading">' . $heading . '</div>';
		$card = '<div class="semorg-detail">' . $badge . $header . '</div>';

		$card .= '{{#tweekiHide:firstHeading}}';
		if( isset( $taboptions['heading'] ) ) {
			$card .= '{{DISPLAYTITLE:' . $taboptions['heading'] . '}}';
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

		$list = '';
		$limit = $wgSemorgListLimit;
		$template = func_get_args()[1];
		$formoptions = self::extractOptions( array_slice(func_get_args(), 2) );
		$request = $parser->getUser()->getRequest();
		$parameters = [];

		$row_template = $template;
		if( wfMessage('semorg-list-' . $template . '-row-template' )->exists() ) {
			$row_template = wfMessage('semorg-list-' . $template . '-row-template' )->text();
		}
		if( isset( $formoptions['row template'] ) ) {
			$row_template = $formoptions['row template'];
		}

		// if a message for the custom headers is defined for the row-template, use custom headers
		if( isset( $formoptions['headers'] ) ) {
			$headers = $formoptions['headers'];
		} elseif( $row_template != $template && wfMessage('semorg-list-' . $row_template . '-headers' )->exists() ) {
			$headers = wfMessage('semorg-list-' . $row_template . '-headers' )->text();
		} else {
			$headers = wfMessage('semorg-list-' . $template . '-headers' )->text();
		}

		$fields = SemanticOrganizationProperties::getPropertiesForTemplate( $template );

		$query_string = '';

		if( isset( $formoptions['category'] ) ) {
			// use custom parameter if it wasn't used to explicitly unset the category
			if( $formoptions['category'] != '-' ) {
				$query_string = '[[Category:' . $formoptions['category'] . ']]';
			}
		} else {
			// use standard category as main query parameter
			$query_string = '[[Category:{{int:semorg-' . $template . '-category}}]]';
		}

		// Custom query parameters
		if( isset( $formoptions['query'] ) ) {
			$query_string .= $formoptions['query'];
		}

		// Get implicit filters (filter links)
		if( isset( $formoptions['filter links'] ) ) {
			$filter_links = explode( ',', $formoptions['filter links'] );
		}

		// Filters
		$applied_filters = [];
		if( isset( $formoptions['filters'] ) || isset( $formoptions['filter links'] ) ) {
			$filters = [];
			$filter_defaults = [];
			$filter_string = '';
			if( isset( $formoptions['filters'] ) ) {
				$filters = explode(',', $formoptions['filters']);
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
		foreach( $formoptions as $option => $value ) {
			if( substr( $option, 0, 1 ) === '?' ) {
				$query .= '|' . $option . '=' . $value;
			}
		}

		$query .= '|link=none|named args=yes|format=template';
		$query .= '|template=semorg-' . $row_template . '-row';
		$query .= '|intro={{semorg-list-intro|columns=' . $headers . '}}';
		$query .= '|outro={{semorg-list-outro}}';

		// Parameters for sorting, ordering, searchlabel, default (queries without results)
		foreach( ['sort', 'order', 'default', 'limit', 'userparam' ] as $parameter ) {
			$parameters[$parameter] = '';

			// set default limit
			// @todo: use global setting instead or make it configurable via message?
			if( $parameter == 'limit' ) {
				$parameters[$parameter] = '1000';
			}

			// set by a message?
			if( wfMessage('semorg-list-' . $row_template . '-' . $parameter )->exists() ) {
				$parameters[$parameter] = wfMessage('semorg-list-' . $row_template . '-' . $parameter )->parse();
			}
			// explicitly set by parser function parameter?
			if( isset( $formoptions[$parameter] ) ) {
				$parameters[$parameter] = $formoptions[$parameter];
			}
			// apply if set...
			if( $parameters[$parameter] != '' ) {
				$query .= '|' . $parameter . '=' . $parameters[$parameter];
			}
		}

		$query .= '|searchlabel=' . ( $formoptions['searchlabel'] ?? '' );
		$query .= '|limit=' . $limit;
		if( $request->getInt( 'page' ) > 1 ) {
			$query .= '|offset=' . ($request->getInt( 'page' )-1) * $limit;
		}

		$query .= '}}';

		$list = $parser->recursiveTagParse( $query );

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

		// pagination
		$count = (int) $parser->recursiveTagParse( '{{#ask:' . $query_string . '|format=count}}' );
		$page = 1;
		if( $request->getInt( 'page' ) > 1 ) {
			$page = $request->getInt( 'page' );
		}
		if( $page > 1 || $count > $limit ) {
			$list .= self::getPagination( $parser, $applied_filters, $count, $limit, $page );
		}

		if( isset( $formoptions['csv'] ) ) {
			$download = self::getDownload( $parser, $query_string, $template, $formoptions['csv'] );
			$list .= $parser->recursiveTagParse( $download );
		}

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
		$csv .= '|searchlabel=<span class="semorg-csv-download">' . wfMessage( 'semorg-download-csv-text' )->plain() . '</span>';
		$csv .= '}}';
		return $csv;
	}


	/**
	 * Link zur Erstellung und Listen von geplanten und vergangenen Treffen anzeigen
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

		$meetings = '{{#semorg-formlink:meeting-' . $template;
		if( $group != '+' ) {
			$meetings .= '|query string=semorg-meeting-' . $template . '[' . $template . ']=' . $group;
		}
		$meetings .= '
		  |popup=true
		}}';
		$meetings .= '<div class="h3">{{int:semorg-list-meeting-current-heading}}</div>';

		$meetings .= '{{#semorg-list:meeting
		  |query=' . $query . '[[Semorg-meeting-date::≥{{CURRENTYEAR}}-{{CURRENTMONTH}}-{{CURRENTDAY}}]]
		  |category=semorg-meeting-' . $template . '
		  |row template=meeting-' . $template . '
		  |sort=Semorg-meeting-date
		}}';
		$meetings .= '<div class="h3">{{int:semorg-list-meeting-past-heading}}</div>';
		$meetings .= '{{#semorg-list:meeting
		  |query=' . $query . '[[Semorg-meeting-date::<{{CURRENTYEAR}}-{{CURRENTMONTH}}-{{CURRENTDAY}}]]
		  |category=semorg-meeting-' . $template . '
		  |row template=meeting-' . $template . '
		  |sort=Semorg-meeting-date
		  |order=desc
		  |default={{int:semorg-list-meeting-default-past}}
		}}';

		return [ $meetings, 'noparse' => false ];
	}


	/**
	 * Formular anzeigen
	 */
	static function renderForm( &$parser ) {
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
	}


	/**
	 * Formularfelder ausgeben
	 */
	static function renderFields( &$parser ) {
		$elements = explode(',',func_get_args()[1]);
		$fieldoptions = self::extractOptions( array_slice(func_get_args(), 2) );
		
		// wurde template gesetzt?
		if( !isset( $fieldoptions['template'] ) ) {
			return wfMessage( 'semorg-form-notemplate' )->text();
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
			return wfMessage( 'semorg-form-notemplate' )->text();
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
			return wfMessage( 'semorg-form-notemplate' )->text();
		}
		$template = $fieldoptions['template'];

		$fieldrows = '';
		foreach( $elements as $element ) {
			$element = trim( $element );
			$fieldrows .= self::getFieldRow( $template, $element );
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
			'mapping-template'
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
			$help = wfMessage($fullelement . '-help')->text();
			$help = '<small class="form-text text-muted">' . $help . '</small>';
		}

		/* get inline help message if it exists */
		if( !wfMessage($fullelement . '-help-inline')->isDisabled() ) {
			$help = wfMessage($fullelement . '-help-inline')->text();
			$help = '<small class="text-muted ml-2">' . $help . '</small>';
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
	 * Handling for the 'semorg-person' parser function
	 *
	 * @param Parser $parser
	 * @return array $output
	 */
	static function renderPersonReference( &$parser ) {
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
		foreach( [ 'organization', 'note' ] as $field ) {
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

		$parser->getOutput()->addModules( 'ext.network' );

		$network_data = [ 
				'nodes' => [], 
				'links' => [] 
			];

		$filter = '';
		if( isset( $options['filter'] ) ) {
			$filter = $options['filter'];
		}

		/* NODES */

		$node_categories = [
			'Projekt' => [ 'group' => 1 ],
			'Semorg-member' => [ 'group' => 4, 'title' => 'Semorg-person-firstname', 'query' => '[[Semorg-person-membership::wahr]][[Semorg-person-firstname::+]]' ],
		];
		foreach( $node_categories as $node_category => $node_options ) {
			$node_filter = $filter;
			$title = 'Kurztitel';
			if( isset( $node_options['title'] ) ) {
				$title = $node_options['title'];
			}
			if( isset( $node_options['query'] ) ) {
				$node_filter .= $node_options['query'];
			}
			$node_query = "{{#ask:[[Kategorie:" . $node_category . "]]" . $node_filter . "|?" . $title . "|format=array|sep=<NODE>}}";
			$node_results = $parser->RecursiveTagParse( $node_query );
			$node_results = explode( '&lt;NODE&gt;', $node_results );
			foreach( $node_results as $node_result ) {
				$node_result = explode( '&lt;PROP&gt;', $node_result );
				$node_id = $node_text = $node_result[0];
				if( isset( $node_result[1] ) && $node_result[1] !== '' ) {
					$node_text = $node_result[1];
				}
				$network_data['nodes'][] = [ 'id' => $node_id, 'group' => $node_options['group'], 'text' => $node_text ];
			}
		}

		$nodes = [];
		foreach( $network_data['nodes'] as $node ) {
			$nodes[] = $node['id'];
		}

		/* LINKS */

		$kontakte_query = "{{#ask:[[Projektteam::+]][[Projekt::+]]|mainlabel=-|?Projektteam|?Projekt|format=array|sep=<KONTAKT>}}";
		$kontakte = $parser->RecursiveTagParse( $kontakte_query );
		$kontakte = explode( '&lt;KONTAKT&gt;', $kontakte );
		foreach( $kontakte as $kontakt ) {
			$link = explode( '&lt;PROP&gt;', $kontakt );
			if( in_array( $link[0], $nodes ) && in_array( $link[1], $nodes ) ) {
				$network_data['links'][] = [ 'source' => $link[0], 'target' => $link[1], 'value' => 5 ];
			}
		}

		$id = 'network';
		if( isset( $options['id'] ) ) {
			$id = $options['id'];
		}

		$out = '<div id="' . $id . '" class="semorg-network"><svg width="960" height="600"></svg></div>';
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
					$roles_query = "{{#ask:[[Inhaber::" . $member . "]][[Gremium::" . $group . "||Koordinationskreis]]|?Rollentitel|format=array|sep=<ROLE>}}";
					$roles = $parser->RecursiveTagParse( $roles_query );
					if( $roles != '' ) {
						$roles = explode( '&lt;ROLE&gt;', $roles );
						$hierarchy_roles = [];
						foreach( $roles as $role ) {
							$role = explode( '&lt;PROP&gt;', $role );
							$hours_query = "{{#ask:[[Rolle::" . $role[0] . "]]|?Stundenaufwand|format=sum|default=1}}";
							$hours = $parser->RecursiveTagParse( $hours_query );
							//$hierarchy_roles[] = [ "name" => $role[1], "link" => $role[0], "type" => "role", "size" => $hours ];
							$hierarchy_roles[] = [ "name" => $role[1], "link" => $group, "type" => "role", "size" => $hours ];
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
	 * @param string $filter_string applied filters
	 *
	 * @return string Comma-separated list of values
	 */
	static function getValues( $parser, $property, $query_string ) {
		$property_parts = explode( '.', $property );
		$query = '{{#ask: [[semorg-' . $property_parts[0] . '::+]]' . $query_string . ' |mainlabel=- |headers=hide |limit=1000 |searchlabel= |?semorg-' . $property . '# }}';
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
		foreach( $filter_links_values as $filter_property => $values ) {
			$filterbox .= '<div class="semorg-filterbox-filter">';
			$filter_properties = explode( '.', $filter_property );
			/* allow nested property names (e.g. not only “feature-field“ but also “feature-field.semorg-feature-field”) */
			$filter_name_msg = 'semorg-field-' . str_replace( 'semorg-', '', end( $filter_properties  ) ) . '-name';
			$filter_name = wfMessage( $filter_name_msg );
			$filterbox .= '<span class="semorg-filterbox-filter-name">' . $filter_name . ': </span>';
			foreach( $values as &$value ) {
				if( $value != '' ) {
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

		for( $i = max( $page-5, 1 ); $i-1 < $count / $limit; $i++ ) {
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
		$pagination = '<div class="semorg-pagination">' . $pagination . '</div>';
		return $pagination;
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
		foreach( $filters_to_apply as $filter_property => $value ) {
			$query_string .= $filter_property . '=' . urlencode( $value ) . '&';
		}
		$query_string = substr( $query_string, 0, -1 );
		$title = $parser->getTitle();
		$filter_url = $title->getFullURL( $query_string );
		return $filter_url;
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
