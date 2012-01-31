<?php	##################
	#
	#	rah_link_search-plugin for Textpattern
	#	version 0.2
	#	by Jukka Svahn
	#	http://rahforum.biz
	#
	###################

/**
	The tag. Returns the list of matching links.
*/

	function rah_link_search($atts, $thing = NULL) {
		
		extract(lAtts(array(
			'form' => 'plainlinks',
			'grand_total' => 1,
			'wraptag' => '',
			'break' => '',
			'class' => ''
		),$atts));
		
		global $pretext, $has_article_tag, $thispage, $thislink;
		
		if($grand_total == 1)
			$thispage['grand_total'] = 0;
		
		if(empty($pretext['q']))
			return;
		
		/*
			We are an article tag,
			well kinda.
		*/
		
		$has_article_tag = true;
		$q = $pretext['q'];
		
		/*
			Quotes should be stripped
			from quote surrounded string
		*/
		
		$quoted = ($q[0] === '"') && ($q[strlen($q)-1] === '"');
		$q = doSlash($quoted ? trim(trim($q, '"')) : $q);
		
		/*
			Clean whitespace and escape the
			special syntax MySQL's like operator uses
		*/
		
		$q = 
			preg_replace('/\s+/', ' ', 
				str_replace(
					array('\\','%','_','\''),
					array('\\\\','\\%','\\_', '\\\''),
					$q
				)
			);
		
		/*
			Get matching IDs
		*/
		
		$rs = 
			safe_rows(
				'*, unix_timestamp(date) as uDate',
				'txp_link',
				"linkname like '%$q%' or description like '%$q%' or url like '%$q%'"
			);
		
		if(!$rs)
			return;
		
		if($grand_total == 1)
			$thispage['grand_total'] = count($rs);
			
		if(!$thing)
			$thing = fetch_form($form);
		
		$out = array();
		
		foreach($rs as $a) {
			extract($a);
			$thislink = 
				array(
					'id' => $id,
					'linkname' => $linkname,
					'url' => $url,
					'description' => $description,
					'date' => $uDate,
					'category' => $category,
					'author' => $author
				)
			;
			$out[] = parse($thing);
			$thislink = '';
		}
		
		return doWrap($out, $wraptag, $break, $class);
	}
?>