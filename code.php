<?php	##################
	#
	#	rah_link_search-plugin for Textpattern
	#	version 0.1
	#	by Jukka Svahn
	#	http://rahforum.biz
	#
	###################

	function rah_link_search($atts, $thing = NULL) {
		extract(lAtts(array(
			'form' => 'plainlinks'
		),$atts));
		global $thislink;
		$out = array();
		$q = doSlash(gps('q'));
		if($q) {
			$rs = safe_rows_start('*, unix_timestamp(date) as uDate','txp_link',"linkname rlike '$q' or description rlike '$q' or url like '$q'");
			while($a = nextRow($rs)) {
				extract($a);
				$thislink = array(
					'id' => $id,
					'linkname' => $linkname,
					'url' => $url,
					'description' => $description,
					'date' => $uDate,
					'category' => $category,
				);
				$out[] = ($thing) ? parse($thing) : parse_form($form);
				$thislink = '';
			}
		}
		return implode('',$out);
	}
?>