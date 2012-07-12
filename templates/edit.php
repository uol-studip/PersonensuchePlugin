<script src="<?=$path?>/js/move.js" type="text/javascript"></script>

<? if($_REQUEST['EditUserData'] == 'true'):?>
<? echo Messagebox::success('Die Änderungen wurden erfolgreich gespeichert.');?>
<? endif;?>

<table width="100%" cellpadding="2" cellspacing="0">
    <tr>
        <td class="topic" colspan="2"><b>Universitäre Daten von <?=$person['Vorname']?> <?=$person['Nachname']?></b></td>
    </tr>
    <tr class="steel1">
        <td>Email: </td>
        <td><?=$person['Email']?></td>
    </tr>
</table>
<table width="100%" cellpadding="2" cellspacing="0">
    <tr>
        <th colspan="2" align="left">Zugehörige Einrichtungen</th>
    </tr>
    <tr class="steel1">
        <td>
<? if (isset($person_institude)) {
		foreach ($person_institude as $key => $person_inst) {
		if($person_inst_data['Institut_id']==$person_inst['Institut_id']){
		    $person_inst_data['inst_name'] = $person_inst['name'];
			$link_style="red";
		} else {
		    $link_style="";
		}
		?>
		<form name="form_delete_<?=$key?>" method="post">
			<div class="steel1" style="margin-right: 20px;">
				<a style="color:<?=$link_style?>" href="<?= URLHelper::getLink($plupath, array('institut_id' => $person_inst['Institut_id'])) ?>"><?=$person_inst['name']?></a>
				<a onClick="return rlysubmit(document.form_delete_<?=$key?>);">
				    <?= Assets::img('icons/16/blue/trash.png', array('title' => 'Löscht die Einrichtung','class'=>'text-top')) ?>
				</a>
			</div>
			<input type="hidden" value="<?=$person_inst['Institut_id']?>" name="delete">
			<input type="hidden" value="<?=$_GET["bearbeiten"]?>" name="user_id">
		</form>
<? 		}
	} else { ?>
		Dieser Benutzer ist keinen Einrichtungen zugewiesen
	<? } ?>
        </td>
        <td>
    	<form name="form_insert" method="post">
            <a onClick="document.form_insert.submit()"><?= Assets::img('icons/16/blue/arr_2left.png', array('title' => 'Einrichtung hinzufügen','class'=>'text-top')) ?></a>
            <select name="faksinst">
    			<?=$faksinst?>
            </select>
            <input type="hidden" value="<?=$_GET["bearbeiten"]?>" name="user_id">
            <input type="hidden" value="true" name="InsertUserInst">
    	</form>
        </td>
    </tr>
</table>
<br/>

<? if(isset($person_inst_data['inst_name'])):?>
<div class="topic"><b>Einstellungen für <em><?=$person_inst_data['inst_name']?></em></b></div>
<? endif; ?>
<form name="form" method="post" onSubmit="return send()">
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="bearbeiten" value="<?=$user_id?>" />
<input type="hidden" name="EditUserData" value="true" />

