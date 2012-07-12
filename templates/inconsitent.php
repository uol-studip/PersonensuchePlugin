
<form action="<?=$link1?>" method="post">
    <input type="hidden" name="action" value="selectsemester" />
	<table cellpadding="2" cellspacing="0" width="100%">
		<tr>
			<td class="topic" colspan="2"><b>Auswahl einschränken</b></td>
		</tr>
		<tr class="cycle_even">
			<td width="300"><b>Semester:</b></td>
			<td>
			<select name="semester">
			<? foreach ($semester as $semester): ?>
				<option value="<?=$semester['semester_id'] ?>"<? if ($semester['semester_id'] == $current_semester): $current_name=$semester['name']; ?> selected="selected"<? endif; ?>><?=$semester['name'] ?></option>
			<? endforeach; ?>
			</select>
			</td>
		</tr>
		<tr class="cycle_odd">
			<td><b>Veranstaltungstyp filtern:</b></td>
			<td>
			    
			    <select name="va_typ">
			        <option value="0" <?=($va_typ == 0)?'selected="selected"':''?>>Alle</option>
			        <? foreach($GLOBALS['SEM_TYPE'] AS $id => $type) :?>
			            <? if($type['class'] == 1) :?>
			            <option <?=($va_typ== $id)?'selected="selected"':''?>value="<?=$id?>"><?=$type['name']?></option>
			            <? endif?>
			        <? endforeach?>
			    </select>
			</td>
		</tr>
		<tr class="cycle_even">
			<td ></td>
			<td><input type="checkbox" name="hiddencourses" value="1"<? if($select_hiddencourses == '1'):?>checked="checked"<? endif;?>> mit versteckten Veranstaltungen</td>
		</tr>
		<tr class="cycle_odd">
			<td></td>
			<td><?=makeButton('auswaehlen', 'input', 'Semester auswählen', 'Bitte wählen Sie ein Semester aus')?></td>
		</tr>
	</table>
	
	<? if($message1) :?>
	    <?=$message1?>
	<? endif?>
	
	<? if($message2) :?>
	    <?=$message2?>
	<? endif?>

	<? if(isset($results)) :?>
	<br />
	
	    <? if(count($results) > 0) :?>
	        <p style="text-align: right; width:100%">
	            <?=makeButton('speichern', 'input', 'Inkonsistente Daten auflösen')?>
	        </p>
	        <table border="0" cellpadding="2" cellspacing="1" width="100%" class="default collapsable">
	            <tr>
	                <td style="width: 40%" class="topic"><strong>Veranstaltungsname</strong></td>
	                <td class="topic"><strong>Dozent</strong></td>
	                <td class="topic"><strong>Modul</strong> <input type="checkbox" class="allSem" /></td>
	                <td class="topic"><strong>Grunddaten</strong> <input type="checkbox" class="allData" /></td>
	            </tr>
	        <? foreach($results AS $institut => $seminare) :?>
	            <tbody class="collapsed">
	                <tr class="steel header-row">
	                    <td colspan="4" class="toggle-indicator" style="border-top: 1px solid #ccc;">
	                        <a class="toggler"><strong><?=$institut?></strong></a>
	                    </td>
	                </tr>
	                <? foreach($seminare AS $seminar) :?>
	                <tr class="<?=TextHelper::cycle('cycle_even','cycle_odd')?>">
	                    <td><a href="<?= URLHelper::getURL('seminar_main.php', array('auswahl' => $seminar['Seminar_id'])) ?>"><?=$seminar['name']?></a></td>
	                    <td></td>
	                    <td><input class="sem" value="1" type="checkbox" name="semtree[<?= $seminar['Seminar_id']?>]" <?=($seminar['sem_tree'] == 1)? 'checked="checked" disabled': '' ?>> </td>
	                    <td><input class="data" value="1" type="checkbox" name="datafield[<?= $seminar['Seminar_id']?>]" <?=($seminar['datafield'] == 1)? 'checked="checked" disabled': '' ?>> </td>
	                </tr>
	                <? endforeach?>
	            </tbody>
	        <? endforeach?>
	        </table>
	        <p style="text-align: right; width:100%">
	            <?=makeButton('speichern', 'input', 'Inkonsistente Daten auflösen')?>
	        </p>
	    <? else :?>
	        <?= MessageBox::info("Ihre Suche ergab leider keine Treffer")?>
	    <? endif?>
	<? endif?>
</form>

<script type="text/javascript">
(function($) {
	$(function () { // this line makes sure this code runs on page load
		$('.allSem').click(function () {
			var classes = $(this).closest('table').find('tbody').hasClass("collapsed").toString();
			if(classes == "true") {
				$(this).closest('table').find('tbody').removeClass('collapsed');
			}
			$(this).parents('table:eq(0)').find(':checkbox:enabled.sem').attr('checked', this.checked);
			
		});
		$('.allData').click(function () {
			var classes = $(this).closest('table').find('tbody').hasClass("collapsed").toString();
			if(classes ==  "true") {
				$(this).closest('table').find('tbody').removeClass('collapsed');
			}
			$(this).parents('table:eq(0)').find(':checkbox:enabled.data').attr('checked', this.checked);
		});
	});
}(jQuery))
</script>