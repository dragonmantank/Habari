<?php 
class MyFormats extends Format
{
	function summarize($value) {
		if(URL::o()->handleraction != 'post') {
			list($ret) = explode("\n", wordwrap($value, 200));
			return $ret . (strlen($value) > strlen($ret) ? '...' : '');
		}
		return $value;
	}
}

//Format::apply('summarize', 'out_post_content'); 
Format::apply('autop', 'out_post_content'); 
Format::apply('tag_and_list', 'out_post_tags');
Format::apply('nice_date', 'out_post_pubdate', 'F j, Y g:ia');
?>
