<?php
/**
 * PersonenSuchePlugin.class.php
 *
 * lange Beschreibung...
 * Diese Datei enthält die Hauptklasse des Plugins
 *
 * PHP version 5
 *
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @package     IBIT_PersonenSuchePlugin
 * @copyright   2008-2010 IBIT
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 3
 * @version     1.0
 * @todo        TODO: Institute und Gruppen editieren
 *              TODO: PDO und Templates...
 */

// IMPORTS
require_once (dirname(__FILE__).'/classes/StudiumGeneraleRtfDB.class.php');
require_once ('lib/raumzeit/raumzeit_functions.inc.php');
require_once ('lib/dates.inc.php');
/**
 * Enter description here...
 *
 */
class PersonenSuchePlugin extends StudIPPlugin implements SystemPlugin
{
    private $template_factory;
    public $user;

    public function __construct()
    {
        parent::__construct();
        $this->user = new StudIPUser();

        $this->buildMenu();
        $this->path = str_replace($GLOBALS['ABSOLUTE_PATH_STUDIP'], '', dirname(__FILE__));
        $this->template_factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');
    }

    public function initialize()
    {
        $GLOBALS['_include_additional_header'] .= '<script src="'.$this->getPluginURL().'/js/move.js" type="text/javascript" ></script>'."\n";
    }

