<?php

/**
 * Rah_link_search plugin for Textpattern CMS
 *
 * @author Jukka Svahn
 * @date 2009-
 * @license GNU GPLv2
 * @link http://rahforum.biz/plugins/rah_link_search
 *
 * Copyright (C) 2012 Jukka Svahn <http://rahforum.biz>
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
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
		
		$has_article_tag = true;
		$q = trim($pretext['q']);
		$quoted = ($q[0] === '"') && ($q[strlen($q)-1] === '"');
		$q = doSlash($quoted ? trim(trim($q, '"')) : $q);
		
		$q = 
			preg_replace('/\s+/', ' ', 
				str_replace(
					array('\\','%','_','\''),
					array('\\\\','\\%','\\_', '\\\''),
					$q
				)
			);
		
		$fields =
			array(
				'linkname',
				'description',
				'url'
			);
		
		if($quoted)
			foreach($fields as $field)
				$sql[] = "lower($field) like lower('%$q%')";
		else {
			$words = explode(' ', $q);

			foreach($words as $word) {
				$fsql = array();
				
				foreach($fields as $field)
					$fsql[] = "lower($field) like lower('%$word%')";
				
				$sql[] = '('.implode(' or ', $fsql).')';
			}
		}

		$rs = 
			safe_rows(
				'*, unix_timestamp(date) as uDate',
				'txp_link',
				implode(($quoted ? ' or ' : ' and '), $sql)
			);
		
		if(!$rs)
			return;
		
		if($grand_total == 1)
			$thispage['grand_total'] = count($rs);
			
		if($thing === NULL)
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