<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<!--<tr class="steel1">
		<td width="150">Name:</td>
		<td><b><?=$person['Nachname']?>, <?=$person['Vorname']?></b></td>
	</tr>
	<tr class="steelgraulight">
		<td>Email:</td>
		<td><b><?=$person['Email']?></b></td>
	</tr>-->
	<tr class="steel1">
		<td>Titel:</td>
		<td><select name="title_front" style="width: 150px;">
		<? foreach ($title_front as $title):?>
		<option <?=($title==$person['title_front'])?'selected="selected"':''?>><?=$title?></option>
		<? endforeach; ?>
		</select></td>
	</tr>
	<tr class="steelgraulight">
		<td>Titel nachgestellt:</td>
		<td><select name="title_rear" style="width: 150px;">
		<? foreach ($title_rear as $title):?>
		<option <?=($title==$person['title_rear'])?'selected="selected"':''?>><?=$title?></option>
		<? endforeach; ?>
		</select></td>
	</tr>
	<tr class="steel1">
		<td>Raum:</td>
		<? if (!empty($person_inst_data)) { ?>
			<td><input type="text" name="raum" value="<?=$person_inst_data['raum']?>" style="width: 400px;" /></td>
		<? } else { ?>
			<td>Bitte wählen Sie ein Institut aus um den Raum zu sehen.</td>
		<? } ?>
	</tr>
	<tr class="steelgraulight">
		<td>Sprechzeit:</td>
		<? if (!empty($person_inst_data)) { ?>
			<td><input type="text" name="sprechzeiten" value="<?=$person_inst_data['sprechzeiten']?>" style="width: 400px;"  /></td>
		<? } else { ?>
			<td>Bitte wählen Sie ein Institut aus um die Sprechzeit zu sehen.</td>
		<? } ?>
	</tr>
	<tr class="steel1">
		<td>Telefon:</td>
		<? if (!empty($person_inst_data)) { ?>
		<td><input type="text" name="telefon" value="<?=$person_inst_data['Telefon']?>" style="width: 400px;"  /></td>
		<? } else { ?>
			<td>Bitte wählen Sie ein Institut aus um die Telefonnummer zu sehen.</td>
		<? } ?>
	</tr>
	<tr class="steelgraulight">
		<td>Fax:</td>
		<? if (!empty($person_inst_data)) { ?>
		<td><input type="text" name="fax" value="<?=$person_inst_data['Fax']?>" style="width: 400px;"  /></td>
		<? } else { ?>
			<td>Bitte wählen Sie ein Institut aus um die Faxnummer zu sehen.</td>
		<? } ?>
	</tr>
	<tr class="steel1">
		<td>Homepage:</td>
		<td><input type="text" name="home" value="<?=$person['Home']?>" style="width: 400px;"  /></td>
	</tr>
	<tr class="steelgraulight">
		<td>Schwerpunkt:</td>
		<td><input type="text" name="schwerp" value="<?=$person['schwerp']?>" style="width: 400px;"  /></td>
	</tr>
	<tr class="steel1">
		<td>
		</td>
		<td>
			Standard-Adresse:<input type="checkbox" value="true" name="standard" <?=$checked2?>>
		</td>
	</tr>
	<tr class="steel1">
		<td>
		</td>
		<td>
			<table>
				<tr>
					<td>Alle Gruppen:</td>
					<td></td>
					<td>Mitglied in:</td>
				</tr>
				<tr>
					<td>
						<select multiple name="list1" size="8" width="150" style="width:180px;">
							<?=$gruppen?>
						</select>
					</td>
					<td align="center">
						<font size="1">Einzeln w&auml;hlen</font><br>
						<input type="button" name="toLeft" value=" &lt; " onclick="turn(this.form.list2,this.form.list1)">
						<input type="button" name="toRight" value=" &gt; " onclick="turn(this.form.list1,this.form.list2)">
						<p>&nbsp;</p>
						<font size="1">Alles ausw&auml;hlen</font><br>
						<input type="button" value="&lt;&lt;" onclick="allToOther(this.form.list2,this.form.list1)">
						<input type="button" value="&gt;&gt;" onclick="allToOther(this.form.list1,this.form.list2)">
					</td>
					<td>
						<select multiple name="list2" size="8" width="150" style="width:180px;">
							<?=$user_gruppen?>
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
		</td>
		<td align=right>
			<input type="hidden" name="user_id" value="<?=$_GET["bearbeiten"]?>">
			<input type="hidden" name="faksinst" value="<?=$person_inst_data['Institut_id']?>">
			<input type="hidden" name="Items" value="">
			<input type="hidden" name="all_groups" value="<?=$save_groups?>">
		</td>
	</tr>
	<tr class="steel2">
		<td></td>
		<td><?=makebutton('uebernehmen', 'input', 'Änderungen übernehmen'); ?></td>
	</tr>
</table>
</form>
<br/>