    private function displayHeader()
    {
        $request = Request::getInstance();

        $tabs = array(
            'show' => 'Einrichtungsverzeichnis',
            'tele' => 'Telefonverzeichnis',
            'names' => array(
                'title' => 'Namensverzeichnis',
                'tabs' => array(
                    'names' => 'Namensverzeichnis',
                    'listCourses' => 'StudiumGeneraleRTF',
                    'inconsistentData' => 'Inkonsistente Daten suchen'
                ),
            ),
        );

        //aktiven Reiter ermitteln
        $command = !empty($_REQUEST['cmd'])
            ? $_REQUEST['cmd']
            : array_shift(explode('?', $GLOBALS['unconsumed'].'?'));
        if (empty($command))
            $command = 'show';

        foreach ($tabs as $index => $data)
        {
            $navigation = new Navigation(is_array($data) ? $data['title'] : $data, PluginEngine::GetLink($this, array(), $index));
            Navigation::addItem('/personenverwaltung/'.$index, $navigation);

            $active = false;
            if (is_array($data))
                foreach ($data['tabs'] as $key => $title)
                {
                    $navigation = new Navigation($title, PluginEngine::GetLink($this, array(), $key));
                    $navigation->setActive($key==$command);
                    Navigation::addItem('/personenverwaltung/'.$index.'/'.$key, $navigation);

                    $active = ($active or $key==$command);
                }
            $navigation = Navigation::getItem('/personenverwaltung/'.$index);
            $navigation->setActive($active or $index == $command);
            Navigation::addItem('/personenverwaltung/'.$index, $navigation);
        }
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getPluginname()
    {
        return _('PersonenSuchePlugin');
    }

    private function checkLogin()
    {
        // Login prüfen
        if (!isset($_SESSION['auth']) or !is_object($_SESSION['auth']) or !isset($_SESSION['auth']->auth) or !isset($_SESSION['auth']->auth['uid']) or ($_SESSION['auth']->auth['uid']=='nobody'))
        {
            echo MessageBox::error('Sie müssen eingeloggt sein, um dieses Plugin aufrufen zu dürfen');
            die();
        }

        // Falls eingeloggt, Rechtestatus checken
        $persadmin = false;
        foreach ($this->user->getAssignedRoles() as $role)
            if ($role->rolename == 'personen_admin')
                $persadmin = true;

        // Nur Root sieht das Menü
        return ($persadmin == true || $this->user->getPermission()->hasRootPermission());
    }

    public function buildMenu()
    {
        if (!$this->checkLogin())
            return;

        $navigation = new Navigation(_('Personenverwaltung'), PluginEngine::GetLink($this, array(), 'show'));
        $navigation->setImage($this->getPluginURL().'/img/personenverwaltung.png');
        //$navigation->setImage('/icons/16/blue/community.png');
        Navigation::addItem('/personenverwaltung', $navigation);
    }

    //Regelt die Anzeige
    public function show_action()
    {
        $request = Request::getInstance();
        $this->displayheader();
        if (!empty($request['bearbeiten']))
            $this->edit();
        else
            $this->search();
    }
    /*
     *
     * Haupt funktion von Studipgeneralertf
     */

    public function ListCourses_action()
    {
        $request = Request::getInstance();
        $semObject = new SemesterData;

        $sem = $semObject->getCurrentSemesterData();

        $current_semester = $sem['semester_id'];

        $this->displayHeader();

        $template = $this->template_factory->open('course_index');
        $template->set_layout( $GLOBALS['template_factory']->open('layouts/base_without_infobox.php') );

        //semester ausgewählt
        if ($request['action'] == 'selectsemester')
        {
            $current_semester = $request['semester'];
            $lehreinheiten = PersoStudiumGeneraleRtfDB::getLehreinheiten($this->user->userid, $this->user->getPermission()->hasRootPermission(), true);

            foreach ($lehreinheiten as $key=>$lehreinheit)
            {
                $verzeichnisse[$key]['name'] = $lehreinheit;
                $verzeichnisse[$key]['faecher'] = PersoStudiumGeneraleRtfDB::getFaecher('18ca85409be550468d39fc2c0c8313c6');
            }

            $anzahl = $veranstaltungen = PersoStudiumGeneraleRtfDB::getCountVeranstaltungInfos('18ca85409be550468d39fc2c0c8313c6', $request['semester'], $request['va_typ']);

        }


        //Ausgabe
        $template->set_attribute('semester', $semObject->getAllSemesterData());
        $template->set_attribute('current_semester', $current_semester);
        $template->set_attribute('plugin_url', $this->getPluginURL());
        $template->set_attribute('link1', PluginEngine::getLink($this, $this->parameters, 'listCourses'));
        $template->set_attribute('link2', PluginEngine::getLink($this, array('hiddencourses' => $request['hiddencourses'], 'vdetails' => $request['vdetails'], 'type' => 'rtf', 'file' => 'verzeichnis'), 'generateExport'));
        $template->set_attribute('link3', PluginEngine::getLink($this, array('hiddencourses' => $request['hiddencourses'], 'vdetails' => $request['vdetails'], 'type' => 'xml', 'file' => 'verzeichnis'), 'generateExport'));
        $template->set_attribute('link4', PluginEngine::getLink($this, array(), 'inconsistentData'));
        $template->set_attribute('select_hiddencourses', $request['hiddencourses']);
        $template->set_attribute('select_vdetails', $request['vdetails']);
        $template->set_attribute('verzeichnisse', $verzeichnisse);
        $template->set_attribute('va_typ', $request['va_typ']);
        $template->set_attribute('anzahl', $anzahl);
        #$template->set_attribute('infobox', $this->getInfobox());
        echo $template->render();
    }

    public function inconsistentData_action() {
        $request = Request::getInstance();
        $semObject = new SemesterData;

        $sem = $semObject->getCurrentSemesterData();

        $current_semester = $sem['semester_id'];

        // Ausgabe
        $this->displayHeader();
        $template = $this->template_factory->open('inconsitent');
        $template->set_layout( $GLOBALS['template_factory']->open('layouts/base.php') );

        if ($request['action'] == 'selectsemester')
        {
            $current_semester = $request['semester'];

            $results = PersoStudiumGeneraleRtfDB::getInconsitentData($current_semester, $request['hiddencourses'], $request['va_typ']);

            if(count($results) == 0) {
                $results = array();
            }
            $template->set_attribute('results', $results);

        }

        if(Request::submitted('speichern')) {
            if(!empty($request['semtree'])) {
                foreach($request['semtree'] AS $sem => $val) {
                    if($val == 1) {
                        $save[] = PersoStudiumGeneraleRtfDB::saveInconsitentSemTree($sem);
                    }
                }

                foreach($save AS $s) {
                    if($s == false) {
                        $msg = false;
                    }
                    else {
                        $msg = true;
                    }
                }

                if($msg == true) {
                    $message1 = MessageBox::success("Ihre Auswahl für die Module wurde korrekt gespeichert!");
                }
                else {
                    $message1 = MessageBox::error("Ihre Auswahl für die Module konnten nicht gespeichert werden!");
                }

                $template->set_attribute('message1', $message1);
            }

            if(!empty($request['datafield'])) {
                foreach($request['datafield'] AS $sem => $val) {
                    if($val == 1) {
                        $save[] = PersoStudiumGeneraleRtfDB::saveInconsitentDataField($sem);
                    }
                }

                foreach($save AS $s) {

                    if($s == false) {
                        $msg = false;
                    }
                    else {
                        $msg = true;
                    }
                }

                if($msg == true) {
                    $message2 = MessageBox::success("Ihre Auswahl für die Grunddaten wurde korrekt gespeichert!");
                }
                else {
                    $message2 = MessageBox::error("Ihre Auswahl für die Grunddaten konnten nicht gespeichert werden!");
                }

                $template->set_attribute('message2', $message2);
            }
            $results = PersoStudiumGeneraleRtfDB::getInconsitentData($current_semester, $request['hiddencourses']);
            if(count($results) == 0) {
                $results = array();
            }
            $template->set_attribute('results', $results);
        }

        $template->set_attribute('semester', $semObject->getAllSemesterData());
        $template->set_attribute('current_semester', $current_semester);
        $template->set_attribute('va_typ', $request['va_typ']);
        $template->set_attribute('plugin_url', $this->getPluginURL());
        $template->set_attribute('select_hiddencourses', $request['hiddencourses']);
            $template->infobox = array(
            'picture' => 'infobox/administration.jpg',
            'content' => array(array(
                "kategorie" => _("Information:"),
                "eintrag" => array(
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Sie haben zwei Möglichkeiten die Inkonsistenz in den Modulen und den Grunddaten wiederherzustellen.<br /><br />
                            Entweder Sie wählen jede Veranstaltung einzeln aus (setzen den jeweiligen Hacken) oder Sie setzen in Überschrift den Hacken für Module
                            oder Grunddaten und alle Veranstaltungen, die eine Inkonsistzen besitzen, werden ausgewählt.
                            <br /><br />
                            Mit einem Klick auf <strong>Speichern</strong>
                            wird die Inkonsistenz der Daten wiederhergestellt')
                )
            ))
        );
        echo $template->render();

    }
    /**
     * Holt alle Fakultäten und Institute aus der DB
     *
     * @return array()
     */
    private function getFaksAndInsts()
    {
        $faks = DBManager::Get()->query("SELECT * FROM Institute WHERE fakultaets_id=Institut_id ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);

        $statement = DBManager::get()->prepare("SELECT * FROM Institute WHERE fakultaets_id = ? AND fakultaets_id != Institut_id ORDER BY Name");
        foreach ($faks as $index=>$fak)
        {
            $statement->execute(array($fak['fakultaets_id']));
            $faks[$index]['institute'] = $statement->fetchAll(PDO::FETCH_ASSOC);
            $statement->closeCursor();
        }
        return $faks;
    }

    /**
     * Telefonverzeichnis
     */
    public function tele_action()
    {
        $this->displayheader();
        $request = Request::getInstance();
        $template = $this->template_factory->open('telefonverzeichnis');
        $template->set_layout( $GLOBALS['template_factory']->open('layouts/base_without_infobox.php') );

        if (!empty($request['bearbeiten'])){
            $this->edit();
        }

        if ($request['anzeigen']=='yes')
        {
            $template->set_attribute('faksinst_id', $request['faksinst']);
            $template->set_attribute('teleliste', $this->getTeleData($request['faksinst']));
        }
        $template->set_attribute('plugin_url', $this->getPluginURL());
        $template->set_attribute('rtfexportlink', PluginEngine::getLink($this, array(), 'generateExport'));
        $template->set_attribute('pluginpath', PluginEngine::getLink($this, array()));
        $template->set_attribute('path', $this->getPluginURL());
        $template->set_attribute('faks', $this->getFaksAndInsts());
        $template->set_attribute('anzeigen', $request['anzeigen']);
        $template->set_attribute('link', PluginEngine::getLink($this, array(), 'tele'));

        echo $template->render();
    }


    /**
     * Der RTF Export von StudiumGeneraleRTF
     *
     */
    private function OriginalStudiumGeneraleRTFexport()
    {

        $request = Request::getInstance();
        //Daten holen
        if ($request['file'] == 'verzeichnis')
        {
            $semdata = new SemesterData();
            $semester_data = $semdata->getSemesterData($request['semester']);

            $id = '18ca85409be550468d39fc2c0c8313c6'; //studium generale

            $statement = DBManager::get()->prepare("SELECT * FROM sem_tree WHERE sem_tree_id = ?");
            $statement->execute(array($id));
            $bereich = $statement->fetch(PDO::FETCH_ASSOC);

            if(!isset($request['va_typ']) || $request['va_typ'] == 0) {
                $veranstaltungen = PersoStudiumGeneraleRtfDB::getVeranstaltungInfos($id, $semester_data['semester_id']);
            }
            else {
                $veranstaltungen = PersoStudiumGeneraleRtfDB::getVeranstaltungInfos($id, $semester_data['semester_id'], $request['va_typ']);
            }


            foreach ($veranstaltungen as $key => $veranstaltung)
            {
                $veranstaltungen[$key]['rooms'] = PersoStudiumGeneraleRtfDB::getVeranstaltungsPlace($veranstaltung['seminar_id']);
                $veranstaltungen[$key]['times'] = PersoStudiumGeneraleRtfDB::getVeranstaltungsTime($veranstaltung['seminar_id']);
                $veranstaltungen[$key]['extradata'] = DBManager::Get()->query("SELECT dfe.datafield_id, df.name, df.type, dfe.content
                                                      FROM datafields_entries dfe, datafields df
                                                      WHERE dfe.range_id='$veranstaltung[seminar_id]' AND dfe.datafield_id=df.datafield_id")->fetchAll(PDO::FETCH_ASSOC);

                foreach ($veranstaltungen[$key]['extradata'] as $index => $extradata)
                {
                    if ($veranstaltungen[$key]['extradata'][$index]['type']=="bool")
                    {
                        switch ($veranstaltungen[$key]['extradata'][$index]['content']) {
                            case 0:
                                $veranstaltungen[$key]['extradata'][$index]['content'] = "Nein";
                            break;
                            case 1:
                                $veranstaltungen[$key]['extradata'][$index]['content'] = "Ja";
                            break;
                        }
                    }
                }
            }
        }

        //Ausgabe
        ob_end_clean();
        ob_start();

        //Nach Fakültät und Institut sortieren
        $veranstaltungen = PersoStudiumGeneraleRtfDB::sortVeranstaltungen($veranstaltungen, $request['hiddencourses']);

        if ($request['type'] == 'rtf')
        {
            header('Content-Type: application/rtf');
            if ($request['file'] == 'verzeichnis')
            {
                header("Content-Disposition: attachment; filename=studiumgenerale.rtf");
                $template = $this->template_factory->open('rtf_veranstaltungsbericht1');

                // zusätzliche filter
                if ($request['vdetails'] == 'yes')
                {
                    $template->set_attribute('vdetails', true);
                }

                if ($request['hiddencourses'] == 'yes')
                {
                    $template->set_attribute('hiddencourses', true);
                }

                $semdata = new SemesterData();
                $template->set_attribute('semester', $semester_data);

                $template->set_attribute('bereich', $bereich);
                $template->set_attribute('veranstaltungen', $veranstaltungen);
                $template->set_attribute('user', $this->user);
                echo $template->render();
            }
        }

        if($request['type'] == 'xml')
            {

            Header('Content-Type: text/xml; charset="UTF-8"');
            Header('Content-Disposition: attachment; filename=studiumgenerale.xml');
            Header('Pragma: no-cache');
            Header('Expires: 0');
            Header('Content-Encoding: UTF-8');

            $xml .= '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<studip version="2.0">';
            foreach($veranstaltungen AS $veranstaltung) {
                foreach($veranstaltung['institutes'] AS $institut) {
                    $xml .= '<institut name="'.htmlSpecialChars(utf8_encode($institut['Name'])).'">';
                    foreach($institut['courses'] AS $seminar) {
                        // $xml .= '<seminare>';
                        $xml .= '<veranstaltungsnummer>'.htmlSpecialChars(utf8_encode($seminar['veranstaltungsnummer'])).'</veranstaltungsnummer>';
                        $xml .= '<titel>' . htmlSpecialChars(utf8_encode($seminar['name'])) . '</titel>';
                        $xml .= '<form>' . htmlSpecialChars(utf8_encode($GLOBALS['SEM_TYPE'][$seminar['status']]['name'])) . '</form>';
                        $anz = 1;
                        foreach($seminar['authors'] as $author) {
                            $dozenten .= htmlSpecialChars(utf8_encode($author['vorname'])).' '.htmlSpecialChars(utf8_encode($author['name']));
                            if($anz < count($seminar['authors'])) {
                                $dozenten .= ", ";
                            }
                            $anz++;
                        }
                        $xml .= '<dozent>'.$dozenten.'</dozent>';
                        unset($dozenten);
                        $xml .= "<termin>".utf8_encode($seminar['times'])."</termin>";
                        $xml .= "<raum>".utf8_encode($seminar['rooms'])."</raum>";
                         if ($request['vdetails'] == 'yes')
                        {
                            $xml .= '<beschreibung>' . htmlSpecialChars(utf8_encode($seminar['beschreibung'])) . '</beschreibung>';
                        }
                    }
                    $xml .= '</institut>';
                }

            }
            $xml .= "</studip>";
        }

        // Pretty print, taken and adapted from google
        $level = 1;
        $indent = 0;
        $pretty = array();

        $xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml));

        if (count($xml) and preg_match('/^<\?\s*xml/', reset($xml))) {
            array_push($pretty, array_shift($xml));
        }

        foreach ($xml as $node) {
            if (preg_match('/^<[\w]+[^>\/]*>$/U', $node)) {
                array_push($pretty, str_repeat("\t", $indent) . $node);
                $indent += $level;
            } else {
                if (preg_match('/^<\/.+>$/', $node)) {
                    $indent -= $level;
                }

                if ($indent < 0) {
                    $indent += $level;
                }

                array_push($pretty, str_repeat("\t", $indent) . $node);
            }
        }
        $xml = implode("\n", $pretty);

		echo $xml;
        ob_end_flush();
        die;
    }

    /**
     * Telefonverzeichnis RTF export
     *
     */
    public function generateExport_action()
    {
        $request = Request::getInstance();

        //Wenn StudiumGeneraleExport, dann gehts zu OriginalStudiumGeneraleRTFexport
        if ($request['action']=="studiumgeneralertf")
            $this->OriginalStudiumGeneraleRTFexport();


        //XML Export
        if($request['action'] == 'studiumgeneralexml')
            $this->OriginalStudiumGeneraleRTFexport();

        if (!empty($request['faksinst']))
        {

            header('Content-Type: application/rtf');
            header("Content-Disposition: attachment; filename=telefonverzeichnis.rtf");
            $template = $this->template_factory->open('telefonverzeichnis.rtf.php');
            $template->set_attribute('faksinst_id', $request['faksinst']);
            $template->set_attribute('teleliste', $this->getTeleData($request['faksinst']));

            ob_end_clean();
            ob_start();

            //Name der Aktuellen Faku. oder Inst.

            $faksandinsts=$this->getFaksAndInsts();
            foreach ($faksandinsts as $fak)
                if ($fak['Institut_id']==$request['faksinst'])
                    $template->set_attribute('aktaktinst', $fak['Name']);

            $teleliste = $this->getTeleData($request['faksinst']);

            echo $template->render();
            die();
        }
        else
        {
            echo MessageBox::error('Ein interner Fehler ist aufgetreten!', array('Es wurde keine ID übergeben'), true);
            $this->tele_action();
            die();
        }
    }

    public function names_action()
    {
        $this->displayheader();
        $template = $this->template_factory->open('namensverzeichnis');
        $template->set_layout( $GLOBALS['template_factory']->open('layouts/base_without_infobox.php') );

        $template->set_attribute('plugin_url', PluginEngine::getLink($this, array()));
        $template->set_attribute('pluginpath', PluginEngine::getLink($this, array()));
        $template->set_attribute('path', $this->getPluginURL());
        $template->set_attribute('faks', $this->getFaksAndInsts());

        $core = new StudIPCore;
        $template->set_attribute('semester', $core->getSemester());
        $template->set_attribute('link', PluginEngine::getLink($this, array(), 'namesexport'));
        echo $template->render();
    }


    private function getNames($semester_id, $type, $generale)
    {
        if ($type=="alle")
        {
            $names = DBManager::Get()->query("SELECT * FROM auth_user_md5 WHERE perms = 'dozent' AND Vorname != '' AND Nachname != '' ORDER BY Nachname, Vorname")->fetchAll(PDO::FETCH_ASSOC);
        }
        elseif ($type=="lehrende")
        {
            $semObject = new SemesterData;
            $sem_data = $semObject->getSemesterData($semester_id);

            if ($generale!='yes')
            {
                // Lehrende die im gewuenschten Semester Seminare begleiten
                $names = DBManager::Get()->query("SELECT DISTINCT seminar_user.user_id, auth_user_md5.Nachname, auth_user_md5.Vorname
                    FROM seminare, seminar_user, auth_user_md5
                    WHERE seminare.start_time = '$sem_data[beginn]'
                        AND seminar_user.Seminar_id = seminare.Seminar_id
                        AND seminar_user.status='dozent'
                        AND auth_user_md5.user_id=seminar_user.user_id
                    ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname")->fetchAll(PDO::FETCH_ASSOC);
            }
            else
            {
                // Studium Generale-> Nur Dozenten die fuer SG Seminare geoeffnet haben
                $names = DBManager::Get()->query("SELECT DISTINCT seminar_user.user_id, au.Nachname, au.Vorname
                    FROM seminare, seminar_user,seminar_sem_tree, auth_user_md5 au
                    WHERE seminare.start_time = '$sem_data[beginn]'
                        AND seminar_sem_tree.sem_tree_id = '18ca85409be550468d39fc2c0c8313c6'
                        AND seminar_sem_tree.Seminar_id = seminare.Seminar_id
                        AND seminar_user.status='dozent'
                        AND seminar_user.Seminar_id = seminare.Seminar_id
                        AND au.user_id=seminar_user.user_id
                    ORDER BY au.Nachname, au.Vorname")->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        foreach ($names as $key => $name)
            $names[$key] = $this->getFullUserData($name['user_id']);

        return $names;
    }

    private function getFullUserData($user_id)
    {
        //Daten der Person holen
        $person = DBManager::Get()->query("SELECT DISTINCT au.Vorname, au.Nachname, au.Email, ui.Home, ui.title_front, ui.title_rear, ui.privatnr, ui.privatcell, ui.privadr
            FROM auth_user_md5 au
            LEFT JOIN user_info ui ON au.user_id=ui.user_id
            WHERE au.user_id='{$user_id}'
            ORDER BY au.Nachname")->fetch(PDO::FETCH_ASSOC);

        $person['title_front'] = trim($person['title_front']);
        $person['title_rear'] = trim($person['title_rear']);
        $person['Vorname'] = trim($person['Vorname']);
        $person['Nachname'] = trim($person['Nachname']);

        //Gruppen eines benutzers holen
        $statusgruppe_user = DBManager::Get()->query("SELECT statusgruppe_id FROM statusgruppe_user WHERE user_id='".$user_id."'")->fetchAll(PDO::FETCH_COLUMN);

        //Eingetragene Einrichtungen der Person holen
        $person['institute'] = DBManager::Get()->query("SELECT user_inst.Institut_id, Institute.name, fak.name as fak_name
            FROM user_inst
            LEFT JOIN Institute ON (Institute.Institut_id=user_inst.Institut_id)
            LEFT JOIN Institute fak ON (fak.Institut_id=Institute.fakultaets_id)
            WHERE user_id='".$user_id."'")->fetchAll(PDO::FETCH_ASSOC);

        $person['schwerp'] = DBManager::Get()->query("SELECT dfe.content
            FROM datafields_entries dfe, datafields df
            WHERE dfe.range_id='$user_id'
                AND df.name='PVSchwerpunkte'
                AND dfe.datafield_id=df.datafield_id")->fetch(PDO::FETCH_COLUMN);

        foreach ($person['institute'] as $key => $institut)
        {
            $person['institute'][$key] = array_merge($person['institute'][$key], DBManager::Get()->query("SELECT Institut_id, raum, Telefon, sprechzeiten, Fax, externdefault
                FROM user_inst
                WHERE user_id='".$user_id."'
                    AND Institut_id='".$institut['Institut_id']."'")->fetch(PDO::FETCH_ASSOC));

            //Gruppen eines Instituts holen
            $person['institute'][$key]['gruppen'] = DBManager::Get()->query("SELECT statusgruppen.name
                FROM statusgruppen, statusgruppe_user
                WHERE statusgruppen.range_id='".$institut['Institut_id']."'
                    AND statusgruppe_user.user_id='$user_id'
                    AND statusgruppen.statusgruppe_id=statusgruppe_user.statusgruppe_id
                ORDER BY statusgruppe_user.position ASC")->fetchAll(PDO::FETCH_COLUMN);

            switch ($person['institute'][$key]['fak_name'])
            {
                case "Fakultät 1: Bildungs- und Sozialwissenschaften":
                    $person['institute'][$key]['fak_name'] = "Fk. I";
                break;
                case "Fakultät 2: Informatik, Wirtschaft- und Rechtswissenschaften":
                    $person['institute'][$key]['fak_name'] = "Fk. II";
                break;
                case "Fakultät 3: Sprach- und Kulturwissenschaften":
                    $person['institute'][$key]['fak_name'] = "Fk. III";
                break;
                case "Fakultät 4: Human- und Gesellschaftswissenschaften":
                    $person['institute'][$key]['fak_name'] = "Fk. IV";
                break;
                case "Fakultät 5: Mathematik und Naturwissenschaften":
                    $person['institute'][$key]['fak_name'] = "Fk. V";
                break;
                default:
                    $person['institute'][$key]['fak_name'] = $person['institute'][$key]['fak_name'];
            }

            switch ($person['institute'][$key]['name'])
            {
                case "Fakultät 1: Bildungs- und Sozialwissenschaften":
                    $person['institute'][$key]['name'] = "Fk. I";
                break;
                case "Fakultät 2: Informatik, Wirtschaft- und Rechtswissenschaften":
                    $person['institute'][$key]['name'] = "Fk. II";
                break;
                case "Fakultät 3: Sprach- und Kulturwissenschaften":
                    $person['institute'][$key]['name'] = "Fk. III";
                break;
                case "Fakultät 4: Human- und Gesellschaftswissenschaften":
                    $person['institute'][$key]['name'] = "Fk. IV";
                break;
                case "Fakultät 5: Mathematik und Naturwissenschaften":
                    $person['institute'][$key]['name'] = "Fk. V";
                break;
                default:
                    $person['institute'][$key]['name'] = $person['institute'][$key]['name'];
            }

            foreach ($person['institute'][$key]['gruppen'] as $ind => $gruppe)
            {
                //echo $person['institute'][$key]['gruppen'][$ind]."<br>";
                switch ($gruppe)
                {
                    case "wiss. MitarbeiterIn":
                        $person['institute'][$key]['gruppen'][$ind] = "WM";
                    break;
                    case "Lehrbeauftragte":
                        $person['institute'][$key]['gruppen'][$ind] = "LB";
                    break;
                }
            }
        }

        return $person;
    }

    /**
     * RTF erzeugen
     */
    public function Namesexport_action()
    {
        $request = Request::getInstance();

        $names = $this->getNames($request['semester_id'], $request['type'], $request['generale']);

        ob_end_clean();
        ob_start();

        header("Window-target: _blank");
        header('Content-Type: application/rtf');
        header("Content-Disposition: attachment; filename=namensverzeichnis.rtf");

        $template = $this->template_factory->open('namensverzeichnis.rtf.php');
        $template->set_attribute('names', $names);

        if ($request['type']=="alle") {
            $list_type = "Alle Mitarbeiter";
        } elseif (!empty($request['semester_id'])) {
            $semObject = new SemesterData;
            $sem_data = $semObject->getSemesterData($request['semester_id']);
            $list_type = "Lehrende im ".$sem_data['name'];
            if ($request['generale'] == true)
                $list_type .= " die Studium Generale zugestimmt haben";
        }
        $template->set_attribute('schwerp', $request['schwerp']);
        $template->set_attribute('list_type', $list_type);
        echo $template->render();

        ob_end_flush();
    }


    public function edit()
    {
        $request = Request::getInstance();
        $inst=$request["institut_id"];

        if (!empty($request["user_id"])&&!empty($request['EditUserData'])) {
            //>>>>>>>>>>>>>>LOGGING ANFANG
            //Alte Daten der Person holen
            $person = DBManager::Get()->query("SELECT au.Vorname, au.Nachname, au.Email,
            ui.Home, ui.schwerp, ui.title_front, ui.title_rear
            FROM auth_user_md5 au
            LEFT JOIN user_info ui ON au.user_id=ui.user_id
            WHERE au.user_id='{$request['user_id']}'")->fetch();

            $person_inst_data = DBManager::Get()->query("SELECT Institut_id, raum, Telefon, sprechzeiten, Fax, externdefault
            FROM user_inst
            WHERE user_id='".$request['user_id']."' AND Institut_id='".$request['faksinst']."'")->fetch();

            //Datenabgleich
            if ($person['Home']!=$request['home']) $edit[] = "Homepage";
            if ($person['schwerp']!=$request['schwerp']) $edit[] = "Schwerpunkt";
            if ($person['title_front']!=$request['title_front']) $edit[] = "Titel vorne";
            if ($person['title_rear']!=$request['title_rear'])  $edit[] = "Titel hinten";

            //Logstring erstellen
            if (!empty($edit)) {
                $log .= "(";
                foreach ($edit as $i=>$ed) {
                    if ($i>0) $log .= ", ";
                    else $log .= "Userdaten: ";
                    $log .= $ed;
                }
                $log .= ")";
            }

            //Datenabgleich
            if ($person_inst_data['Fax']!=$request['fax']) $edit1[] = "Fax";
            if ($person_inst_data['raum']!=$request['raum']) $edit1[] = "Raum";
            if ($person_inst_data['Telefon']!=$request['telefon']) $edit1[] = "Telefon";
            if ($person_inst_data['sprechzeiten']!=$request['sprechzeiten'])    $edit1[] = "Sprechzeiten";

            //Logstring erstellen
            if (!empty($edit1)) {
                $log .= "(";
                foreach ($edit1 as $i=>$ed) {
                    if ($i>0) $log .= ", ";
                    else $log .= "Institut ".$request['faksinst'].": ";
                    $log .= $ed;
                }

                $log .= ")";
            }

            //Gruppen---------------

            //Gruppen eines Instituts holen
            $all_groups = array();
            $all_groups = DBManager::Get()->query("SELECT name,statusgruppe_id,position
                                                      FROM statusgruppen
                                                      WHERE range_id='".$inst."' ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);

            $save_groups="";
            foreach ($all_groups as $group) {
                $save_groups .= $group['statusgruppe_id'].";";
            }

            //Alte Gruppen eines Benutzers holen
            $statusgruppe_user=array();
            $statusgruppe_user = DBManager::Get()->query("SELECT statusgruppe_id
                                                          FROM statusgruppe_user
                                                          WHERE user_id='".$request["bearbeiten"]."'")->fetchAll(PDO::FETCH_COLUMN);

            foreach ($all_groups as $group) {
                $groups[] = $group['statusgruppe_id'];
            }
            $alte_gruppen = array();
            foreach ($statusgruppe_user as $statusgruppe) {
                if (in_array($statusgruppe, $groups)) {
                    $alte_gruppen[] = $statusgruppe;
                }
            }

            $gruppen=explode(";",$request['Items']);
            $neue_gruppen = array();
            for($i=0;$i<count($gruppen);$i++)
            {
                $list=explode("#",$gruppen[$i]);
                if (!empty($list[0])) {
                    $neue_gruppen[] = $list[0];
                }
            }

            $empty1 = array_diff($alte_gruppen, $neue_gruppen);
            $empty2 = array_diff($neue_gruppen, $alte_gruppen);

            if (!empty($empty1) OR !empty($empty2))
                $log .= "(Gruppen)";
            //>>>>>>>>>>>>>>LOGGING ENDE


            //Realen Edit erst nach logging ausführen
            $this->EditUserData();
        }


        if (!empty($request["delete"])&&!empty($request["user_id"])) {
            if ($this->DeleteUserInst())
            $inst = DBManager::Get()->query("SELECT Institut_id
                                             FROM user_inst
                                             WHERE user_id='".$_GET["bearbeiten"]."'")->fetch();
            $inst = $inst['Institut_id'];
            //>>>>>>>>>>>>>>LOGGING ANFANG
            $log .= "(Institut entfernt)";
            //>>>>>>>>>>>>>>LOGGING ENDE
        }

        if (!empty($_POST["faksinst"])&&!empty($request["user_id"]) &&!empty($request['InsertUserInst'])) {
            if ($this->InsertUserInst()) {
                $inst=$request["faksinst"];
            }
            //>>>>>>>>>>>>>>LOGGING ANFANG
            $log .= "(Institut hinzugefügt)";
            //>>>>>>>>>>>>>>LOGGING ENDE
        }

        //>>>>>>>>>>>>>>LOGGING ANFANG
        if (empty($log))
            $log = "Ohne Änderungen übernommen";

        log_event('PERSSU_PERSON_EDITED',$request["user_id"],null,$log);
        //>>>>>>>>>>>>>>LOGGING ENDE

        //Daten der Person holen
        $person = DBManager::Get()->query("SELECT au.Vorname, au.Nachname, au.Email,
        ui.Home, ui.schwerp, ui.title_front, ui.title_rear
        FROM auth_user_md5 au
        LEFT JOIN user_info ui ON au.user_id=ui.user_id
        WHERE au.user_id='{$request['bearbeiten']}'")->fetch();

        $person_inst_data = DBManager::Get()->query("SELECT Institut_id, raum, Telefon, sprechzeiten, Fax, externdefault
        FROM user_inst
        WHERE user_id='".$request["bearbeiten"]."' AND Institut_id='".$inst."'")->fetch();

        //Eingetragene Einrichtungen der Person holen
        $person_institude = DBManager::Get()->query("SELECT user_inst.Institut_id, Institute.name FROM user_inst LEFT JOIN Institute ON (Institute.Institut_id=user_inst.Institut_id) WHERE user_id='".$_GET["bearbeiten"]."'")->fetchAll();

        //Gruppen eines Instituts holen
        $all_groups = array();
        $all_groups = DBManager::Get()->query("SELECT name,statusgruppe_id,position
                                                  FROM statusgruppen
                                                  WHERE range_id='".$inst."' ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);

        $save_groups="";
        foreach ($all_groups as $group) {
            $save_groups .= $group['statusgruppe_id'].";";
        }

        //Gruppen eines benutzers holen
        $statusgruppe_user=array();
        $statusgruppe_user = DBManager::Get()->query("SELECT statusgruppe_id
                                                      FROM statusgruppe_user
                                                      WHERE user_id='".$_GET["bearbeiten"]."'")->fetchAll(PDO::FETCH_COLUMN);

        //<option>-Eintrüge für die Gruppenboxen erzeugen
        $user_gruppen="";
        $gruppen="";
        for($i=0;$i<=count($all_groups);$i++)
        {
          $check=true;

          for($k=0;$k<=count($statusgruppe_user);$k++)
          {
            if ($all_groups[$i]['statusgruppe_id']==$statusgruppe_user[$k]&&!empty($all_groups[$i]['statusgruppe_id']))
            {
            $user_gruppen.="<option value='".$all_groups[$i]['statusgruppe_id']."#".$all_groups[$i]['position']."'>".$all_groups[$i]['name']."</option>";
            $check=false;
            }
          }
          if ($check&&!empty($all_groups[$i]['statusgruppe_id']))
          {
            $gruppen.="<option value='".$all_groups[$i]['statusgruppe_id']."#".$all_groups[$i]['position']."'>".$all_groups[$i]['name']."</option>";
          }

        }

        //Ist Standard-Adresse?
        if ($person_inst_data['externdefault'])
         {
            $checked2="checked";
         }

        //Daten dem Template zuweisen
        $template = $this->template_factory->open('edit.php');
        $template->set_attribute('link', PluginEngine::getLink($this, array()));
        $template->set_attribute('title_front', $GLOBALS['TITLE_FRONT_TEMPLATE']);
        $template->set_attribute('title_rear', $GLOBALS['TITLE_REAR_TEMPLATE']);
        $template->set_attribute('user_id', $request['bearbeiten']);
        $template->set_attribute('person', $person);
        $template->set_attribute('person_institude', $person_institude);
        $template->set_attribute('person_inst_data', $person_inst_data);
        $template->set_attribute('faksinst', $this->getInstMenue());
        $template->set_attribute('gruppen', $gruppen);
        $template->set_attribute('user_gruppen', $user_gruppen);
        $template->set_attribute('checked2', $checked2);
        $template->set_attribute('save_groups', $save_groups);
        $template->set_attribute('path', $this->path);
        $template->set_attribute('plupath', PluginEngine::getLink($this, array('bearbeiten' => $_GET['bearbeiten'])));
        $template->set_layout( $GLOBALS['template_factory']->open('layouts/base_without_infobox.php') );
        echo $template->render();
    }

    private function getInstMenue() {
        //Instituts-Menü
        $tempfaks = DBManager::Get()->query("SELECT * FROM Institute I WHERE fakultaets_id=Institut_id ORDER BY NAME")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tempfaks as $tempfak) {
            $faksinst.="<option style='font-weight: bold;' value='".$tempfak["Institut_id"]."'>".$tempfak['Name']."</option>";
            $tempinsts = DBManager::Get()->query("SELECT * FROM Institute I WHERE fakultaets_id='".$tempfak["fakultaets_id"]."' ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($tempinsts as $tempinst) {
                if ($tempfak["fakultaets_id"]==$tempinst["Institut_id"])continue;
                $faksinst.="<option style='font-weight: normal;' value='".$tempinst["Institut_id"]."'>".$tempinst["Name"]."</option>";
            }
        }
        return $faksinst;
    }

    private function DeleteUserInst() {
        DBManager::Get()->exec("DELETE FROM user_inst WHERE user_id='".$_POST["user_id"]."' AND institut_id='".$_POST["delete"]."'");

        $status_gruppen = DBManager::Get()->query("SELECT statusgruppe_id FROM statusgruppen WHERE range_id='".$_POST["delete"]."'")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($status_gruppen as $status_gruppe) {
            DBManager::Get()->exec("DELETE FROM `statusgruppe_user` WHERE user_id='".$_POST["user_id"]."' AND statusgruppe_id='".$status_gruppe."'");
        }
        return true;
    }

    private function InsertUserInst() {
        if (!DBManager::Get()->query("SELECT * FROM user_inst WHERE user_id='".$_POST["user_id"]."' AND institut_id='".$_POST["faksinst"]."'")->fetchAll(PDO::FETCH_COLUMN)) {
            DBManager::Get()->exec("INSERT INTO user_inst (user_id,Institut_id,inst_perms,sprechzeiten,raum,Telefon,Fax,externdefault,priority,visible,manual_entry)VALUES ('".$_POST["user_id"]."','".$_POST["faksinst"]."','dozent','','','','',0,0,1,0)");
        }
    }

    private function EditUserData() {
        $request = Request::getInstance();
        if ($request["standard"]=="true"){$standard="1";}else{$standard="0";}

        $del=$this->delete_gruppen($request['all_groups'], $request['user_id']);
        $ins=$this->insert_gruppen($request['Items'], $request['user_id']);

        DBManager::Get()->exec("UPDATE user_inst SET raum='".$request["raum"]."',Telefon='".$request["telefon"]."',sprechzeiten='".$request["sprechzeiten"]."',Fax='".$request["fax"]."',externdefault='".$standard."' WHERE user_id='".$request["user_id"]."' AND Institut_id='".$request["faksinst"]."'");
        DBManager::Get()->exec("UPDATE user_info SET Home='".$request["home"]."',schwerp='".$request["schwerp"]."', title_front='".$request["title_front"]."', title_rear='".$request["title_rear"]."' WHERE user_id='".$request["user_id"]."'");

    }



    /**
     * Enter description here...
     *
     */
    public function search()
    {
        $request = Request::getInstance();
        $template = $this->template_factory->open('einrichtungsverzeichnis.php');
        if ($request['action'] == 'search')
        {
            if (empty($request['vorname']) && empty($request['nachname']))
            {
                $template->set_attribute('message', MessageBox::error("Fehler. Sie haben kein Suchwort eingegeben!"));
            }
            else
            {
                $template->set_attribute('vorname', $request['vorname']);
                $template->set_attribute('nachname', $request['nachname']);

                $sql = "SELECT nachname, vorname, user_id, username FROM auth_user_md5 ";
                if (!empty($request['vorname']) && !empty($request['nachname']))
                {
                    $sql .= "WHERE vorname LIKE '%{$request['vorname']}%' AND nachname LIKE'%{$request['nachname']}%' ";
                }
                elseif (!empty($request['vorname'])&&empty($request['nachname']))
                {
                    $sql .= "WHERE vorname LIKE '%{$request['vorname']}%' ";
                }
                elseif (empty($request['vorname'])&&!empty($request['nachname']))
                {
                    $sql .= "WHERE nachname LIKE '%{$request['nachname']}%' ";
                }
                $sql .= "AND perms='dozent' ORDER BY nachname";
                $users = DBManager::Get()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                foreach ($users as $index=>$user)
                {
                    $users[$index]['zusatz'] = DBManager::Get()->query("SELECT ui.institut_id, ui.raum, ui.telefon, i.name as institut
                    FROM user_inst ui LEFT JOIN Institute i ON ui.institut_id=i.institut_id
                    WHERE ui.user_id='{$user['user_id']}'")->fetchAll(PDO::FETCH_ASSOC);
                }

                $template->set_attribute('users', $users);
                if (count($users)==0)
                {
                    $template->set_attribute('message', MessageBox::info("Es wurden keine Personen gefunden."));
                }
            }
        }
        $template->set_attribute('link', PluginEngine::getLink($this, array()));
        $template->set_layout( $GLOBALS['template_factory']->open('layouts/base_without_infobox.php') );
        echo $template->render();
    }


    private function delete_gruppen($array, $user)
    {
        $list=explode(";",$array);
        for($i=0;$i<count($list);$i++)
        {
            if (!empty($list[$i]))
            {
                DBManager::Get()->exec("DELETE FROM statusgruppe_user WHERE user_id='".$user."' AND statusgruppe_id='".$list[$i]."'");
            }
        }
        return false;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $array
     * @param unknown_type $db
     * @param unknown_type $user
     * @return unknown
     */
    private function insert_gruppen($array, $user)
    {
        $gruppen=explode(";",$array);
        for($i=0;$i<count($gruppen);$i++)
        {
            $list=explode("#",$gruppen[$i]);

            if (!empty($list[0]))
            {
                DBManager::Get()->exec("INSERT INTO statusgruppe_user (statusgruppe_id,user_id,position,visible,inherit) VALUES ('".$list[0]."','".$user."','".$list[1]."','1','1')");
            }
        }
    }

    /**
     * Gibt die Telefonlisten zurück
     * TODO: PDO und schick...
     *
     * @param string $faksinst
     * @return array()
     */
    private function getTeleData($faksinst)
    {
        if (isset($faksinst))
        {
            $isInstitut = DBManager::Get()->query("SELECT * FROM Institute WHERE fakultaets_id='".$faksinst."' AND Institut_id!='".$faksinst."' ORDER BY NAME")->fetchAll(PDO::FETCH_ASSOC);
            if ($isInstitut)
            {
                $Daten = array();
                foreach ($isInstitut as $instKey => $Institut)
                {
                    $institutsName = DBManager::Get()->query("SELECT Name FROM Institute WHERE Institut_id='".$Institut['Institut_id']."' ORDER BY NAME")->fetchAll(PDO::FETCH_COLUMN);

                    $Daten[$instKey]['name'] = "";
                    $Daten[$instKey]['name'] = $institutsName['0'];

                    $Daten[$instKey]['statusgruppe'] = array();

                    $statusgruppen = DBManager::Get()->query("SELECT * FROM statusgruppen WHERE range_id='".$Institut['Institut_id']."' ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($statusgruppen as $statKey => $Statusgruppe) {
                        $Daten[$instKey]['statusgruppe'][$statKey]['statusgruppe'] = array();
                        $Daten[$instKey]['statusgruppe'][$statKey]['statusgruppe'] = $Statusgruppe;
                        $Daten[$instKey]['statusgruppe'][$statKey]['personen'] = array();
                        $Daten[$instKey]['statusgruppe'][$statKey]['personen'] = DBManager::Get()->query("SELECT statu.user_id, aum.Nachname, aum.Vorname, aum.Email, ui.Institut_id, ui.sprechzeiten, ui.raum, ui.Telefon, ui.Fax FROM statusgruppe_user statu
                                                 JOIN auth_user_md5 aum on (aum.user_id = statu.user_id)
                                                 JOIN user_inst ui on (ui.user_id = statu.user_id AND ui.Institut_id = '".$Institut['Institut_id']."')
                                                 WHERE statusgruppe_id='".$Statusgruppe['statusgruppe_id']."' ORDER BY aum.Nachname")->fetchAll(PDO::FETCH_ASSOC);
                    }

                    $personsWithoutFunction = DBManager::Get()->query("SELECT DISTINCT statu.user_id, aum.Nachname, aum.Vorname, aum.Email, ui.Institut_id, ui.sprechzeiten, ui.raum, ui.Telefon, ui.Fax
                                                 FROM user_inst ui
                                                 JOIN statusgruppe_user statu on (statu.user_id = ui.user_id)
                                                 JOIN auth_user_md5 aum on (aum.user_id = statu.user_id)
                                                 WHERE ui.Institut_id='".$Institut['Institut_id']."' AND ui.user_id!='(SELECT user_id FROM statusgruppe_user)' ORDER BY aum.Nachname")->fetchAll(PDO::FETCH_ASSOC);

                    if ($personsWithoutFunction) {
                        $statCount = count($Daten[$instKey]['statusgruppe']);

                        $Daten[$instKey]['statusgruppe'][$statCount]['statusgruppe']['name'] = "Personen ohne Funktion im Institut ".$Daten[$instKey]['name'];
                        $Daten[$instKey]['statusgruppe'][$statCount]['personen'] = array();
                        $Daten[$instKey]['statusgruppe'][$statCount]['personen'] = $personsWithoutFunction;
                    }
                }

            } else {
                $instKey = 0;
                $Daten = array();
                $institutsName = DBManager::Get()->query("SELECT Name FROM Institute WHERE Institut_id='".$faksinst."' ORDER BY NAME")->fetchAll(PDO::FETCH_COLUMN);

                $Daten[$instKey]['name'] = "";
                $Daten[$instKey]['name'] = $institutsName['0'];

                $Daten[$instKey]['statusgruppe'] = array();

                $statusgruppen = DBManager::Get()->query("SELECT * FROM statusgruppen WHERE range_id='".$faksinst."' ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($statusgruppen as $statKey => $Statusgruppe) {
                    $Daten[$instKey]['statusgruppe'][$statKey]['statusgruppe'] = array();
                    $Daten[$instKey]['statusgruppe'][$statKey]['statusgruppe'] = $Statusgruppe;
                    $Daten[$instKey]['statusgruppe'][$statKey]['personen'] = array();
                    $Daten[$instKey]['statusgruppe'][$statKey]['personen'] = DBManager::Get()->query("SELECT statu.user_id, aum.Nachname, aum.Vorname, aum.Email, ui.Institut_id, ui.sprechzeiten, ui.raum, ui.Telefon, ui.Fax FROM statusgruppe_user statu
                                             JOIN auth_user_md5 aum on (aum.user_id = statu.user_id)
                                             JOIN user_inst ui on (ui.user_id = statu.user_id AND ui.Institut_id = '".$faksinst."')
                                             WHERE statusgruppe_id='".$Statusgruppe['statusgruppe_id']."' ORDER BY aum.Nachname")->fetchAll(PDO::FETCH_ASSOC);
                }

                $personsWithoutFunction = DBManager::Get()->query("SELECT DISTINCT statu.user_id, aum.Nachname, aum.Vorname, aum.Email, ui.Institut_id, ui.sprechzeiten, ui.raum, ui.Telefon, ui.Fax
                                             FROM user_inst ui
                                             JOIN statusgruppe_user statu on (statu.user_id = ui.user_id)
                                                 JOIN auth_user_md5 aum on (aum.user_id = statu.user_id)
                                             WHERE ui.Institut_id='".$faksinst."' AND ui.user_id!='(SELECT user_id FROM statusgruppe_user)' ORDER BY aum.Nachname")->fetchAll(PDO::FETCH_ASSOC);

                if ($personsWithoutFunction) {
                    $statCount = count($Daten[$instKey]['statusgruppe']);

                    $Daten[$instKey]['statusgruppe'][$statCount]['statusgruppe']['name'] = "Personen ohne Funktion im Institut ".$Daten[$instKey]['name'];
                    $Daten[$instKey]['statusgruppe'][$statCount]['personen'] = array();
                    $Daten[$instKey]['statusgruppe'][$statCount]['personen'] = $personsWithoutFunction;
                }
            }
            return $Daten;

        } else {
            return false;
        }
    }
}
