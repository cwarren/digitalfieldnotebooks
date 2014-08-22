<?php

	// general utility functions

	function util_genRandomIdString($len = 128) {
		$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#%^&*()-_=+,.<>?~';
		$id   = '';
		for ($i = 0; $i < $len; $i++) {
			$id .= substr($pool, rand(0, strlen($pool) - 1), 1);
		}
		return $id;
	}

    function util_genRandomAlphNumString($len = 128) {
    $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $id   = '';
    for ($i = 0; $i < $len; $i++) {
        $id .= substr($pool, rand(0, strlen($pool) - 1), 1);
    }
    return $id;
}
	function util_wipeSession() {
		unset($_SESSION['isAuthenticated']);
		unset($_SESSION['fingerprint']);
		unset($_SESSION['userdata']);
		unset($_SESSION['digitalfieldnotebooks_id']);
		$_COOKIE['digitalfieldnotebooks_id'] = "";
		setcookie("digitalfieldnotebooks_id", "", time() - 3600);

		return;
	}

	function util_redirectToAppHome($status = "", $msg_key_or_text = '', $log = 0) {
		// ensure value conforms to expectations
		if ($status != "success" && $status != "failure" && $status != "info") {
			# security: ensure status has a valid value
            header('Location: ' . APP_FOLDER . '/index.php');
            exit;
		}

        if ($log > 0) {
            # TODO: Add database log capability
        }

    	header('Location: ' . APP_FOLDER . '/index.php?' . $status . '=' . urlencode($msg_key_or_text));
		exit;
	}

	function util_redirectToAppHomeWithPrejudice() {
		util_wipeSession();
		util_redirectToAppHome();
	}

	// this section adds and checks a random id string for the browser and does some checking against that ID string.
	// this makes it much harder to spoof sessions
	function util_doDigitalFieldNotebooksIdSecurityCheck() {
		if ((!isset($_COOKIE["digitalfieldnotebooks_id"])) || (!$_COOKIE["digitalfieldnotebooks_id"])) {
			if (isset($_SESSION['digitalfieldnotebooks_id']) && ($_SESSION['digitalfieldnotebooks_id'])) { // the session has an digitalfieldnotebooks id, but there was no cookie set for it - highly suspicious
				// TODO: log and/or message?
				util_redirectToAppHomeWithPrejudice();
			}
			$digitalfieldnotebooks_id = util_genRandomIdString(300);
			setcookie("digitalfieldnotebooks_id", $digitalfieldnotebooks_id);
			$_SESSION['digitalfieldnotebooks_id'] = $digitalfieldnotebooks_id;
		}
		elseif ((!isset($_SESSION['digitalfieldnotebooks_id'])) || ($_COOKIE["digitalfieldnotebooks_id"] != $_SESSION['digitalfieldnotebooks_id'])) {
			// there was an appropriately named cookie, but the value doesn't match the one associated with this session
			// TODO: log and/or message?
			util_redirectToAppHomeWithPrejudice();
		}
	}

	function util_generateRequestFingerprint() {
		util_doDigitalFieldNotebooksIdSecurityCheck();

		return md5(FINGERPRINT_SALT . $_SESSION["digitalfieldnotebooks_id"] .
			(isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 18) : 'nouseragent')
		);
	}


	// a quick handle for a slightly complex condition check
	function util_checkAuthentication() {
		return (isset($_SESSION['isAuthenticated']) && ($_SESSION['isAuthenticated']));
	}

	function util_createDbConnection() {
		//print_r($_SERVER);
		//        TODO: figure out how to handle this for command line scripts (possibly build this directly into the command line header, but still need to resolve test vs live)
		//		if ((array_key_exists('SERVER_NAME',$_SERVER)) && ($_SERVER['SERVER_NAME'] == 'localhost')) {
		if ($_SERVER['SERVER_NAME'] == 'localhost') {
			return new PDO("mysql:host=" . TESTING_DB_SERVER . ";dbname=" . TESTING_DB_NAME . ";port=3306", TESTING_DB_USER, TESTING_DB_PASS);
		}
		return new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";port=3306", DB_USER, DB_PASS);
	}


	# validation routine: trim fxn strips (various types of) whitespace characters from the beginning and end of a string
	function util_quoteSmart($value) {
		// stripslashes — Un-quotes a quoted string
		// trim — Strip whitespace (or other characters) from the beginning and end of a string
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
			$value = trim($value);
		}
		return $value;
	}

	# Output an object wrapped with HTML PRE tags for pretty output
	function util_prePrintR($obj) {
		echo "<pre>";
		print_r($obj);
		echo "</pre>";
		return TRUE;
	}

	/**
	 * takes: a time string of the form YYYY-MM-DD HH:MI:SS (i.e. as it comes from MySQL)
	 * returns: a hash with the following keys-
	 * YYYY - the year
	 * Y - the year
	 * MM - the month with 2 characters (leading 0)
	 * M - the month with 1 character if < 10
	 * DD - the day with 2 characters
	 * D - the day with 1 character if < 10
	 * hh - the 24-clock hour with 2 characters
	 * h - the 24-clock hour with 1 character if < 10
	 * hhap - the 12-clock with 2 characters
	 * hap - the 12-clock with 1 character if < 10
	 * ap - AM or PM
	 * mm - the minutes with 2 characters
	 * m - the minutes with 1 character if < 10
	 * ss - the seconds with 2 characters
	 * s - the seconds with 1 character if < 10
	 */
	function util_processTimeString($ts) {
		$parts = preg_split('/[-: ]/', $ts);

		$res = [
			'YYYY' => $parts[0],
			'Y'    => $parts[0],
			'MM'   => $parts[1],
			'M'    => $parts[1],
			'DD'   => $parts[2],
			'D'    => $parts[2],
			'hh'   => $parts[3],
			'h'    => $parts[3],
			'hhap' => $parts[3],
			'hap'  => $parts[3],
			'ap'   => ($parts[3] < 12) ? 'AM' : 'PM',
			'mi'   => $parts[4],
			'm'    => $parts[4],
			'ss'   => $parts[5],
			's'    => $parts[5]
		];

		if ($res['hhap'] > 12) {
			$res['hhap'] -= 12;
		}
		if ($res['hhap'] < 1) {
			$res['hhap'] = '12';
		}
		if ($res['hap'] > 12) {
			$res['hap'] -= 12;
		}
		if ($res['hap'] < 1) {
			$res['hap'] = '12';
		}

		$res['M'] = preg_replace('/^0+/', '', $res['M']);

		$res['D'] = preg_replace('/^0+/', '', $res['D']);

		$res['h'] = preg_replace('/^0+/', '', $res['h']);
		if (!$res['h']) {
			$res['h'] = '0';
		}

		$res['hap'] = preg_replace('/^0+/', '', $res['hap']);
		if (!$res['hap']) {
			$res['hap'] = '0';
		}

		$res['m'] = preg_replace('/^0+/', '', $res['m']);
		if (!$res['m']) {
			$res['m'] = '0';
		}

		$res['s'] = preg_replace('/^0+/', '', $res['s']);
		if (!$res['s']) {
			$res['s'] = '0';
		}

		$res['date'] = $res['Y'] . '/' . $res['M'] . '/' . $res['D'];

		return $res;
	}

	function util_timeRangeString($tstart, $tstop) {
		if (!is_array($tstart)) {
			$tstart = util_processTimeString($tstart);
		}
		if (!is_array($tstop)) {
			$tstop = util_processTimeString($tstop);
		}

		$first_part  = $tstart['date'] . ' ' . $tstart['hap'] . ':' . $tstart['mi'];
		$second_part = '';

		if ($tstart['date'] != $tstop['date']) {
			$first_part .= ' ' . $tstart['ap'];
			$second_part = $tstop['date'] . ' ' . $tstop['hap'] . ':' . $tstop['mi'] . ' ' . $tstop['ap'];
		}
		elseif ($tstart['ap'] != $tstop['ap']) {
			$first_part .= ' ' . $tstart['ap'];
			$second_part = $tstop['hap'] . ':' . $tstop['mi'] . ' ' . $tstop['ap'];
		}
		else {
			$second_part = $tstop['hap'] . ':' . $tstop['mi'] . ' ' . $tstop['ap'];
		}

		return "$first_part-$second_part";
	}

    function util_displayMessage($type,$key_or_text) {
        $alert_type = 'alert-info';
        $alert_title = util_lang('alert_info');
        if ($type == 'error') {
            $alert_type = 'alert-error';
            $alert_title = util_lang('alert_error');
        } else
        if ($type == 'success') {
            $alert_type = 'alert-success';
            $alert_title = util_lang('alert_success');
        }

        $msg_text = util_lang($key_or_text);
        if (preg_match('/UNKNOWN LANGUAGE LABEL/',$msg_text)) {
            $msg_text = htmlentities($key_or_text);
        }

        echo "<div class=\"alert $alert_type\">";
        echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>";
        echo "<h4>$alert_title</h4>";
        echo $msg_text;
        echo "</div>";
    }

    function util_lang($label) {
        global $LANGUAGE, $CUR_LANG_SET;

        $ret = "UNKNOWN LANGUAGE LABEL '$label' FOR LANGUAGE '$CUR_LANG_SET'";

        if (array_key_exists($label, $LANGUAGE[$CUR_LANG_SET])) {
            $ret =  $LANGUAGE[$CUR_LANG_SET][$label];
        }

//        util_prePrintR($ret);
        return $ret;
    }

    function util_listItemTag($id = '', $class_ar = [], $other_attr_hash = []) {
        $li = '<li';
        if ($id) {
            $li .= " id=\"$id\"";
        }
        if ($class_ar) {
            $li .= " class=\"" . implode(' ', $class_ar) . '"';
        }

        $hash_keys = array_keys($other_attr_hash);
        sort($hash_keys);
        foreach ($hash_keys as $k) {
            $v = $other_attr_hash[$k];
            $li .= " $k=\"$v\"";
        }
        $li .= '>';
        return $li;
    }