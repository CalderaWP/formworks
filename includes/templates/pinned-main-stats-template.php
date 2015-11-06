<?php
/**
 * partioal template for Main Stats
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */

?>

<div style="display: inline-block; margin: -12px -4px 0px;">{{> quick_stats}}</div>
<hr style="margin-top:0;">
<div style="clear:both;">
{{> core_events}}
</div>

<div style="width: 33%; min-width:260px; float: left; padding: 0px 12px 0px 0px; box-sizing: padding-box;">
	{{> summary_story}}
</div>

<div style="width: 22%; min-width:260px; float: left; padding: 0px 12px 0px 0px; box-sizing: padding-box;">
	{{> field_edits}}
</div>
<div style="width: 22%; min-width:260px; float: left; padding: 0px 12px 0px 0px; box-sizing: padding-box;">
	{{> field_drop_off}}
</div>


<?php /*


*/ 