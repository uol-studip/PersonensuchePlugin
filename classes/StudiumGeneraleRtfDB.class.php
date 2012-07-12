<?php
/**
 * ModulVerwaltungDB.class.php
 *
 * lange Beschreibung...
 *
 * PHP version 5
 *
 * @author  	Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @package 	IBIT_ModulVerwaltungPlugin
 * @copyright 	2008 IBIT
 * @license 	http://www.gnu.org/licenses/gpl.html GPL Licence 3
 * @version 	0.8.2
 */


class PersoStudiumGeneraleRtfDB
{
	/**
	 * Enter description here...
	 *
	 * @param string $seminar_id
	 * @return string
	 */
	public static function getSeminarName($seminar_id)
	{
	    /*$db = new DB_Seminar;
	    $sql =  "SELECT Name FROM seminare WHERE Seminar_id = '".$seminar_id."'";
	    $db->query($sql);
	    $db->next_record();
	    return $db->f("Name");*/

	    $result = DBManager::Get()->query("SELECT Name FROM seminare WHERE Seminar_id = '".$seminar_id."'")->fetch(PDO::FETCH_ASSOC);
	    return $result['Name'];
	}

	/**
	 * Enter description here...
	 *
	 * @param string $seminar_id
	 * @return string
	 */
	public static function getVANummer($seminar_id)
	{
	    /*$db = new DB_Seminar;
	    $sql =  "SELECT VeranstaltungsNummer FROM seminare WHERE Seminar_id = '".$seminar_id."'";
	    $db->query($sql);
	    $db->next_record();
	    return $db->f("VeranstaltungsNummer");*/

	    $result = DBManager::Get()->query("SELECT VeranstaltungsNummer FROM seminare WHERE Seminar_id = '".$seminar_id."'")->fetch(PDO::FETCH_ASSOC);
	    return $result['VeranstaltungsNummer'];
	}

	/**
	 * Enter description here...
	 *
	 * @param string $seminar_id
	 * @return string
	 */
	public static function getInstitut($seminar_id)
	{
	    /*$db = new DB_Seminar;
	    $sql =  "SELECT i.Name as Name FROM seminare s LEFT JOIN Institute i ON s.Institut_id=i.Institut_id WHERE Seminar_id = '".$seminar_id."'";
	    $db->query($sql);
	    $db->next_record();
	    return $db->f("Name");*/

	    $result = DBManager::Get()->query("SELECT i.Name as Name FROM seminare s LEFT JOIN Institute i ON i.Institut_id=s.Institut_id WHERE Seminar_id = '".$seminar_id."'")->fetch(PDO::FETCH_ASSOC);
	    return $result['Name'];
	}

