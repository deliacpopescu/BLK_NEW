<?php
global $user_logat;
global $user_access;

if( !empty($user_logat) ){
  /* if( isset($_GET['f_rid']) && !empty ($_GET['f_rid']) ){
       include ('submenu.php');
    }*/

?>
<table width="90%" align="center" cellspacing="1" cellpadding="1" border="0">
    <tr><td align="center">


<?php
//------------------------------------------------------------------------------
include("config/config.php");

################################################################################
## +---------------------------------------------------------------------------+
## | 1. Creating & Calling:                                                    |
## +---------------------------------------------------------------------------+
##  *** only relative (virtual) path (to the current document)
  define ("DATAGRID_DIR", "libs/datagrid420/");
  define ("PEAR_DIR", "libs/datagrid420/pear/");

  require_once(DATAGRID_DIR.'datagrid.class.php');
  require_once(PEAR_DIR.'PEAR.php');
  require_once(PEAR_DIR.'DB.php');
  
ob_start();
  $db_conn = DB::factory('mysql');
  $db_conn -> connect(DB::parseDSN('mysql://'.$db_user.':'.$db_pass.'@'.$db_host.'/'.$db_name));

##  *** put a primary key on the first place
  if(!isset($_GET["f__ff_onSUBMIT_FILTER"])){ 
        $condLimit = " WHERE status =1 AND CURDATE( ) < DATE_ADD( `data_in` , INTERVAL 30 DAY )";
      }
  else{ $condLimit = ""; }
    $sql = "SELECT cantariri.*, clienti_furnizori.nume_firma, soferi.nume AS nume_sofer, transportatori.name AS nume_transp, materiale.denumire AS nume_material, destinatie.localitate AS nume_dest, masini.nr_inmat AS nr_masina, remorca.nr_inmat AS nr_remorca, um.simbol AS um_simbol
             FROM cantariri
             LEFT OUTER JOIN clienti_furnizori ON cantariri.client = clienti_furnizori.id
             LEFT OUTER JOIN soferi ON cantariri.sofer = soferi.id
             LEFT OUTER JOIN transportatori ON cantariri.transportator = transportatori.id
             LEFT OUTER JOIN materiale ON cantariri.material = materiale.id
             LEFT OUTER JOIN destinatie ON cantariri.destinatie = destinatie.id
             LEFT OUTER JOIN masini ON cantariri.masina = masini.id
             LEFT OUTER JOIN remorca ON cantariri.remorca=remorca.id 
             LEFT OUTER JOIN um ON cantariri.um = um.id
             ".$condLimit;
     
$caption_raport = "Istoric cantariri";

// Nota->nrAviz
$avizInNota = false;
//print_r($avizInNota);
$sqlf = sprintf ( "SELECT * FROM config_avize WHERE eticheta='AvizInNota'" );
$rezf = mysql_query ( $sqlf );
if ($rezf) {
	print_r($rez);
	$rowc = mysql_fetch_assoc ( $rezf );
	$avizInNota = $rowc ['valoare']=="1"?true:false;
	//print_r($avizInNota);
}

if($avizInNota){
	$NumeNota = "Aviz";
}
else {
	$NumeNota = "Nota";
}

##  *** set needed options
  $debug_mode = false;
  $messaging = true;
  $unique_prefix = "f_";
  $dgrid = new DataGrid($debug_mode, $messaging, $unique_prefix, DATAGRID_DIR);
##  *** set data source with needed options
$default_order_field = "data_out";
$default_order_type = "ASC";

$dgrid->dataSource($db_conn, $sql, $default_order_field, $default_order_type);

## +---------------------------------------------------------------------------+
## | 2. General Settings:                                                      |
## +---------------------------------------------------------------------------+

##  *** set DataGrid caption
$dg_caption = $caption_raport;
$dgrid -> setCaption($dg_caption);
//@@@@
$dg_caption = $caption_raport 
  . "<span style='float:right; display:inline-block; margin-left:8px;'>"
  . "  <a href='your_action.php' title='JR action'>"
  . "    <img src='images/jr_16.gif' width='16' height='16' alt='JR' style='vertical-align:middle;'/>"
  . "  </a>"
  . "</span>";
$dgrid->setCaption($dg_caption);
//@@@

##  *** set CSS class for datagrid
##  *** "default" or "blue" or "gray" or "green" or your css file relative path with name
## "embedded" - use embedded classes, "file" - link external css file
$css_class = "default";
$css_type = "embedded";
$dgrid -> setCssClass($css_class, $css_type);

##  *** set DataGrid languages
$dg_language = $activ_lang;
$dgrid->setInterfaceLang($dg_language);

##  *** set direction: "ltr" or "rtr" (default - "ltr")
$direction = "ltr";
$dgrid -> setDirection($direction);

##  *** set layouts: 0 - tabular(horizontal) - default, 1 - columnar(vertical)
$layouts = array("view"=>0, "edit"=>1, "filter"=>1);
$dgrid -> setLayouts($layouts);

##  *** set modes for operations
if( intval($user_access) == 1 ){
	$type_add_edit_delete_mode = false;
}
else{
	$type_add_edit_delete_mode = false;
}
if( $type_add_edit_delete_mode ){
	$modes = array(
            "add"=>array("view"=>true, "edit"=>false, "type"=>"link"),
            "edit"=>array("view"=>true, "edit"=>true, "type"=>"link", "byFieldValue"=>""),
            "cancel"=>array("view"=>true, "edit"=>true, "type"=>"link"),
            "details"=>array("view"=>false, "edit"=>false, "type"=>"link"),
            "delete"=>array("view"=>true, "edit"=>false, "type"=>"image")
        );
}
else{
	$modes = array(
            "add"=>array("view"=>false, "edit"=>false, "type"=>"link"),
            "edit"=>array("view"=>false, "edit"=>false, "type"=>"link", "byFieldValue"=>""),
            "cancel"=>array("view"=>false, "edit"=>true, "type"=>"link"),
            "details"=>array("view"=>false, "edit"=>false, "type"=>"link"),
            "delete"=>array("view"=>false, "edit"=>false, "type"=>"image")
	);
}
$dgrid -> setModes($modes);

##  *** allow scrolling on datagrid
//$scrolling_option = true;
//$dgrid -> allowScrollingSettings($scrolling_option);

##  *** set scrolling settings (optional)
// $scrolling_width = "90%";
// $scrolling_height = "100%";
// $dgrid1->setScrollingSettings($scrolling_width, $scrolling_height);
##  *** allow mulirow operations
if( intval($user_access) == 1 ){
	$multirow_option = false;
}
else{
	$multirow_option = false;
}
$dgrid -> allowMultirowOperations($multirow_option);
$multirow_operations = array(
   "delete"  => array("view"=>true),
   "details" => array("view"=>true),
    );
$dgrid -> setMultirowOperations($multirow_operations);

## +---------------------------------------------------------------------------+
## | 3.PAGINARE:                                                    |
## +---------------------------------------------------------------------------+
##  *** set paging option: true(default) or false
 $paging_option = true;
 $rows_numeration = false;
 $numeration_sign = "Nr.";
 $dgrid -> allowPaging($paging_option, $rows_numeration, $numeration_sign);
##  *** set paging settings
 $bottom_paging = array("results"=>true, "results_align"=>"left", "pages"=>true, "pages_align"=>"center", "page_size"=>true, "page_size_align"=>"right");
 //$top_paging = array("results"=>true, "results_align"=>"left", "pages"=>true, "pages_align"=>"center", "page_size"=>true, "page_size_align"=>"right");
 $pages_array = array("10"=>"10", "25"=>"25", "30"=>"30", "50"=>"50", "100"=>"100", "1000"=>"1000", "10000"=>"10000");
 //$pages_array = array("10"=>"10", "25"=>"25", "50"=>"50", "100"=>"100", "250"=>"250", "500"=>"500", "1000"=>"1000");
 $default_page_size = 25;
 //get from link or session
 session_start();
 if(isset($_GET['f_page_size'])){
 	$default_page_size = intval($_GET['f_page_size']);
 	$_SESSION['page_size'] = $_GET['f_page_size'];
 }
 elseif(isset($_SESSION['page_size'])){
 	$default_page_size = intval($_SESSION['page_size']);
 }

 $dgrid -> setPagingSettings($bottom_paging, $top_paging, $pages_array, $default_page_size);
//--------------end paginare
## +---------------------------------------------------------------------------+
## |4.  Printing & Exporting Settings:                                         |
## +---------------------------------------------------------------------------+
##  *** set printing option: true(default) or false
 $printing_option = true;
 $dgrid->allowPrinting($printing_option);
##  *** set exporting option: true(default) or false
 $exporting_option = true;
 $exporting_directory = "";
 $dgrid->allowExporting($exporting_option, $exporting_directory );
 $exporting_types = array("excel"=>"true", "pdf"=>"false", "xml"=>"false");
 $dgrid->AllowExportingTypes($exporting_types);
 
##
#+---------------------------------------------------------------------------+
#| #Step 4. Sorting & Paging Settings.
#+---------------------------------------------------------------------------+
##  *** set sorting option: true(default) or false
//lista masini care o inregistrare in istoric
$msql = sprintf("SELECT masini.id, masini.nr_inmat FROM masini WHERE masini.id IN(SELECT DISTINCT masina FROM cantariri)");
$resm = mysql_query($msql);
if($resm){
    while($row = mysql_fetch_assoc($resm)){
        $f[$row['id']] = $row['nr_inmat'];
    }
}
//lista clienti care o inregistrare in istoric
$msql = sprintf("SELECT clienti_furnizori.id, clienti_furnizori.nume_firma FROM clienti_furnizori WHERE clienti_furnizori.id IN(SELECT DISTINCT client FROM cantariri)");
$resm = mysql_query($msql);
if($resm){
    while($row = mysql_fetch_assoc($resm)){
        $cl[$row['id']] = $row['nume_firma'];
    }
}
//lista transportatori care o inregistrare in istoric
$tsql = sprintf("SELECT transportatori.id, transportatori.name FROM transportatori WHERE transportatori.id IN(SELECT DISTINCT transportator FROM cantariri)");
$rest = mysql_query($tsql);
if($rest){
    while($row = mysql_fetch_assoc($rest)){
        $trsp[$row['id']] = $row['name'];
    }
}
//print_r($f);
//lista soferi care o inregistrare in istoric
$tsql = sprintf("SELECT soferi.id, soferi.nume FROM soferi WHERE soferi.id IN(SELECT DISTINCT sofer FROM cantariri)");
$rest = mysql_query($tsql);
if($rest){
    while($row = mysql_fetch_assoc($rest)){
        $sof[$row['id']] = $row['nume'];
    }
}
//lista materiale care o inregistrare in istoric
$tsql = sprintf("SELECT materiale.id, materiale.denumire FROM materiale WHERE materiale.id IN(SELECT DISTINCT material FROM cantariri)");
$rest = mysql_query($tsql);
if($rest){
    while($row = mysql_fetch_assoc($rest)){
        $mat[$row['id']] = $row['denumire'];
    }
}//print_r($mat);
$stat = array("1"=>"Cantariri finalizate", "0"=>"Cantariri nefinalizate");
$gout = array("0"=>"Numai intrari");
$gint = array("0"=>"Numai iesiri");
$totalizare = array("1"=>"Da", "0"=>"Nu");
$filtering_option = true;
$dgrid->allowFiltering($filtering_option);
$filtering_fields = array(
    "De la data"=>array(
        "type"=>"calendar",
        "table"=>"cantariri",
        "field"=>"data_in",
        "show_operator"=>false,
        "default_operator"=>">=",
        "case_sensitive"=>false,
        "comparison_type"=>"string",
        "width"=>"",
        "on_js_event"=>""),
    "Pana la data"=>array(
        "type"=>"calendar",
        "table"=>"cantariri",
        "field"=>"data_out",
        "show_operator"=>false,
        "default_operator"=>"<=",
        "case_sensitive"=>false,
        "comparison_type"=>"string",
        "width"=>"",
        "on_js_event"=>""),
    "Firma"=>array(
        "type"=>"dropdownlist",
        "order"=>"ASC",
        "table"=>"cantariri",
        "field"=>"client",
        "source"=>$cl,
        "show_operator"=>false,
        "default_operator"=>"=",
        "case_sensitive"=>false,
        "comparison_type"=>"string",
        "width"=>"",
        "on_js_event"=>"",
        "show"=>"",
        "condition"=>""),
    "Masina"=>array(
        "type"=>"dropdownlist",
        "order"=>"ASC",
        "table"=>"cantariri",
        "field"=>"masina",
        "source"=>$f,
        "show_operator"=>false,
        "default_operator"=>"=",
        "case_sensitive"=>false,
        "comparison_type"=>"string",
        "width"=>"",
        "on_js_event"=>"",
        "show"=>"",
        "condition"=>""),
    "Sofer"=>array(
        "type"=>"dropdownlist",
        "order"=>"ASC",
        "table"=>"cantariri",
        "field"=>"sofer",
        "source"=>$sof,
        "show_operator"=>false,
        "default_operator"=>"=",
        "case_sensitive"=>false,
        "comparison_type"=>"string",
        "width"=>"",
        "on_js_event"=>"",
        "show"=>"",
        "condition"=>""),
    "Transportator"=>array(
        "type"=>"dropdownlist",
        "order"=>"ASC",
        "table"=>"cantariri",
        "field"=>"transportator",
        "source"=>$trsp,
        "show_operator"=>false,
        "default_operator"=>"=",
        "case_sensitive"=>false,
        "comparison_type"=>"string",
        "width"=>"",
        "on_js_event"=>"",
        "show"=>"",
        "condition"=>""),
    "Material"=>array(
        "type"=>"dropdownlist",
        "order"=>"ASC",
        "table"=>"cantariri",
        "field"=>"material",
        "source"=>$mat,
        "show_operator"=>false,
        "default_operator"=>"=",
        "case_sensitive"=>false,
        "comparison_type"=>"string",
        "width"=>"",
        "on_js_event"=>"",
        "show"=>"",
        "condition"=>""),
    "Numai intrari"=>array(
        "type"=>"dropdownlist",
        "order"=>"ASC",
        "table"=>"cantariri",
        "field"=>"greutate_out",
        "source"=>$gout,
        "show_operator"=>false,
        "default_operator"=>"=",
        "case_sensitive"=>false,
        "comparison_type"=>"string",
        "width"=>"",
        "on_js_event"=>"",
        "show"=>"",
        "condition"=>""),
    "Numai iesiri"=>array(
        "type"=>"dropdownlist",
        "order"=>"ASC",
        "table"=>"cantariri",
        "field"=>"greutate_in",
        "source"=>$gint,
        "show_operator"=>false,
        "default_operator"=>"=",
        "case_sensitive"=>false,
        "comparison_type"=>"string",
        "width"=>"",
        "on_js_event"=>"",
        "show"=>"",
        "condition"=>""),
    "Status"=>array(
        "type"=>"dropdownlist",
        "order"=>"ASC",
        "table"=>"cantariri",
        "field"=>"status",
        "source"=>$stat,
        "show_operator"=>false,
        "default_operator"=>"=",
        "case_sensitive"=>false,
        "comparison_type"=>"string",
        "width"=>"",
        "on_js_event"=>"",
        "show"=>"",
        "condition"=>""),
/*"Totalizare"=>array(
        "type"=>"dropdownlist",
        "order"=>"",
        "table"=>"",
        "field"=>"",
        "source"=>$totalizare,
        "show_operator"=>false,
        "default_operator"=>"=",
        "case_sensitive"=>false,
        "comparison_type"=>"string",
        "width"=>"",
        "on_js_event"=>"",
        "show"=>"",
        "condition"=>""),*/
       );
     
$dgrid->SetFieldsFiltering($filtering_fields);



## +---------------------------------------------------------------------------+
## | 5. View Mode Settings:                                                    |
## +---------------------------------------------------------------------------+
##  *** set columns in view mode
$vm_colimns = array(
    "nr_cantarire" => array("header"=>"Nr. cantarire", "type"=>"link", "width"=>"2%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "data_in" => array("header"=>"Data intrare", "type"=>"link", "width"=>"10%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "ora_in" => array("header"=>"Ora intrare", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "data_out" => array("header"=>"Data iesire", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "ora_out" => array("header"=>"Ora iesire", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "greutate_in" => array("header"=>"Greutatea intrare", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "greutate_out" => array("header"=>"Greutatea iesire", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "tara" => array("header"=>"Tara", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "greutate_neta" => array("header"=>"Greutatea neta", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal", "summarize"=>"true"),
	"um_simbol" => array("header"=>"UM", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "tip_operatie" => array("header"=>"Operatie", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "nume_firma" => array("header"=>"Client", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "nume_sofer" => array("header"=>"Sofer", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "nume_transp" => array("header"=>"Transportator", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "nume_material" => array("header"=>"Material", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "nume_dest" => array("header"=>"Destinatie", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "nr_masina" => array("header"=>"Masina", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "nr_remorca" => array("header"=>"Remorca", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "nr_axe" => array("header"=>"Nr. axe", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "pers_contact" => array("header"=>"Pers. contact", "type"=>"link", "width"=>"30%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "nota" => array("header"=>$NumeNota, "type"=>"link", "width"=>"90%", "align"=>"left",   "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
                    );
//$vm_colimns = $vm_colimns_spec;
$dgrid->setColumnsInViewMode($vm_colimns);
 //$dgrid->setAutoColumnsInViewMode(true);

## +---------------------------------------------------------------------------+
## | 6. Add/Edit/Details Mode settings:                                        |
## +---------------------------------------------------------------------------+
##  ***  set settings for edit/details mode
  $table_name = "cantariri";
  $primary_key = "id";
  $condition = "";
  $dgrid->setTableEdit($table_name, $primary_key, $condition);
  /*$em_columns = array(
    "nr_cantarire"  =>array("header"=>"Nr. cantarire", "type"=>"textbox",  "width"=>"210px", "req_type"=>"rt", "title"=>"Codul materialului", "unique"=>true),
    "data_in" =>array("header"=>"Data", "type"=>"textbox",  "width"=>"210px", "req_type"=>"rt", "title"=>"Denumire material"),
    "greutate_in" =>array("header"=>"G. intrare", "type"=>"textbox",  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "greutate_out" =>array("header"=>"G. iesire", "type"=>"textbox",  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "tara" =>array("header"=>"Tara", "type"=>"textbox",  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "greutate_neta" =>array("header"=>"G. Iesire", "type"=>"textbox",  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "nume_firma" =>array("header"=>"Cod postal", "type"=>"textbox",  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "sediul" =>array("header"=>"Sediul", "type"=>"textbox",  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "cui" =>array("header"=>"CUI", "type"=>"textbox",  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "orc" =>array("header"=>"ORC", "type"=>"textbox",  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "client" =>array("header"=>"Client", "type"=>"checkbox", "true_value"=>1, "false_value"=>0,  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "furnizor" =>array("header"=>"Furnizor", "type"=>"checkbox", "true_value"=>1, "false_value"=>0,  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "telefon" =>array("header"=>"Telefon", "type"=>"textbox",  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "fax" =>array("header"=>"Fax", "type"=>"textbox",  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "pers_contact" =>array("header"=>"Pers. Contact", "type"=>"textbox", "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "email" =>array("header"=>"Email", "type"=>"textbox",  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "web" =>array("header"=>"Web", "type"=>"textbox",  "width"=>"210px", "req_type"=>"st", "title"=>"Denumire material"),
    "nota"     =>array("header"=>"Nota", "type"=>"textarea",  "width"=>"310px", "req_type"=>"st", "title"=>"Descriere material", "edit_type"=>"wysiwyg", "rows"=>"7", "cols"=>"50"),
				); //contine campurile editabile
   $dgrid->setColumnsInEditMode($em_columns);*/
  //$dgrid->setAutoColumnsInEditMode(true);
   


## +---------------------------------------------------------------------------+
## | 7. Bind the DataGrid:                                                     |
## +---------------------------------------------------------------------------+
##  *** set debug mode & messaging options
    $dgrid->bind();
    ob_end_flush();
################################################################################
//------------------------------------------------------------------------------
/*if( isset($_GET['f__ff__']) && $_GET['f__ff__']==1 ){
include ('libs/totalizari.class.php');

//echo 'aici totalizari... '.$_GET['f__ff_cantariri_client'];
//$total = new Total($data_in="", $data_out="", $client="", $masina="", $sofer="", $transportator="", $material="", $numai_intrari="", $numai_iesiri="", $status="");
$total = new Total($_GET['f__ff_cantariri_data_in'], $_GET['f__ff_cantariri_data_out'], $_GET['f__ff_cantariri_client'], $_GET['f__ff_cantariri_masina'], $_GET['f__ff_cantariri_sofer'], $_GET['f__ff_cantariri_transportator'], $_GET['f__ff_cantariri_material'], $_GET['f__ff_cantariri_greutate_in'], $_GET['f__ff_cantariri_greutate_out'], $_GET['f__ff_cantariri_status'], $_GET["f__ff_onSUBMIT_FILTER"]);
//print_r($total->afisareTotal());
$total->afisareTotal();
}*/
?>
</td></tr></table>
<?php
} //end if check user logat
else{
    //include("config/config.php");
    //include("libs/lang_".$activ_lang.".php");
    echo '<div class="div_access_denied">Access denied! - TREBUIE SA FITI LOGAT</div>';
}

?>
