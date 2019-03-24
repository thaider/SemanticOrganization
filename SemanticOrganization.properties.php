<?php
/**
 * Properties for the Semantic Organization Extension
 */
class SemanticOrganizationProperties {
	static $properties = [
		"account" => [ "id" => "AC","fields" => ["account-number" => [ "id" => "NU","type" => "num" ],"account-name" => [ "id" => "NA","type" => "txt" ], ] ],
		"account-special" => [ "id" => "AS","fields" => [ ] ],
		"agenda" => [ "id" => "AG","fields" => ["meeting" => [ "id" => "ME","type" => "wpg" ],"time-real" => [ "id" => "TR","type" => "num" ],"description" => [ "id" => "DE","type" => "txt" ],"title" => [ "id" => "TT","type" => "txt" ],"proposal" => [ "id" => "PR","type" => "txt" ],"person" => [ "id" => "PE","type" => "wpg" ],"tag" => [ "id" => "TA","type" => "txt" ],"type" => [ "id" => "TY","type" => "txt" ],"start" => [ "id" => "ST","type" => "num" ],"time" => [ "id" => "TI","type" => "num" ],"end" => [ "id" => "ET","type" => "num" ],"priority" => [ "id" => "PY","type" => "txt" ], ] ],
		"contact" => [ "id" => "CO","fields" => ["participant" => [ "id" => "PA","type" => "wpg" ],"details" => [ "id" => "DE","type" => "txt" ],"contact" => [ "id" => "CO","type" => "wpg" ],"date" => [ "id" => "DA","type" => "dat" ],"type" => [ "id" => "TY","type" => "txt" ],"location" => [ "id" => "LO","type" => "txt" ],"time" => [ "id" => "TI","type" => "txt" ],"subject" => [ "id" => "SU","type" => "txt" ], ] ],
		"department" => [ "id" => "DE","fields" => ["name" => [ "id" => "NA","type" => "txt" ], ] ],
		"gdpr-affected" => [ "id" => "GA","fields" => ["name" => [ "id" => "NA","type" => "txt" ],"processing" => [ "id" => "PR","type" => "wpg" ], ] ],
		"gdpr-category" => [ "id" => "GC","fields" => ["name" => [ "id" => "NA","type" => "txt" ],"affected" => [ "id" => "AF","type" => "wpg" ],"sensitive" => [ "id" => "SE","type" => "boo" ],"prosecution" => [ "id" => "PR","type" => "boo" ],"erase-time" => [ "id" => "ET","type" => "txt" ], ] ],
		"gdpr-processing" => [ "id" => "GP","fields" => ["name" => [ "id" => "NA","type" => "txt" ],"documents" => [ "id" => "DO","type" => "txt" ],"purpose" => [ "id" => "PU","type" => "txt" ],"measures" => [ "id" => "ME","type" => "txt" ],"legal-basis" => [ "id" => "LB","type" => "txt" ],"access" => [ "id" => "AC","type" => "txt" ],"application" => [ "id" => "AP","type" => "txt" ],"department" => [ "id" => "DP","type" => "wpg" ],"responsible" => [ "id" => "RE","type" => "wpg" ], ] ],
		"gdpr-recipient" => [ "id" => "GR","fields" => ["name" => [ "id" => "NA","type" => "txt" ],"category" => [ "id" => "CA","type" => "wpg" ],"third-country" => [ "id" => "TC","type" => "txt" ],"international-organization" => [ "id" => "IO","type" => "txt" ], ] ],
		"generator" => [ "id" => "GE","fields" => ["template-name" => [ "id" => "TN","type" => "txt" ],"template-id" => [ "id" => "TI","type" => "txt" ],"page-name" => [ "id" => "PN","type" => "" ], ] ],
		"generator-field" => [ "id" => "GF","fields" => ["template" => [ "id" => "TE","type" => "wpg" ],"prefix" => [ "id" => "PR","type" => "txt" ],"id" => [ "id" => "ID","type" => "txt" ],"suffix" => [ "id" => "SU","type" => "txt" ],"property-id" => [ "id" => "PID","type" => "txt" ],"fields" => [ "id" => "FI","type" => "txt" ],"name" => [ "id" => "NA","type" => "txt" ],"help" => [ "id" => "HE","type" => "txt" ],"parameters" => [ "id" => "PA","type" => "txt" ],"type" => [ "id" => "TY","type" => "txt" ], ] ],
		"group" => [ "id" => "G","fields" => ["name" => [ "id" => "NA","type" => "txt" ],"description" => [ "id" => "DE","type" => "txt" ],"permanent" => [ "id" => "PM","type" => "boo" ],"active" => [ "id" => "AC","type" => "boo" ],"host" => [ "id" => "HO","type" => "wpg" ], ] ],
		"group-member" => [ "id" => "GM","fields" => ["group" => [ "id" => "GR","type" => "wpg" ],"member" => [ "id" => "ME","type" => "wpg" ],"start-date" => [ "id" => "SD","type" => "dat" ],"end-date" => [ "id" => "ED","type" => "dat" ],"open-end" => [ "id" => "OE","type" => "boo" ], ] ],
		"invoice" => [ "id" => "IN","fields" => ["recipient" => [ "id" => "RE","type" => "txt" ],"payed" => [ "id" => "PA","type" => "boo" ],"storno" => [ "id" => "ST","type" => "boo" ],"due-date" => [ "id" => "DD","type" => "dat" ],"issuer" => [ "id" => "IS","type" => "wpg" ],"reminder-1-date" => [ "id" => "R1","type" => "dat" ],"payment-date" => [ "id" => "PD","type" => "dat" ],"number" => [ "id" => "NU","type" => "txt" ],"collection-date" => [ "id" => "CD","type" => "dat" ],"description" => [ "id" => "DS","type" => "txt" ],"date" => [ "id" => "DT","type" => "dat" ],"reminder-2-date" => [ "id" => "R2","type" => "dat" ],"department" => [ "id" => "DE","type" => "wpg" ],"amount-gross" => [ "id" => "AG","type" => "num" ],"year" => [ "id" => "YE","type" => "num" ],"project" => [ "id" => "PJ","type" => "wpg" ],"amount-net" => [ "id" => "AN","type" => "num" ], ] ],
		"meeting" => [ "id" => "M","fields" => ["group" => [ "id" => "GR","type" => "wpg" ],"guest" => [ "id" => "GU","type" => "wpg" ],"participant" => [ "id" => "PA","type" => "wpg" ],"project" => [ "id" => "PR","type" => "wpg" ],"excused" => [ "id" => "EX","type" => "wpg" ],"department" => [ "id" => "DE","type" => "wpg" ],"secretary" => [ "id" => "SE","type" => "wpg" ],"date" => [ "id" => "DT","type" => "dat" ],"moderator" => [ "id" => "MO","type" => "wpg" ],"time" => [ "id" => "TI","type" => "txt" ],"overview" => [ "id" => "OV","type" => "txt" ],"location" => [ "id" => "LO","type" => "txt" ], ] ],
		"meeting-department" => [ "id" => "","fields" => [ ] ],
		"meeting-group" => [ "id" => "","fields" => [ ] ],
		"meeting-project" => [ "id" => "","fields" => [ ] ],
		"membership" => [ "id" => "MS","fields" => ["member" => [ "id" => "ME","type" => "wpg" ],"payed" => [ "id" => "PY","type" => "boo" ],"shares" => [ "id" => "SH","type" => "num" ],"date" => [ "id" => "DT","type" => "dat" ],"type" => [ "id" => "TY","type" => "txt" ],"due-date" => [ "id" => "DD","type" => "dat" ],"pay-date" => [ "id" => "PD","type" => "dat" ], ] ],
		"membership-payment" => [ "id" => "MP","fields" => ["ref" => [ "id" => "RE","type" => "wpg" ],"date" => [ "id" => "DT","type" => "dat" ],"member" => [ "id" => "ME","type" => "wpg" ],"amount" => [ "id" => "AM","type" => "num" ], ] ],
		"person" => [ "id" => "P","fields" => ["ref" => [ "id" => "RF","type" => "wpg" ],"birthday" => [ "id" => "BD","type" => "dat" ],"workaddress" => [ "id" => "WAD","type" => "txt" ],"ssn" => [ "id" => "SS","type" => "txt" ],"email" => [ "id" => "EM","type" => "ema" ],"membership-end" => [ "id" => "MEE","type" => "dat" ],"prefix" => [ "id" => "PF","type" => "txt" ],"birthplace" => [ "id" => "BP","type" => "txt" ],"homepage" => [ "id" => "HP","type" => "uri" ],"legal-form" => [ "id" => "LF","type" => "txt" ],"worklocality" => [ "id" => "WLO","type" => "txt" ],"personnel" => [ "id" => "PE","type" => "boo" ],"firstname" => [ "id" => "FN","type" => "txt" ],"iban" => [ "id" => "IB","type" => "txt" ],"organization" => [ "id" => "OR","type" => "txt" ],"legal-registry" => [ "id" => "LR","type" => "txt" ],"workstreet" => [ "id" => "WST","type" => "txt" ],"personnel-number" => [ "id" => "PEN","type" => "num" ],"lastname" => [ "id" => "LN","type" => "txt" ],"membership" => [ "id" => "ME","type" => "boo" ],"note" => [ "id" => "NT","type" => "txt" ],"tag" => [ "id" => "TA","type" => "txt" ],"workpostalcode" => [ "id" => "WPC","type" => "txt" ],"personnel-start" => [ "id" => "PES","type" => "dat" ],"suffix" => [ "id" => "SF","type" => "txt" ],"membership-number" => [ "id" => "MEN","type" => "num" ],"gender" => [ "id" => "GD","type" => "txt" ],"vat-number" => [ "id" => "VAT","type" => "txt" ],"workphone" => [ "id" => "WPH","type" => "tel" ],"personnel-end" => [ "id" => "PEE","type" => "dat" ],"name" => [ "id" => "NA","type" => "txt" ],"membership-start" => [ "id" => "MES","type" => "dat" ], ] ],
		"person-contact" => [ "id" => "","fields" => [ ] ],
		"person-legal" => [ "id" => "","fields" => [ ] ],
		"person-ref" => [ "id" => "PR","fields" => ["name" => [ "id" => "NA","type" => "wpg" ],"role" => [ "id" => "RO","type" => "txt" ],"tag" => [ "id" => "TG","type" => "txt" ],"ref" => [ "id" => "RF","type" => "wpg" ], ] ],
		"person-user" => [ "id" => "","fields" => [ ] ],
		"process" => [ "id" => "PS","fields" => ["title" => [ "id" => "TI","type" => "txt" ],"description" => [ "id" => "DE","type" => "txt" ],"owner" => [ "id" => "OW","type" => "wpg" ],"visualization" => [ "id" => "VI","type" => "wpg" ], ] ],
		"project" => [ "id" => "PJ","fields" => ["title" => [ "id" => "TT","type" => "txt" ],"team-extern" => [ "id" => "TE","type" => "wpg" ],"start" => [ "id" => "ST","type" => "dat" ],"end" => [ "id" => "EN","type" => "dat" ],"contact-person" => [ "id" => "CP","type" => "wpg" ],"desc" => [ "id" => "DE","type" => "txt" ],"team-intern" => [ "id" => "TI","type" => "wpg" ], ] ],
		"role" => [ "id" => "RO","fields" => ["name" => [ "id" => "NA","type" => "txt" ],"group" => [ "id" => "GR","type" => "wpg" ], ] ],
		"role-holder" => [ "id" => "RH","fields" => ["role" => [ "id" => "RO","type" => "wpg" ],"holder" => [ "id" => "HO","type" => "wpg" ],"start-date" => [ "id" => "SD","type" => "dat" ],"end-date" => [ "id" => "ED","type" => "dat" ], ] ],
		"role-ref" => [ "id" => "RR","fields" => ["ref" => [ "id" => "RE","type" => "wpg" ],"role" => [ "id" => "RO","type" => "wpg" ], ] ],
		"role-task" => [ "id" => "RT","fields" => ["role" => [ "id" => "RO","type" => "wpg" ],"name" => [ "id" => "NA","type" => "txt" ],"description" => [ "id" => "DE","type" => "txt" ],"hours" => [ "id" => "HO","type" => "num" ],"month" => [ "id" => "MO","type" => "num" ], ] ],
		"salary" => [ "id" => "SA","fields" => ["employee" => [ "id" => "EM","type" => "wpg" ],"date" => [ "id" => "DT","type" => "dat" ],"amount" => [ "id" => "AM","type" => "num" ],"hours" => [ "id" => "HR","type" => "num" ], ] ],
		"transfer" => [ "id" => "TR","fields" => ["sender" => [ "id" => "SE","type" => "wpg" ],"list" => [ "id" => "LI","type" => "wpg" ],"recipient" => [ "id" => "RE","type" => "wpg" ],"listed" => [ "id" => "LD","type" => "boo" ],"amount" => [ "id" => "AM","type" => "num" ],"year" => [ "id" => "YE","type" => "num" ],"date" => [ "id" => "DA","type" => "dat" ],"details" => [ "id" => "DE","type" => "txt" ],"subject" => [ "id" => "SU","type" => "txt" ], ] ],
		"transfer-list" => [ "id" => "TL","fields" => ["date" => [ "id" => "DT","type" => "dat" ], ] ],
		"vacation" => [ "id" => "VA","fields" => ["person" => [ "id" => "PE","type" => "wpg" ],"start-date" => [ "id" => "SD","type" => "dat" ],"end-date" => [ "id" => "ED","type" => "dat" ],"note" => [ "id" => "NO","type" => "txt" ], ] ],
		"working-time" => [ "id" => "WT","fields" => ["person" => [ "id" => "PE","type" => "wpg" ],"day" => [ "id" => "DA","type" => "num" ],"time" => [ "id" => "TI","type" => "txt" ],"note" => [ "id" => "NO","type" => "txt" ], ] ],
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
