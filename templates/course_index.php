<table border="0" width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td valign="top">
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
				<td></td>
				<td><input type="checkbox" name="vdetails" value="yes" <? if($select_vdetails == 'yes'):?>checked="checked"<? endif;?>> mit Veranstaltungsdetails (Beschreibung Grunddaten)</td>
			</tr>
			<tr class="cycle_odd">
				<td ></td>
				<td><input type="checkbox" name="hiddencourses" value="yes"<? if($select_hiddencourses == 'yes'):?>checked="checked"<? endif;?>> mit versteckten Veranstaltungen</td>
			</tr>
			<tr class="cycle_even">
				<td></td>
				<td><?=makeButton('auswaehlen', 'input', 'Semester auswählen', 'Bitte wählen Sie ein Semester aus')?></td>
			</tr>
		</table>
		</form>
		<br/>

		<? if(!empty($current_semester) && !empty($verzeichnisse)): ?>
		<table cellpadding="2" cellspacing="0" width="100%">
			<tr>
				<td class="topic" colspan="2"><b>Veranstaltungsberichte für <?=$current_name?></b></td>
			</tr>
			<tr>
				<th align="left">Fach</th>
				<th width="15%">Berichte</th>
			</tr>
		</table>
		<? foreach ($verzeichnisse as $lid=>$lehreinheit): ?>
		<? if(count($lehreinheit['faecher']) > 0):?>
		<? if($anzahl > 0) : ?>
		<table cellpadding="2" cellspacing="0" width="100%">
			<tr>
				<td class="steel" colspan="2"><b><?=$lehreinheit['name']?></b></td>
			</tr>
			<? $i=0; foreach ($lehreinheit['faecher'] as $key=>$fach): ?>
			<tr class="<?=($i%2==0)?'steel1':'steelgraulight' ?>">
				<td><?=$fach?></td>
				<td align="center" width="15%">
				<a href="<?=$link2?>&semester=<?=$current_semester?>&lid=<?=$lid?>&id=<?=$key?>&action=studiumgeneralertf&va_typ=<?=$va_typ?>">
				    <?= Assets::img('icons/16/blue/file-generic.png', array('title' => 'Veranstaltungsverzeichnis generierenn','class'=>'text-top')) ?>
                </a>
                <a href="<?=$link3?>&semester=<?=$current_semester?>&lid=<?=$lid?>&id=<?=$key?>&action=studiumgeneralexml&va_typ=<?=$va_typ?>">
				    <?= Assets::img('icons/16/blue/file-xls.png', array('title' => 'Veranstaltungsverzeichnis generieren(XML)','class'=>'text-top')) ?>
                </a>
				</td>
			</tr>
			<? $i++; endforeach; ?>
		</table>
		<br/>
		<? else :?>
		<?=MessageBox::info("Leider sind keine Datensätze vorhanden.")?>
		<? endif?>
		<? endif; ?>
		<? endforeach; ?>
		<? endif; ?>
		</td>
		<td width="270" align="right" valign="top">
		<!-- Hinweisbox -->
		<? print_infobox(array(
                array(
                    'kategorie'=>_('Hinweis').':',
                    'eintrag'=>array(
                        array(
                            'icon'=>'icons/16/blue/file-generic.png',
                            'text'=>_('Das Namensverzeichnis wird als <strong>RTF-Datei</strong> erstellt und zum Download angeboten.')
                        ),
						array(
							'icon' => 'icons/16/blue/file-xls.png',
							'text' => _('Das Namensverzeichnis wird als kompakte <strong>XML-Datei</strong> erstellt und zum Download angeboten.')),
						array(
							'icon' => 'icons/16/black/info.png',
							'text' => _('Die Erzeugung kann unter Umständen längern dauern. Haben Sie bitte Geduld.')),
                        array(
                            'icon' => 'icons/16/black/exclaim-circle.png',
                            'text' => _('Nach Inkonsitenten Daten suchen<br><a href="'.URLHelper::getURL($link4)).'">Suchen</a>')
                    )
                )
            ), 'infobox/archiv.jpg'); ?>
		<!-- Hinweisbox Ende -->
		</td>
	</tr>
</table>