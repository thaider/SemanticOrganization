<?php
/**
 * Properties for the Semantic Organization Extension
 */
class SemanticOrganizationProperties {
	static $properties = [
		"agenda" => [ "id" => "AG","fields" => ["meeting" => [ "id" => "ME","type" => "wpg" ],"description" => [ "id" => "DE","type" => "txt" ],"title" => [ "id" => "TT","type" => "txt" ],"proposal" => [ "id" => "PR","type" => "txt" ],"person" => [ "id" => "PE","type" => "wpg" ],"tag" => [ "id" => "TA","type" => "txt" ],"type" => [ "id" => "TY","type" => "txt" ],"time" => [ "id" => "TI","type" => "num" ],"priority" => [ "id" => "PR","type" => "txt" ], ] ],
		"department" => [ "id" => "DE","fields" => ["name" => [ "id" => "NA","type" => "txt" ], ] ],
		"gdpr-affected" => [ "id" => "GA","fields" => ["name" => [ "id" => "NA","type" => "txt" ],"processing" => [ "id" => "PR","type" => "wpg" ], ] ],
		"gdpr-category" => [ "id" => "GC","fields" => ["name" => [ "id" => "NA","type" => "txt" ],"affected" => [ "id" => "AF","type" => "wpg" ],"sensitive" => [ "id" => "SE","type" => "boo" ],"prosecution" => [ "id" => "PR","type" => "boo" ],"erase-time" => [ "id" => "ET","type" => "txt" ], ] ],
		"gdpr-processing" => [ "id" => "GP","fields" => ["name" => [ "id" => "NA","type" => "txt" ],"documents" => [ "id" => "DO","type" => "txt" ],"purpose" => [ "id" => "PU","type" => "txt" ],"measures" => [ "id" => "ME","type" => "txt" ],"legal-basis" => [ "id" => "LB","type" => "txt" ],"access" => [ "id" => "AC","type" => "txt" ],"application" => [ "id" => "AP","type" => "txt" ],"department" => [ "id" => "DP","type" => "wpg" ],"responsible" => [ "id" => "RE","type" => "wpg" ], ] ],
		"gdpr-recipient" => [ "id" => "GR","fields" => ["name" => [ "id" => "NA","type" => "txt" ],"category" => [ "id" => "CA","type" => "wpg" ],"third-country" => [ "id" => "TC","type" => "txt" ],"international-organization" => [ "id" => "IO","type" => "txt" ], ] ],
		"generator" => [ "id" => "GE","fields" => ["template-name" => [ "id" => "TN","type" => "txt" ],"template-id" => [ "id" => "TI","type" => "txt" ],"page-name" => [ "id" => "PN","type" => "" ], ] ],
		"generator-field" => [ "id" => "GF","fields" => ["template" => [ "id" => "TE","type" => "wpg" ],"prefix" => [ "id" => "PR","type" => "txt" ],"id" => [ "id" => "ID","type" => "txt" ],"suffix" => [ "id" => "SU","type" => "txt" ],"property-id" => [ "id" => "PID","type" => "txt" ],"fields" => [ "id" => "FI","type" => "txt" ],"name" => [ "id" => "NA","type" => "txt" ],"help" => [ "id" => "HE","type" => "txt" ],"parameters" => [ "id" => "PA","type" => "txt" ],"type" => [ "id" => "TY","type" => "txt" ], ] ],
		"group" => [ "id" => "G","fields" => [ ] ],
		"group-member" => [ "id" => "GM","fields" => [ ] ],
		"invoice" => [ "id" => "IN","fields" => ["recipient" => [ "id" => "RE","type" => "txt" ],"project" => [ "id" => "PJ","type" => "wpg" ],"amount-net" => [ "id" => "AN","type" => "num" ],"issuer" => [ "id" => "IS","type" => "wpg" ],"storno" => [ "id" => "ST","type" => "boo" ],"due-date" => [ "id" => "DD","type" => "dat" ],"number" => [ "id" => "NU","type" => "txt" ],"payment-date" => [ "id" => "PD","type" => "dat" ],"date" => [ "id" => "DT","type" => "dat" ],"description" => [ "id" => "DS","type" => "txt" ],"year" => [ "id" => "YE","type" => "num" ],"department" => [ "id" => "DE","type" => "wpg" ],"amount-gross" => [ "id" => "AG","type" => "num" ], ] ],
		"invoices" => [ "id" => "IS","fields" => ["year" => [ "id" => "YE","type" => "num" ], ] ],
		"meeting" => [ "id" => "M","fields" => ["group" => [ "id" => "GR","type" => "wpg" ],"guest" => [ "id" => "GU","type" => "wpg" ],"participant" => [ "id" => "PA","type" => "wpg" ],"project" => [ "id" => "PR","type" => "wpg" ],"excused" => [ "id" => "EX","type" => "wpg" ],"department" => [ "id" => "DE","type" => "wpg" ],"secretary" => [ "id" => "SE","type" => "wpg" ],"date" => [ "id" => "DT","type" => "dat" ],"moderator" => [ "id" => "MO","type" => "wpg" ],"time" => [ "id" => "TI","type" => "txt" ],"overview" => [ "id" => "OV","type" => "txt" ],"location" => [ "id" => "LO","type" => "txt" ], ] ],
		"meeting-department" => [ "id" => "","fields" => [ ] ],
		"meeting-group" => [ "id" => "","fields" => [ ] ],
		"meeting-project" => [ "id" => "","fields" => [ ] ],
		"membership" => [ "id" => "MS","fields" => ["member" => [ "id" => "ME","type" => "wpg" ],"payed" => [ "id" => "PY","type" => "boo" ],"shares" => [ "id" => "SH","type" => "num" ],"date" => [ "id" => "DT","type" => "dat" ],"type" => [ "id" => "TY","type" => "txt" ],"due-date" => [ "id" => "DD","type" => "dat" ],"pay-date" => [ "id" => "PD","type" => "dat" ], ] ],
		"membership-payment" => [ "id" => "MP","fields" => ["ref" => [ "id" => "RE","type" => "wpg" ],"date" => [ "id" => "DT","type" => "dat" ],"member" => [ "id" => "ME","type" => "wpg" ],"amount" => [ "id" => "AM","type" => "num" ], ] ],
		"person" => [ "id" => "P","fields" => ["ref" => [ "id" => "RF","type" => "wpg" ],"birthday" => [ "id" => "BD","type" => "dat" ],"ssn" => [ "id" => "SS","type" => "txt" ],"workaddress" => [ "id" => "WAD","type" => "txt" ],"email" => [ "id" => "EM","type" => "ema" ],"membership-end" => [ "id" => "MEE","type" => "dat" ],"prefix" => [ "id" => "PF","type" => "txt" ],"birthplace" => [ "id" => "BP","type" => "txt" ],"legal-form" => [ "id" => "LF","type" => "txt" ],"homepage" => [ "id" => "HP","type" => "uri" ],"worklocality" => [ "id" => "WLO","type" => "txt" ],"personnel" => [ "id" => "PE","type" => "boo" ],"firstname" => [ "id" => "FN","type" => "txt" ],"iban" => [ "id" => "IB","type" => "txt" ],"legal-registry" => [ "id" => "LR","type" => "txt" ],"organization" => [ "id" => "OR","type" => "txt" ],"workstreet" => [ "id" => "WST","type" => "txt" ],"personnel-number" => [ "id" => "PEN","type" => "num" ],"lastname" => [ "id" => "LN","type" => "txt" ],"membership" => [ "id" => "ME","type" => "boo" ],"tag" => [ "id" => "TA","type" => "txt" ],"note" => [ "id" => "NT","type" => "txt" ],"workpostalcode" => [ "id" => "WPC","type" => "txt" ],"personnel-start" => [ "id" => "PES","type" => "dat" ],"suffix" => [ "id" => "SF","type" => "txt" ],"membership-number" => [ "id" => "MEN","type" => "num" ],"gender" => [ "id" => "GD","type" => "txt" ],"workphone" => [ "id" => "WPH","type" => "tel" ],"personnel-end" => [ "id" => "PEE","type" => "dat" ],"name" => [ "id" => "NA","type" => "txt" ],"membership-start" => [ "id" => "MES","type" => "dat" ], ] ],
		"person-contact" => [ "id" => "","fields" => [ ] ],
		"person-legal" => [ "id" => "P","fields" => ["form" => [ "id" => "LF","type" => "" ],"registry" => [ "id" => "LR","type" => "" ], ] ],
		"person-ref" => [ "id" => "PR","fields" => [ ] ],
		"person-user" => [ "id" => "","fields" => [ ] ],
		"process" => [ "id" => "PS","fields" => ["title" => [ "id" => "TI","type" => "txt" ],"description" => [ "id" => "DE","type" => "txt" ],"owner" => [ "id" => "OW","type" => "wpg" ],"visualization" => [ "id" => "VI","type" => "wpg" ], ] ],
		"project" => [ "id" => "PJ","fields" => [ ] ],
		"role" => [ "id" => "RO","fields" => [ ] ],
		"role-holder" => [ "id" => "RH","fields" => [ ] ],
		"role-ref" => [ "id" => "RR","fields" => ["ref" => [ "id" => "RE","type" => "wpg" ],"role" => [ "id" => "RO","type" => "wpg" ], ] ],
		"role-task" => [ "id" => "RT","fields" => ["role" => [ "id" => "RO","type" => "wpg" ],"name" => [ "id" => "NA","type" => "txt" ],"description" => [ "id" => "DE","type" => "txt" ],"hours" => [ "id" => "HO","type" => "num" ],"month" => [ "id" => "MO","type" => "num" ], ] ],
		"salary" => [ "id" => "SA","fields" => ["employee" => [ "id" => "EM","type" => "wpg" ],"date" => [ "id" => "DT","type" => "dat" ],"amount" => [ "id" => "AM","type" => "num" ],"hours" => [ "id" => "HR","type" => "num" ], ] ],
		"transfer" => [ "id" => "TR","fields" => ["sender" => [ "id" => "SE","type" => "wpg" ],"recipient" => [ "id" => "RE","type" => "wpg" ],"amount" => [ "id" => "AM","type" => "num" ],"date" => [ "id" => "DA","type" => "dat" ],"details" => [ "id" => "DE","type" => "txt" ], ] ],
		"vacation" => [ "id" => "VA","fields" => ["person" => [ "id" => "PE","type" => "wpg" ],"start-date" => [ "id" => "SD","type" => "dat" ],"end-date" => [ "id" => "ED","type" => "dat" ],"note" => [ "id" => "NO","type" => "txt" ], ] ],
		"working-time" => [ "id" => "WT","fields" => [ ] ],
	];

	public static function registerProperty( $id, $typeid, $label ) {
		if ( class_exists( 'SMWDIProperty' ) ) {
			SMWDIProperty::registerProperty( $id, $typeid, $label, true );
		} else {
			SMWPropertyValue::registerProperty( $id, $typeid, $label, true );
		}
	}


	public static function getPropertyNamesForTemplate( $template_name ) {
		if( isset( self::$properties[$template_name] ) ) {
			return array_keys( self::$properties[$template_name]['fields'] );
		} else {
			return false;
		}
	}


	public static function getPropertiesForTemplate( $template_name ) {
		if( isset( self::$properties[$template_name] ) ) {
			return self::$properties[$template_name]['fields'];
		} else {
			return false;
		}
	}


	public static function onsmwInitProperties() {
		foreach( self::$properties as $template_name => $template ) {
			$template_id = $template['id'];
			foreach( $template['fields'] as $field_name => $field ) {
				self::registerProperty( '_SO_' . $template_id . '_' . $field['id'], '_' . $field['type'], 'Semorg-' . $template_name . '-' . $field_name );
			}
		}
	}

}