	public static function getVADozenten($seminar_id) {
	    $query = "SELECT a.Nachname, a.Vorname
	                FROM seminar_user AS su
	                LEFT JOIN auth_user_md5 AS a
	                ON su.user_id = a.user_id
	                WHERE su.Seminar_id= ? AND su.status=?";
	    
	    $sql = DBManager::get()->prepare($query);
	    $sql->execute(array($seminar_id, 'dozent'));
	    $dozenten = $sql->fetchAll(PDO::FETCH_ASSOC);
	    
	    return $dozenten;
	}
	
	
	public static function getInstitutId($seminar_id) {
	    $result = DBManager::Get()->query("SELECT i.Institut_id as Institut_id FROM seminare s LEFT JOIN Institute i ON i.Institut_id=s.Institut_id WHERE Seminar_id = '".$seminar_id."'")->fetch(PDO::FETCH_ASSOC);
	    return $result['Institut_id'];
	}
	/**
	 * Gibt alle Institut-IDs eines admins anhand seiner User-ID zurück
	 *
	 * @param string $userid
	 * @return array()
	 */
	public static function getAdminsInstitute($userid='')
	{
		$institute_ids = array();
		$db = new DB_Seminar;
		$db->queryf("SELECT Institut_id FROM `user_inst` WHERE `user_id` = '%s' AND `inst_perms` = 'admin'", $userid);
		while($db->next_record())
		{
			$institute_ids[] = $db->f('Institut_id');
		}
		return $institute_ids;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $userid
	 * @return array()
	 */
	public static function getLehreinheiten($userid='', $root = false, $include_prof = true)
	{
		$lehreinheiten = array();
		//Alle Lehreinheiten anzeigen
		$db = new DB_Seminar;
		$db->queryf("SELECT sem_tree_id, name FROM `sem_tree` WHERE `sem_tree_id`='18ca85409be550468d39fc2c0c8313c6'");
		while ($db->next_record())
		{
			$lehreinheiten[$db->f('sem_tree_id')] = $db->f('name');
		}

		#echo "<pre>";
		#print_r($lehreinheiten);
		#echo "</pre>";
		return $lehreinheiten;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $lehreinheit_id
	 * @return array()
	 */
	public static function getFaecher($lehreinheit_id='')
	{
		$faecher = array();
		$db = new DB_Seminar;
		$db->queryf("SELECT sem_tree_id, priority, name FROM `sem_tree` WHERE `sem_tree_id`='%s' ORDER BY `priority`", $lehreinheit_id);
		while ($db->next_record())
		{
			$faecher[$db->f('sem_tree_id')] = $db->f('name');
		}
		return $faecher;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $fach_id
	 * @return array()
	 */
	public static function getAbschluesse($fach_id)
	{echo $fach_id;
		$abschluesse = array();
		$db = new DB_Seminar;
		$db->queryf("SELECT sem_tree_id, priority, name FROM `sem_tree` WHERE `sem_tree_id`='%s' ORDER BY `priority`", $fach_id);
		while ($db->next_record())
		{
			$abschluesse[$db->f('sem_tree_id')] = array('name' => $db->f('name'));
		}
		return $abschluesse;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $fach_id
	 * @return array()
	 */
	public static function getAbschluesseAndModule($fach_id)
	{
		$abschluesse = array();
		$db = new DB_Seminar;
		$db->queryf("SELECT sem_tree_id, priority, name FROM `sem_tree` WHERE `type`='4' AND `parent_id`='%s' ORDER BY `priority`", $fach_id);
		while ($db->next_record())
		{
			$abschluesse[$db->f('sem_tree_id')] = array('name' => $db->f('name'), 'module' => self::getModule($db->f('sem_tree_id')));
		}
		return $abschluesse;
	}

	/**
	 * Holt alle abstrakten Module zu einem Fach.
	 *
	 * @param string $abschluss_id
	 * @return array()
	 */
	public static function getModule($abschluss_id)
	{
		$rows = DBManager::Get()->query("SELECT DISTINCT sem_tree_id, name FROM sem_tree WHERE type=5 AND parent_id='{$abschluss_id}' ORDER BY priority ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);

		$modules = array();
		foreach ($rows as $row)
		{
			$modules[ $row['sem_tree_id'] ] = $row['name'];
		}
		return $modules;
	}

	/**
	 * Gibt eine Liste aller konkreten Module zu einem Abschluss (in einem Semester) zurück
	 *
	 * @param string $abschluss_id
	 * @param optional string $semester
	 * @return array()
	 */
	public static function getKonModule($abschluss_id, $semester = '')
	{
		$module = array();
		$db = new DB_Seminar;
		$db->queryf("SELECT DISTINCT sem_tree_id FROM `sem_tree` WHERE `type`='5' AND `parent_id`='%s' ORDER BY `name`", $abschluss_id);
		while ($db->next_record())
		{
			#echo "semtree_id: ".$db->f('sem_tree_id')."<br>";
			$db2 = new DB_Seminar;
			if(!empty($semester))
			{
				$db2->queryf("SELECT m.modul_kon_id, mz.modul_abst_id, mz.modultitel, mz.modulschluessel, mz.pruef_version, mz.pruef_form, m.semester, m.mok, s.name as semestername FROM mod_zuordnung AS mz LEFT JOIN module m ON mz.modul_abst_id = m.modul_abst_id LEFT JOIN semester_data s ON m.semester=s.semester_id WHERE mz.sem_tree_id = '%s' AND m.semester ='%s' ORDER BY mz.modulschluessel", $db->f('sem_tree_id'), $semester);
			}
			else
			{
				$db2->queryf("SELECT m.modul_kon_id, mz.modul_abst_id, mz.modultitel, mz.modulschluessel, mz.pruef_version, mz.pruef_form, m.semester, m.mok, s.name as semestername FROM mod_zuordnung AS mz LEFT JOIN module m ON mz.modul_abst_id = m.modul_abst_id LEFT JOIN semester_data s ON m.semester=s.semester_id WHERE mz.sem_tree_id = '%s' ORDER BY mz.modulschluessel", $db->f('sem_tree_id'));
			}
			if($db2->next_record())
			{
				$modul = array(
					'modul_kon_id' => $db2->f('modul_kon_id'),
					'modul_abst_id' => $db2->f('modul_abst_id'),
					'modultitel' => $db2->f('modultitel'),
					'modulschluessel' => $db2->f('modulschluessel'),
					'pruef_version' => $db2->f('pruef_version'),
					'pruef_form' => $db2->f('pruef_form'),
					'mok' => $db2->f('mok'),
					'semester_id' => $db2->f('semester'),
					'semester_name' => str_replace(array('Sommersemester', 'Wintersemester'), array('SS', 'WS'), trim($db2->f('semestername')))
				);
				$module[] = $modul;
			}
		}
		return $module;
	}

	/**
	 * Gibt eine Liste aller konkreten Module zu einem Abschluss (in einem Semester) zurück
	 *
	 * @param string $abschluss_id
	 * @param optional string $semester
	 * @return array()
	 */
	public static function getKonModuleForFach($fach_id)
	{
		$module = array();
		$db = new DB_Seminar;
		$db->queryf("SELECT DISTINCT objekt_id FROM `struktur_zuordnung` WHERE `sem_tree_id`='%s'", $fach_id);
		while ($db->next_record())
		{
			$db2 = new DB_Seminar;
			$db2->queryf("SELECT DISTINCT m.modul_kon_id, mz.modul_abst_id, mz.modultitel, mz.modulschluessel, mz.pruef_version, mz.pruef_form, m.semester, m.mok, s.name as semestername FROM mod_zuordnung AS mz LEFT JOIN module m ON mz.modul_abst_id = m.modul_abst_id LEFT JOIN semester_data s ON m.semester=s.semester_id WHERE mz.fach_id = '%s' ORDER BY mz.modulschluessel", $db->f("objekt_id"));
			while($db2->next_record())
			{
				$modul = array(
					'modul_kon_id' => $db2->f('modul_kon_id'),
					'modul_abst_id' => $db2->f('modul_abst_id'),
					'modultitel' => $db2->f('modultitel'),
					'modulschluessel' => $db2->f('modulschluessel'),
					'pruef_version' => $db2->f('pruef_version'),
					'pruef_form' => $db2->f('pruef_form'),
					'mok' => $db2->f('mok'),
					'semester_id' => $db2->f('semester'),
					'semester_name' => str_replace(array('Sommersemester', 'Wintersemester'), array('SS', 'WS'), trim($db2->f('semestername')))
				);
				$module[] = $modul;
			}
		}
		array_unique($module);
		return $module;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $semester
	 * @param string $lehreinheit_id
	 * @param string $fach_id
	 * @param string $abschluss_id
	 * @return array()
	 */
	public static function getVeranstaltungen($semester, $lehreinheit_id, $fach_id, $abschluss_id)
	{
		$resultset = array();
		if (!empty($abschluss_id))
		{
			$db = new DB_Seminar;
			$db->queryf("SELECT DISTINCT type FROM sem_tree WHERE parent_id = '%s'", $abschluss_id);
			if ($db->next_record())
			{
				if($db->f('type') == '5')
				{
					$module = array();
					$db2 = new DB_Seminar;
					$db2->queryf("SELECT DISTINCT sem_tree_id FROM sem_tree WHERE parent_id = '%s' ORDER BY priority", $abschluss_id);
					while ($db2->next_record())
					{
						$modul = array(
							'id' => $db2->f('sem_tree_id'),
							'name' => self::getSemTreeIdName($db2->f('sem_tree_id')),
							'kommentar' => self::getModulKommentar($db2->f('sem_tree_id'), $semester),
							'veranstaltungen' => self::getVeranstaltungInfos($db2->f('sem_tree_id'), $semester)
						);
						if(!empty($modul['veranstaltungen']))
						{
							$module[] = $modul;
						}
					}
					$abschluss = array(
						'id' => $abschluss_id,
						'name' => self::getSemTreeIdName($abschluss_id),
						'module' => $module
					);
					$fach = array(
						'id' => $fach_id,
						'name' => self::getSemTreeIdName($fach_id),
						'abschluesse' => array($abschluss)
					);
					$lehreinheit = array(
						'id' => $lehreinheit_id,
						'name' => self::getSemTreeIdName($lehreinheit_id),
						'faecher' => array($fach)
					);
					$resultset[] = $lehreinheit;
				}
			}
			//Modul ausgewählt
			else
			{
				$modul = array(
					'id' => $abschluss_id,
					'name' => self::getSemTreeIdName($abschluss_id),
					'kommentar' => self::getModulKommentar($abschluss_id, $semester),
					'veranstaltungen' => self::getVeranstaltungInfos($abschluss_id, $semester)
				);
				//todo...
				$abschluss = array(
					'id' => $abschluss_id,
					'name' => self::getSemTreeIdName($abschluss_id),
					'module' => array($modul)
				);
				$fach = array(
					'id' => $fach_id,
					'name' => self::getSemTreeIdName($fach_id),
					'abschluesse' => array($abschluss)
				);
				$lehreinheit = array(
					'id' => $lehreinheit_id,
					'name' => self::getSemTreeIdName($lehreinheit_id),
					'faecher' => array($fach)
				);
				$resultset[] = $lehreinheit;

				#echo "modul ausgewählt<br/>";
				#echo "<pre>";
				#print_r($resultset);
				#echo "</pre>";
			}
		}
		elseif (!empty($fach_id))
		{
			$abschluesse = array();
			$db2 = new DB_Seminar;
			$db2->queryf("SELECT sem_tree_id FROM sem_tree WHERE parent_id = '%s'", $fach_id);
			while ($db2->next_record())
			{
				$module = array();
				$db3 = new DB_Seminar;
				$db3->queryf("SELECT sem_tree_id FROM sem_tree WHERE parent_id = '%s'", $db2->f('sem_tree_id'));
				while ($db3->next_record())
				{
					$modul = array(
						'id' => $db3->f('sem_tree_id'),
						'name' => self::getSemTreeIdName($db3->f('sem_tree_id')),
						'veranstaltungen' => self::getVeranstaltungInfos($db3->f('sem_tree_id'), $semester)
					);
					if(!empty($modul['veranstaltungen']))
					{
						$module[] = $modul;
					}
				}
				$abschluss = array(
					'id' => $db2->f('sem_tree_id'),
					'name' => self::getSemTreeIdName($db2->f('sem_tree_id')),
					'module' => $module
				);
				if(!empty($abschluss['module']))
				{
					$abschluesse[] = $abschluss;
				}
			}
			$fach = array(
				'id' => $fach_id,
				'name' => self::getSemTreeIdName($fach_id),
				'abschluesse' => $abschluesse
			);
			$lehreinheit = array(
				'id' => $lehreinheit_id,
				'name' => self::getSemTreeIdName($lehreinheit_id),
				'faecher' => array($fach)
			);
			$resultset[] = $lehreinheit;
		}
		elseif (!empty($lehreinheit_id))
		{
			$faecher = array();
			$db4 = new DB_Seminar;
			$db4->queryf("SELECT sem_tree_id FROM sem_tree WHERE parent_id = '%s'", $lehreinheit_id);
			while ($db4->next_record())
			{
				$abschluesse = array();
				$db2 = new DB_Seminar;
				$db2->queryf("SELECT sem_tree_id FROM sem_tree WHERE parent_id = '%s'", $db4->f('sem_tree_id'));
				while ($db2->next_record())
				{
					$module = array();
					$db3 = new DB_Seminar;
					$db3->queryf("SELECT sem_tree_id FROM sem_tree WHERE parent_id = '%s'", $db2->f('sem_tree_id'));
					while ($db3->next_record())
					{
						$modul = array(
							'id' => $db3->f('sem_tree_id'),
							'name' => self::getSemTreeIdName($db3->f('sem_tree_id')),
							'veranstaltungen' => self::getVeranstaltungInfos($db3->f('sem_tree_id'), $semester)
						);
						#wenn leer nicht anzeigen
						if(!empty($modul['veranstaltungen']))
						{
							$module[] = $modul;
						}

					}
					$abschluss = array(
						'id' => $db2->f('sem_tree_id'),
						'name' => self::getSemTreeIdName($db2->f('sem_tree_id')),
						'module' => $module
					);
					if(!empty($abschluss['module']))
					{
						$abschluesse[] = $abschluss;
					}
				}
				$fach = array(
					'id' => $db4->f('sem_tree_id'),
					'name' => self::getSemTreeIdName($db4->f('sem_tree_id')),
					'abschluesse' => $abschluesse
				);
				if(!empty($fach['abschluesse']))
				{
					$faecher[] = $fach;
				}
			}
			$lehreinheit = array(
				'id' => $lehreinheit_id,
				'name' => self::getSemTreeIdName($lehreinheit_id),
				'faecher' => $faecher
			);

			$resultset[] = $lehreinheit;
		}
		return $resultset;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $semester
	 * @param unknown_type $lehreinheit_id
	 * @param unknown_type $fach_id
	 * @param unknown_type $abschluss_id
	 * @param unknown_type $userid
	 * @param unknown_type $root
	 * @return array() list of courses
	 */
	public static function getVeranstaltungenForBericht($semester, $lehreinheit_id, $fach_id, $abschluss_id, $userid, $root=false)
	{
		$instituts = self::getAdminsInstitute($userid);
		$resultset = array();
		$db = new DB_Seminar;
		$db->queryf("SELECT DISTINCT type FROM sem_tree WHERE sem_tree_id = '%s'", $abschluss_id);
		if ($db->next_record())
		{
			if($db->f('type') == '5')
			{
				$module = array();
				$db2 = new DB_Seminar;
				$db2->queryf("SELECT DISTINCT sem_tree_id FROM sem_tree WHERE parent_id = '%s' ORDER BY priority", $abschluss_id);
				while ($db2->next_record())
				{
					$modul = array(
						'id' => $db2->f('sem_tree_id'),
						'name' => self::getSemTreeIdName($db2->f('sem_tree_id')),
						'fachsemester' => self::getModulFachsemester($db2->f('sem_tree_id'), $semester),
						'kommentar' => self::getModulKommentar($db2->f('sem_tree_id'), $semester),
						'veranstaltungen' => self::getVeranstaltungInfos($db2->f('sem_tree_id'), $semester, $instituts, $root)
					);
					if(!empty($modul['veranstaltungen']))
					{
						$module[] = $modul;
					}
				}
				$fsemester =array();
				foreach($module as $modul)
				{
					if(empty($modul['fachsemester']))
					{
						$fsemester['x'][] = $modul;
					}
					else
					{
						foreach($modul['fachsemester'] as $fsid)
						{
							$fsemester[$fsid['fachsem_id']][] = $modul;
						}
					}
				}
				#sort($fsemester);

				$abschluss = array(
					'id' => $abschluss_id,
					'name' => self::getSemTreeIdName($abschluss_id),
					'semester' => $fsemester
				);
				$fach = array(
					'id' => $fach_id,
					'name' => self::getSemTreeIdName($fach_id),
					'abschluesse' => array($abschluss)
				);
				$lehreinheit = array(
					'id' => $lehreinheit_id,
					'name' => self::getSemTreeIdName($lehreinheit_id),
					'faecher' => array($fach)
				);
				$resultset[] = $lehreinheit;
			}
		}
		return $resultset;
	}
    
	
	/**
	 * Enter description here...
	 *
	 * @param string $sem_tree_id
	 * @return unknown
	 */
	public static function getModulKommentar($sem_tree_id, $semester)
	{
		return DBManager::get()->query("SELECT md.kommentar
			FROM modul_deskriptor AS md
			LEFT JOIN module AS m ON md.desk_id=m.desk_id
			LEFT JOIN mod_zuordnung AS mz ON m.modul_abst_id= mz.modul_abst_id
			WHERE mz.sem_tree_id='{$sem_tree_id}' AND m.semester='{$semester}'")->fetchColumn();
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $sem_tree_id
	 * @param unknown_type $semester
	 * @return unknown
	 */
	public static function getModulFachsemester($sem_tree_id, $semester)
	{
		return DBManager::get()->query("SELECT mf.fachsem_id
			FROM mod_fachsem AS mf
			LEFT JOIN module AS m ON mf.modul_kon_id=m.modul_kon_id
			LEFT JOIN mod_zuordnung AS mz ON m.modul_abst_id= mz.modul_abst_id
			WHERE mz.sem_tree_id='{$sem_tree_id}' AND m.semester='{$semester}'")->fetchAll(PDO::FETCH_ASSOC);
	}

	
	public static function getCountVeranstaltungInfos($sem_tree_id, $semester_id, $type) {
	    // Query wird zusammengebaut
	    
	    $query = "SELECT s.Seminar_id, s.VeranstaltungsNummer, s.Name, s.status, s.art, s.Beschreibung, s.admission_turnout, s.visible, s.Institut_id as inst, si.institut_id
		          FROM seminar_sem_tree AS sst
		          LEFT JOIN seminare AS s ON sst.seminar_id = s.Seminar_id
		          LEFT JOIN semester_data AS sd ON s.start_time = sd.beginn
		          LEFT JOIN seminar_inst si ON sst.seminar_id = si.seminar_id
		          WHERE sst.sem_tree_id = ?
		          AND sd.semester_id= ?";
	    $condition = array($sem_tree_id, $semester_id);
		if($type != 0) {
		    $query .= "AND s.status = ?";
		    array_push($condition, $type);
		}
		
		$query .= "ORDER BY s.VeranstaltungsNummer ASC";
		
		
		$sql = DBManager::get()->prepare($query);
		$sql->execute($condition);
		$seminars = $sql->rowCount();
		
		return $seminars;
	}
	public function saveInconsitentDataField($seminar_id) {
	    $field_id = '6b9b277c60698855a9cfde20cc0c05d8'; // Sem_tree_id Studium generale / Gasthörstudium
	    
	    $query = "INSERT INTO datafields_entries (content, datafield_id, range_id, mkdate, chdate)
                     VALUES (?,?,?,UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
                     ON DUPLICATE KEY UPDATE content=?, chdate=UNIX_TIMESTAMP()";

	    $db = DBManager::get()->prepare($query);
	    
	    return $db->execute(array('1', $field_id, $seminar_id, '1'));
	}
	
	
	public function saveInconsitentSemTree($seminar_id) {
	    $sem_tree_id = '18ca85409be550468d39fc2c0c8313c6'; // Sem_tree_id Studium generale / Gasthörstudium
	    
	    $query = "INSERT INTO seminar_sem_tree (seminar_id, sem_tree_id) VALUES (?,?)";
	    $db = DBManager::get()->prepare($query);
	    
	    return $db->execute(array($seminar_id, $sem_tree_id));
	}
	
	
	public static function getInconsitentData($semester, $hiddencourses, $type = 0) {
	    $result = array();
	    if($type != 0) {
	        $typ = $type;
	    }
	    else {
	        $typ = 0;
	    }
	    // Datefield ID StudiumGenerale
	    $field_id = '6b9b277c60698855a9cfde20cc0c05d8';
	    // Seminare aus dem Seminar_Sem_Tree auslesen
	    
	    $query = "SELECT s.Seminar_id
	                FROM seminar_sem_tree AS sst
	                LEFT JOIN sem_tree AS st ON st.sem_tree_id = sst.sem_tree_id
	                LEFT JOIN seminare AS s ON sst.seminar_id = s.Seminar_id
	                LEFT JOIN semester_data sd ON s.start_time = sd.beginn
	                LEFT JOIN seminar_inst si ON sst.seminar_id = si.seminar_id
	                Left JOIN Institute AS i ON si.Institut_id = i.Institut_id
	                Where st.name = ? and sd.semester_id= ?";
	    $condition = array('Studium generale / Gasthörstudium', $semester);
	    
	    if($typ != 0) {
	        $query .= " AND s.status = ?";
	        array_push($condition, $typ);
	    }
	    
	    if($hiddencourses != 1) {
	        $query .= " AND s.visible = ?";
	        array_push($condition, 1);
	       
	    }
	  
		
	    $db = DBManager::get()->prepare($query);
	    $db->execute($condition);
	
	    $seminar_ids = $db->fetchAll(PDO::FETCH_COLUMN);
	    
	    
	    // Generische Datenfelder auslesen
	    $query = "SELECT df.range_id FROM datafields_entries df 
	                LEFT JOIN seminare AS s on s.Seminar_id = df.range_id
	                LEFT JOIN semester_data AS sd ON s.start_time = sd.beginn
	                WHERE datafield_id = ? AND sd.semester_id= ? AND df.content = ?";
	    $condition = array($field_id, $semester, 1);
	    
	    if($typ != 0) {
	        $query .= " AND s.status = ?";
	        array_push($condition, $typ);
	    }
	    
	    if($hiddencourses != 1) {
	        $query .= " AND s.visible = ?";
	        array_push($condition, 1);
	       
	    }
	    $db = DBManager::get()->prepare($query);
	    $db->execute($condition);
	    
	    $datafields = $db->fetchAll(PDO::FETCH_COLUMN);
	    $arr = array_flip($seminar_ids);
	    
	    // Überprüfung ob Datafields == Studienmodule
	    foreach($datafields AS $datafield) {
	        if(!isset($arr[$datafield])) {
	            $results['inDatafield'][] = $datafield;
	            
	        }
	    }
	    
	    // Umgekehrte Prüfung
	    $arr2 = array_flip($datafields);
	    foreach($seminar_ids AS $seminar) {
	        if(!isset($arr2[$seminar])) {
	            $results['inSemTree'][] = $seminar;
	            
	        }
	    }
	    
	    
	    // Zusammenführung der beiden Array in ein gesamtes Konstrukt
	    if(!empty($results['inDatafield'])) {
	        foreach($results['inDatafield'] AS $df) {
	            $inst_name = PersoStudiumGeneraleRtfDB::getInstitut($df);
	            $sem['Seminar_id'] = $df;
	            $sem['name'] = PersoStudiumGeneraleRtfDB::getSeminarName($df);
	            $sem['va_nr'] = PersoStudiumGeneraleRtfDB::getVANummer($df);
	            $sem['dozenten'] = PersoStudiumGeneraleRtfDB::getVADozenten($df);
	            $sem['datafield'] = '1';
	            $sem['sem_tree'] = '0';
	        
	            $array[$inst_name][] = $sem;
	        }
	    }
	    
	    if(!empty($results['inSemTree'])) {
	        foreach($results['inSemTree'] AS $sst) {
	            $inst_name = PersoStudiumGeneraleRtfDB::getInstitut($sst);
	            $sem['Seminar_id'] = $sst;
	            $sem['name'] = PersoStudiumGeneraleRtfDB::getSeminarName($sst);
	            $sem['va_nr'] = PersoStudiumGeneraleRtfDB::getVANummer($sst);
	            $sem['dozenten'] = PersoStudiumGeneraleRtfDB::getVADozenten($sst);
	            $sem['datafield'] = '0';
	            $sem['sem_tree'] = '1';
	        
	            $array[$inst_name][] = $sem;
	        }
	    }
	    
	    
	    
	    return $array;
	}
	

	/**
	 * //TODO: status als typ ausgeben...
	 *
	 * @param string $sem_tree_id
	 * @param string $semester_id
	 * @return array()
	 */
	public static function getVeranstaltungInfos($sem_tree_id, $semester_id, $type = false, $instituts=array(), $root=false)
	{

		// Query wird zusammengebaut
	    $query = "SELECT DISTINCT s.Seminar_id, s.VeranstaltungsNummer, s.Name, s.status, s.art, s.Beschreibung, s.admission_turnout, s.visible, s.Institut_id as inst, si.institut_id
		          FROM seminar_sem_tree AS sst
		          LEFT JOIN seminare AS s ON sst.seminar_id = s.Seminar_id
		          LEFT JOIN semester_data AS sd ON s.start_time = sd.beginn
		          LEFT JOIN seminar_inst AS si ON sst.seminar_id = si.seminar_id
		          WHERE sst.sem_tree_id = ?
		          AND sd.semester_id= ?";
	    $condition = array($sem_tree_id, $semester_id);
		if($type != false) {
		    $query .= "AND s.status = ?";
		    array_push($condition, $type);
		}
		
		$query .= "GROUP BY s.Seminar_id ORDER BY s.VeranstaltungsNummer ASC";
		
		
		$sql = DBManager::get()->prepare($query);
		$sql->execute($condition);
		$seminars = $sql->fetchAll(PDO::FETCH_ASSOC);

		foreach($seminars AS $seminar) {
		    $authors =array();
		    
		    $query = "SELECT a.Nachname, a.Vorname
			            FROM seminar_user AS su
			            LEFT JOIN auth_user_md5 AS a
			            ON su.user_id = a.user_id
			            WHERE su.Seminar_id= ? AND su.status=?";
		    
		    $sql = DBManager::get()->prepare($query);
		    $sql->execute(array($seminar['Seminar_id'], 'dozent'));
		    $dozenten = $sql->fetchAll(PDO::FETCH_ASSOC);
		    
		    foreach($dozenten AS $dozent){
		        $author = array(
		                'name' => $dozent['Nachname'],
		                'vorname' => $dozent['Vorname']
		        );
		        $authors[] = $author;
		    }
		    
		    $course = array(
		            'seminar_id' => $seminar['Seminar_id'],
		            'veranstaltungsnummer' => $seminar['VeranstaltungsNummer'],
		            'institut' => $seminar['inst'],
		            'name' => $seminar['Name'],
		            'status' => $seminar['status'],
		            'art' => $seminar['art'],
		            'beschreibung' => $seminar['Beschreibung'],
		            'max_user' => $seminar['admission_turnout'],
		            'visible' => $seminar['visible'],
		            'typ' => $GLOBALS['SEM_TYPE'][$seminar['status']]['name'],
		            'authors' => $authors
		    );
		    $courses[]=$course;
			
		}
		
        
		return $courses;
	}

	public static function sortVeranstaltungen($courses, $showhidden)
	{
		$faks = DBManager::get()->query("SELECT * FROM Institute WHERE Institut_id = fakultaets_id AND type = '7' ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);

		$new_faks = array();
		foreach ($faks as $key=>$fak)
		{
			$instituts = DBManager::get()->query("SELECT * FROM Institute WHERE fakultaets_id = '{$fak['Institut_id']}' ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);
			$new_instituts = array();
			foreach ($instituts as $index => $institut)
			{
				$new_courses = array();
				foreach ($courses as $course)
				{
					if($course['institut'] == $institut['Institut_id'] && ($course['visible'] == 1 || $showhidden == 'yes'))
					{
						$new_courses[] = $course;
					}
				}
				if(count($new_courses)>0)
				{
					$new_instituts[$index] = $institut;
					$new_instituts[$index]['courses'] = $new_courses;
				}
			}
			if(count($new_instituts)>0)
			{
				$new_faks[$key] = $fak;
				$new_faks[$key]['institutes'] = $new_instituts;
			}
		}
		return $new_faks;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $sem_tree_id
	 * @param unknown_type $semester_id
	 */

    
    public static function getVeranstaltungsTime($seminar_id) {
        $seminar = new Seminar($seminar_id);
        $terms = $seminar->getUndecoratedData();
        
        if (is_array($terms['regular']['turnus_data'])) {
            $anz = 1;
            foreach ($terms['regular']['turnus_data'] as $cycle) {
                $termine[] = getWeekDay($cycle['day']).", ".$cycle['start_hour'].":".$cycle['start_minute']." - ".$cycle['end_hour'].":".$cycle['end_minute']."";
            }
        }
        
        $presence_types = getPresenceTypes();

        if (is_array($terms['irregular'])) {
            foreach ($terms['irregular'] as $date) {
                $termine[] = getWeekDay(date('w', $date['start_time'])).", ".date('H:i', $date['start_time'])." - ".date('H:i', $date['end_time']);
            }
        }
                
        if(!empty($termine)) {
            $termine = array_unique($termine);
            for($i = 0; $i < count($termine); $i++) {
                if(!empty($termine[$i])) {
                    $termin .= $termine[$i];

                    if($i+1 < count($termine)) {
                        $termin.= ", ";
                    }
                }
            }
        }
        
        return $termin;
    }
    
    
    public static function getVeranstaltungsPlace($seminar_id) {
        $seminar = new Seminar($seminar_id);
        $datas = $seminar->getUndecoratedData();

        if (is_array($datas['regular']['turnus_data'])) {
            $anz = 1;
            foreach ($datas['regular']['turnus_data'] as $cycle) {
                $plainRooms = '';
                if (is_array($cycle['assigned_rooms'])){
                    $plainRooms = implode(', ', getPlainRooms($cycle['assigned_rooms']));
                }
                
                if(!empty($plainRooms)) {
                    $rooms[] = htmlspecialchars($plainRooms);
                }
                
                if(empty($cycle['assigned_rooms'])) {
                    if(!empty($cycle['freetext_rooms'])) {
                        foreach($cycle['freetext_rooms'] AS $name => $anz) {
                            
                            $rooms[] = $name;
                        }
                    }
                }
            }
        }
                        
        $presence_types = getPresenceTypes();

        if (is_array($datas['irregular'])) {
            foreach ($datas['irregular'] as $date) {
                $plainRooms = implode(', ', getPlainRooms(array($date['resource_id'] => 1)));
                if(!empty($plainRooms)) {
                    $rooms[] = htmlspecialchars($plainRooms);
                }
                else {
                    $rooms[] = htmlspecialchars($date['raum']);
                }
            }
        }
        
        
        
        if(!empty($rooms)) {
            $rooms = array_unique($rooms);
            for($i = 0; $i < count($rooms); $i++) {
                $room .= $rooms[$i];
                if(($i+1) < count($rooms)) {
                    $room.= ", ";
                }
            }
        }
        
        
        if(empty($rooms))
        {
            $query = "SELECT Ort FROM seminare WHERE Seminar_id = ?";
            $db = DBManager::get()->prepare($query);
            $db->execute(array($seminar_id));
            $result2 = $db->fetch(PDO::FETCH_ASSOC);
            
            
            if(!empty($result2['Ort']))
            {
                $room = $result2['Ort'];
            }
        }
        
        if(empty($rooms))
        {
            $room = 'k.A.';
        }
        
       
        return $room;
    }
    
    
    
    /**
	 * Enter description here...
	 *
	 * @param string $id
	 * @return string
	 */
	public static function getSemTreeIdName($id)
	{
		$db = new DB_Seminar;
		$db->queryf("SELECT sem_tree_id, name FROM `sem_tree` WHERE sem_tree_id='%s'", $id);
		if($db->next_record())
		{
			return $db->f('name');
		}
		else
		{
			return '';
		}
	}

	/**
	 * Überprüft eine Institut anhand der ID, ob es sich um eine Fakultät handelt.
	 *
	 * @param string $institut_id
	 * @return boolean
	 */
	public static function isFaculty($institut_id)
	{
		$db = new DB_Seminar;
		$db->queryf("SELECT Institut_id FROM `Institute` WHERE `Institut_id` = '%s' AND `fakultaets_id` = '%s'", $institut_id, $institut_id);
		if($db->next_record())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Enter description here...
	 *
	 * @return array()
	 */
	public static function getAllVAdmins()
	{
		$vadmins = array();
		$db = new DB_Seminar;
		$db->queryf("SELECT u.user_id, u.username, u.Vorname, u.Nachname, i.Name FROM `auth_user_md5` as u LEFT JOIN `user_inst` AS iu ON u.user_id=iu.user_id LEFT JOIN `Institute` AS i ON iu.Institut_id=i.Institut_id WHERE iu.inst_perms='admin' ORDER BY u.Nachname, i.Name");
		while($db->next_record())
		{
			$user = array(
				'username' => $db->f('username'),
				'vorname' => $db->f('Vorname'),
				'nachname' => $db->f('Nachname'),
				'institut' => $db->f('Name')
			);
			$vadmins[] = $user;
		}
		return $vadmins;
	}

	/**
	 * Enter description here...
	 *
	 * @return array()
	 */
	public static function getAllMAdmins()
	{
		$madmins = array();
		$db = new DB_Seminar;
		$db->queryf("SELECT ru.userid, u.username, u.Vorname, u.Nachname, r.rolename, i.Name FROM `roles_user` AS ru LEFT JOIN `roles` AS r ON ru.roleid=r.roleid LEFT JOIN `auth_user_md5` as u ON ru.userid=u.user_id LEFT JOIN `user_inst` AS iu ON ru.userid=iu.user_id LEFT JOIN `Institute` AS i ON iu.Institut_id=i.Institut_id WHERE r.rolename='M-Admin' AND iu.inst_perms='admin' ORDER BY u.Nachname, i.Name");
		while($db->next_record())
		{
			$user = array(
				'username' => $db->f('username'),
				'vorname' => $db->f('Vorname'),
				'nachname' => $db->f('Nachname'),
				'institut' => $db->f('Name')
			);
			$madmins[] = $user;
		}
		return $madmins;
	}

	/**
	 * Sucht die Veranstaltungen raus, die nicht kopiert werden dürfen und gibt
	 * diese als Array zurück
	 *
	 * @param array() $seminars
	 * @param string $user
	 * @return array()
	 */
	public static function checkSeminars($seminars, $user)
	{
	    $db = new DB_Seminar;
	    $db->queryf("SELECT Institut_id FROM user_inst WHERE user_id = '%s' AND inst_perms = 'admin'", $user);
	    while ($db->next_record())
	    {
	        $user_inst_admin[] = $db->f("Institut_id");
	    }
	    foreach ($seminars as $val)
	    {
	        $has_perms = 0;
	        $sql =  "SELECT i.Institut_id, i.fakultaets_id FROM seminare s ".
	                "LEFT JOIN Institute i ON s.Institut_id=i.Institut_id ".
	                "WHERE Seminar_id = '".$val."'";
	        $db->query($sql);
	        $db->next_record();
	        $i_id = $db->f("Institut_id");
	        $f_id = $db->f("fakultaets_id");
	        foreach ($user_inst_admin as $val2)
	        {
	            if (($i_id === $val2) || ($f_id === $val2))
	            {
	                $has_perms++;
	            }
	        }
	        if (!$has_perms)
	        {
	            $sem_array[1][] = $val; // diese Veranstaltung darf nicht kopiert werden
	        } else {
	            $sem_array[0][] = $val; // diese schon
	        }
	    }
	    return $sem_array;
	}

	/**
	 * Kopiert konkrete Module von einem Semester in ein anderes
	 *
	 * @param array() $modules
	 * @param string $semester
	 * @param string $userid
	 * @return array values of copied and already existing modules
	 */
	public static function copyModules($modules, $semester, $userid)
	{
		$i = 0;
		$j = 0;
		foreach ($modules as $modul)
		{
			$result = DBManager::get()->query("SELECT DISTINCT * FROM module WHERE modul_kon_id = '{$modul}'")->fetch(PDO::FETCH_ASSOC);
			$check = DBManager::get()->query("SELECT DISTINCT * FROM module WHERE semester = '{$semester}' AND modul_abst_id = '{$result['modul_abst_id']}'")->fetch(PDO::FETCH_ASSOC);

			if(!empty($result) && empty($check))
			{
				$newkonid = md5($modul.rand());

				//1. db module
				DBManager::get()->exec("INSERT INTO module
				(modul_kon_id, modul_abst_id, desk_id, komplett, semester, kp, letzteAenderung, aktiv, user_id, notenart, pruef_form, pruef_zeiten, mok, institut_id, strukturschluessel)
				VALUES (
					'{$newkonid}',
					'{$result['modul_abst_id']}',
					'{$result['desk_id']}',
					'{$result['komplett']}',
					'{$semester}',
					'{$result['kp']}',
					UNIX_TIMESTAMP(NOW()),
					'{$result['aktiv']}',
					'{$userid}',
					'{$result['notenart']}',
					'{$result['pruef_form']}',
					'{$result['pruef_zeiten']}',
					'{$result['mok']}',
					'{$result['institut_id']}',
					'{$result['strukturschluessel']}')");

				//2. db modul_lehrform
				$modul_lehrform_results = DBManager::get()->query("SELECT * FROM modul_lehrform WHERE modul_kon_id = '{$modul}';")->fetchAll(PDO::FETCH_ASSOC);
				if(!empty($modul_lehrform_results))
				{
					foreach ($modul_lehrform_results as $modul_lehrform)
					{
						DBManager::get()->exec("INSERT INTO modul_lehrform (modul_kon_id, veranst_typ_id, sws) VALUES ('{$newkonid}', '{$modul_lehrform['veranst_typ_id']}', '{$modul_lehrform['sws']}')");
					}
				}

				//3. db modul_sprach_zuord
				$modul_sprach_zuord_results = DBManager::get()->query("SELECT * FROM mod_sprach_zuord WHERE modul_kon_id = '{$modul}';")->fetchAll(PDO::FETCH_ASSOC);
				if(!empty($modul_sprach_zuord_results))
				{
					foreach ($modul_sprach_zuord_results as $modul_sprach_zuord)
					{
						DBManager::get()->exec("INSERT INTO mod_sprach_zuord (modul_kon_id, sprach_id) VALUES ('{$newkonid}', '{$modul_sprach_zuord['sprach_id']}')");
					}
				}

				//4. db modul_fachsem
				$modul_fachsem_results = DBManager::get()->query("SELECT * FROM mod_fachsem WHERE modul_kon_id = '{$modul}';")->fetchAll(PDO::FETCH_ASSOC);
				if(!empty($modul_fachsem_results))
				{
					foreach ($modul_fachsem_results as $modul_fachsem)
					{
						DBManager::get()->exec("INSERT INTO mod_fachsem (modul_kon_id, fachsem_id) VALUES ('{$newkonid}', '{$modul_fachsem['fachsem_id']}')");
					}
				}

				//5. db modul_bereich_zuord
				$modul_bereich_zuord_results = DBManager::get()->query("SELECT * FROM mod_bereich_zuord WHERE modul_kon_id = '{$modul}';")->fetchAll(PDO::FETCH_ASSOC);
				if(!empty($modul_bereich_zuord_results))
				{
					foreach ($modul_bereich_zuord_results as $modul_bereich_zuord)
					{
						DBManager::get()->exec("INSERT INTO mod_bereich_zuord (modul_kon_id, bereich_id) VALUES ('{$newkonid}', '{$modul_bereich_zuord['bereich_id']}')");
					}
				}

				//6. db modul_schwerp_zuord
				$modul_schwerp_zuord_results = DBManager::get()->query("SELECT * FROM mod_schwerp_zuord WHERE modul_kon_id = '{$modul}';")->fetchAll(PDO::FETCH_ASSOC);
				if(!empty($modul_schwerp_zuord_results))
				{
					foreach ($modul_schwerp_zuord_results as $modul_schwerp_zuord)
					{
						DBManager::get()->exec("INSERT INTO mod_schwerp_zuord (modul_kon_id, schwerpunkt_id) VALUES ('{$newkonid}', '{$modul_schwerp_zuord['schwerpunkt_id']}')");
					}
				}

				//7. db modul_verantwort_zuord
				$modul_verantwort_zuord_results = DBManager::get()->query("SELECT * FROM mod_verantwort_zuord WHERE modul_kon_id = '{$modul}';")->fetchAll(PDO::FETCH_ASSOC);
				if(!empty($modul_verantwort_zuord_results))
				{
					foreach ($modul_verantwort_zuord_results as $modul_verantwort_zuord)
					{
						DBManager::get()->exec("INSERT INTO mod_verantwort_zuord (modul_kon_id, user_id, verantwort_typ_id) VALUES ('{$newkonid}', '{$modul_verantwort_zuord['user_id']}', '{$modul_verantwort_zuord['verantwort_typ_id']}')");
					}
				}

				$i++;
			}
			elseif(!empty($check))
			{
				$j++;
			}
		}
		return array($i, $j);
	}
	

}

?>
