<?php
if (!defined('SMF'))
	die('No direct access...');
	
function template_twosichatLinks(){

global $context, $txt, $scripturl;

echo '
	<table class="table_grid" cellspacing="0" width="100%">
		<tr class="catbg">
			<td align="left" class="windowbg2">'.$txt['2sichat_ord'].'</td>
			<td align="left" class="windowbg2">'.$txt['2sichat_title'].'</td>
			<td align="left" class="windowbg2">'.$txt['2sichat_vis'].'</td>
		</tr>		';
foreach ($context['gadgets'] as $row)
{
    echo'
		<tr class="windowbg">
			<td align="left" class="windowbg2">'.$row['ord'].'</td>
                   <td align="left" class="windowbg2">
				<strong>'.$row['title'].'</strong>
				<div style="float:right;padding-right:40px;">
					<button type="button" onclick="javascript:window.location.href = \''.$scripturl.'?action=admin;area=plugsetting;sa=link;edit='.$row['id'].'\';">'.$txt['2sichat_edit'].'</button>
					<button type="button" onclick="javascript:window.location.href = \''.$scripturl.'?action=admin;area=plugsetting;sa=link;delete='.$row['id'].'\';">'.$txt['2sichat_delete'].'</button>
	 			</div>
	 		</td>
            <td align="left" class="windowbg2">
				'.($row['vis'] == 0 ? $txt['2sichat_vis0'] : '').'
				'.($row['vis'] == 1 ? $txt['2sichat_vis1'] : '').'
				'.($row['vis'] == 2 ? $txt['2sichat_vis2'] : '').'
				'.($row['vis'] == 3 ? $txt['2sichat_vis3'] : '').'
			</td>
        </tr>';
}
echo'
 	<tr>
		<td align="left" class="windowbg2" colspan="3">
			<button type="button" onclick="javascript:window.location.href = \''.$scripturl.'?action=admin;area=plugsetting;sa=link;edit\';">'.$txt['2sichat_ladd'].'</button>
		</td>
	</tr>
</table>';

}

function template_twosichatLinkAdd(){

	global $context, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=admin;area=plugsetting;sa=link;save" method="post" accept-charset="', $context['character_set'], '">
			<table width="100%" border="0" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
				<td align="left" class="windowbg2">
						<strong>'.$txt['2sichat_linkyyy'].'</strong>
					</td>
                         <td align="left" class="windowbg2">
					 <input type="checkbox" name="newwin" class="input_check" ' . (!empty($context['gadget']['newwin']) ? ' checked="checked" ' : '') . ' />
					</td>
				</tr>
					<td align="left" class="windowbg2">
						<strong>'.$txt['2sichat_title'].'</strong>
					</td>
                         <td align="left" class="windowbg2">
						<input type="text" name="title" size="64" maxlength="255" value="'.(isset($context['gadget']['title']) ? $context['gadget']['title']:'').'" /><br />
					</td>
				</tr>
				<tr>
					<td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_laddu'].'</strong>
                         </td>
                         <td align="left" class="windowbg2">
						<input type="text" name="url" size="64" maxlength="255" value="'.(isset($context['gadget']['url']) ? $context['gadget']['url']:'').'" />
					</td>
				</tr>
				<tr>
                         <td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_laddiu'].'</strong>
                         </td>
                         <td align="left" class="windowbg2">
						<input type="text" name="image" size="64" maxlength="255" value="'.(isset($context['gadget']['image']) ? $context['gadget']['image']:'').'" /><br />
					</td>
				</tr>
				
				<tr>
                         <td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_vis'].'</strong>
                         </td>
					<td align="left" class="windowbg2">
						<select name="vis">
							<option value="0" '.(isset($context['gadget']['vis']) && $context['gadget']['vis'] == 0 ? 'selected="selected"' : '').'>'.$txt['2sichat_vis0'].'</option>
							<option value="1" '.(isset($context['gadget']['vis']) && $context['gadget']['vis'] == 1 ? 'selected="selected"' : '').'>'.$txt['2sichat_vis1'].'</option>
							<option value="2" '.(isset($context['gadget']['vis']) && $context['gadget']['vis'] == 2 ? 'selected="selected"' : '').'>'.$txt['2sichat_vis2'].'</option>
							<option value="3" '.(isset($context['gadget']['vis']) && $context['gadget']['vis'] == 3 ? 'selected="selected"' : '').'>'.$txt['2sichat_vis3'].'</option>
						</select>
					</td>
				</tr>
				<tr>
                         <td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_ord'].'</strong>
                         </td>
					<td align="left" class="windowbg2">
						<input type="text" name="ord" size="64" maxlength="255" value="'.(isset($context['gadget']['ord']) ? $context['gadget']['ord']:'').'" /><br />
					</td>
				</tr>
				<tr>
                         <td align="left" class="windowbg2" colspan="2">
          				<input type="submit" name="submit" value="submit" />
					</td>
				</tr>
			</table>
			'.(isset($context['gadget']['id']) ? '<input type="hidden" name="mod" value="'.$context['gadget']['id'].'" />':'').'
		</form>
	';
}
?>