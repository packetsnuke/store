<?php

/**
 * An array of flat rate box pricing - 2016
 * As of Jan 2016 USPS has removed all discounts for Click N Ship/Business/Online rate.  All rates are returning retail rates now.
 * We're keeping both just in case they change their minds later but for now will make the rates the same for both.
 * Priority mail flat rate envelope prices updated on 04/13/2017 according to https://www.usps.com/ship/priority-mail.htm
 */
return apply_filters( 'wc_usps_flat_rate_box_pricing', array(

	// Priority Mail Express

		// Priority Mail Express Flat Rate Envelope
		"d13"     => array(
			"retail" => "23.75",
			"online" => "23.75",
		),
		// Priority Mail Express Legal Flat Rate Envelope
		"d30"     => array(
			"retail" => "23.95",
			"online" => "23.95",
		),
		// Priority Mail Express Padded Flat Rate Envelope
		"d63"     => array(
			"retail" => "24.95",
			"online" => "24.95",
		),

	// Priority Mail Boxes

		// Priority Mail Flat Rate Medium Box
		"d17"     => array(
			"retail" => "13.60",
			"online" => "13.60",
		),
		// Priority Mail Flat Rate Medium Box
		"d17b"     => array(
			"retail" => "13.60",
			"online" => "13.60",
		),
		// Priority Mail Flat Rate Large Box
		"d22"     => array(
			"retail" => "18.85",
			"online" => "18.85",
		),

		// Priority Mail Flat Rate Large Box
		"d22a"     => array(
			"retail" => "18.85",
			"online" => "18.85",
		),
		// Priority Mail Flat Rate Small Box
		"d28"     => array(
			"retail" => "7.15",
			"online" => "7.15",
		),

	// Priority Mail Envelopes

		// Priority Mail Flat Rate Envelope
		"d16"     => array(
			"retail" => "6.65",
			"online" => "6.65",
		),
		// Priority Mail Padded Flat Rate Envelope
		"d29"     => array(
			"retail" => "7.20",
			"online" => "7.20",
		),
		// Priority Mail Gift Card Flat Rate Envelope
		"d38"     => array(
			"retail" => "6.65",
			"online" => "6.65",
		),
		// Priority Mail Window Flat Rate Envelope
		"d40"     => array(
			"retail" => "6.65",
			"online" => "6.65",
		),
		// Priority Mail Small Flat Rate Envelope
		"d42"     => array(
			"retail" => "6.65",
			"online" => "6.65",
		),
		// Priority Mail Legal Flat Rate Envelope
		"d44"     => array(
			"retail" => "6.95",
			"online" => "6.95",
		),

	// International Priority Mail Express

		// Priority Mail Express Flat Rate Envelope
		"i13"     => array(
			"retail"    => array(
				'*'  => "63.50",
				'CA' => "41.50"
			)
		),
		// Priority Mail Express Legal Flat Rate Envelope
		"i30"     => array(
			"retail"    => array(
				'*'  => "63.50",
				'CA' => "41.50"
			)
		),
		// Priority Mail Express Padded Flat Rate Envelope
		"i63"     => array(
			"retail"    => array(
				'*'  => "63.50",
				'CA' => "41.50"
			)
		),

	// International Priority Mail

		// Priority Mail Flat Rate Envelope
		"i8"      => array(
			"retail"    => array(
				'*'  => "33.95",
				'CA' => "23.95"
			)
		),
		// Priority Mail Padded Flat Rate Envelope
		"i29"      => array(
			"retail"    => array(
				'*'  => "33.95",
				'CA' => "23.95"
			)
		),
		// Priority Mail Flat Rate Small Box
		"i16"     => array(
			"retail"    => array(
				'*'  => "34.95",
				'CA' => "24.95"
			)
		),
		// Priority Mail Flat Rate Medium Box
		"i9"      => array(
			"retail"    => array(
				'*'  => "75.95",
				'CA' => "45.95"
			)
		),
		// Priority Mail Flat Rate Medium Box
		"i9b"      => array(
			"retail"    => array(
				'*'  => "75.95",
				'CA' => "45.95"
			)
		),
		// Priority Mail Flat Rate Large Box
		"i11"     => array(
			"retail"    => array(
				'*'  => "95.95",
				'CA' => "59.95"
			)
		),
) );
