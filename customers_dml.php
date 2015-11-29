<?php

// Data functions (insert, update, delete, form) for table customers

// This script and data application were generated by AppGini 5.42
// Download AppGini for free from http://bigprof.com/appgini/download/

function customers_insert(){
	global $Translation;

	if($_GET['insert_x']!=''){$_POST=$_GET;}

	// mm: can member insert record?
	$arrPerm=getTablePermissions('customers');
	if(!$arrPerm[1]){
		return false;
	}

	$data['CustomerID'] = makeSafe($_POST['CustomerID']);
		if($data['CustomerID'] == empty_lookup_value){ $data['CustomerID'] = ''; }
	$data['CompanyName'] = makeSafe($_POST['CompanyName']);
		if($data['CompanyName'] == empty_lookup_value){ $data['CompanyName'] = ''; }
	$data['ContactName'] = makeSafe($_POST['ContactName']);
		if($data['ContactName'] == empty_lookup_value){ $data['ContactName'] = ''; }
	$data['ContactTitle'] = makeSafe($_POST['ContactTitle']);
		if($data['ContactTitle'] == empty_lookup_value){ $data['ContactTitle'] = ''; }
	$data['Address'] = br2nl(makeSafe($_POST['Address']));
	$data['City'] = makeSafe($_POST['City']);
		if($data['City'] == empty_lookup_value){ $data['City'] = ''; }
	$data['Region'] = makeSafe($_POST['Region']);
		if($data['Region'] == empty_lookup_value){ $data['Region'] = ''; }
	$data['PostalCode'] = makeSafe($_POST['PostalCode']);
		if($data['PostalCode'] == empty_lookup_value){ $data['PostalCode'] = ''; }
	$data['Country'] = makeSafe($_POST['Country']);
		if($data['Country'] == empty_lookup_value){ $data['Country'] = ''; }
	$data['Phone'] = makeSafe($_POST['Phone']);
		if($data['Phone'] == empty_lookup_value){ $data['Phone'] = ''; }
	$data['Fax'] = makeSafe($_POST['Fax']);
		if($data['Fax'] == empty_lookup_value){ $data['Fax'] = ''; }
	if($data['CustomerID'] == '') {echo StyleSheet() . "\n\n<div class=\"alert alert-danger\">" . $Translation['error:'] . " 'Customer ID': " . $Translation['pkfield empty'] . '</div>'; exit;}


	// hook: customers_before_insert
	if(function_exists('customers_before_insert')){
		$args=array();
		if(!customers_before_insert($data, getMemberInfo(), $args)){ return false; }
	}

	$o=array('silentErrors' => true);
	sql('insert into `customers` set       `CustomerID`=' . (($data['CustomerID'] !== '' && $data['CustomerID'] !== NULL) ? "'{$data['CustomerID']}'" : 'NULL') . ', `CompanyName`=' . (($data['CompanyName'] !== '' && $data['CompanyName'] !== NULL) ? "'{$data['CompanyName']}'" : 'NULL') . ', `ContactName`=' . (($data['ContactName'] !== '' && $data['ContactName'] !== NULL) ? "'{$data['ContactName']}'" : 'NULL') . ', `ContactTitle`=' . (($data['ContactTitle'] !== '' && $data['ContactTitle'] !== NULL) ? "'{$data['ContactTitle']}'" : 'NULL') . ', `Address`=' . (($data['Address'] !== '' && $data['Address'] !== NULL) ? "'{$data['Address']}'" : 'NULL') . ', `City`=' . (($data['City'] !== '' && $data['City'] !== NULL) ? "'{$data['City']}'" : 'NULL') . ', `Region`=' . (($data['Region'] !== '' && $data['Region'] !== NULL) ? "'{$data['Region']}'" : 'NULL') . ', `PostalCode`=' . (($data['PostalCode'] !== '' && $data['PostalCode'] !== NULL) ? "'{$data['PostalCode']}'" : 'NULL') . ', `Country`=' . (($data['Country'] !== '' && $data['Country'] !== NULL) ? "'{$data['Country']}'" : 'NULL') . ', `Phone`=' . (($data['Phone'] !== '' && $data['Phone'] !== NULL) ? "'{$data['Phone']}'" : 'NULL') . ', `Fax`=' . (($data['Fax'] !== '' && $data['Fax'] !== NULL) ? "'{$data['Fax']}'" : 'NULL'), $o);
	if($o['error']!=''){
		echo $o['error'];
		echo "<a href=\"customers_view.php?addNew_x=1\">{$Translation['< back']}</a>";
		exit;
	}

	$recID=$data['CustomerID'];

	// hook: customers_after_insert
	if(function_exists('customers_after_insert')){
		$res = sql("select * from `customers` where `CustomerID`='" . makeSafe($recID) . "' limit 1", $eo);
		if($row = db_fetch_assoc($res)){
			$data = array_map('makeSafe', $row);
		}
		$data['selectedID'] = makeSafe($recID);
		$args=array();
		if(!customers_after_insert($data, getMemberInfo(), $args)){ return (get_magic_quotes_gpc() ? stripslashes($recID) : $recID); }
	}

	// mm: save ownership data
	sql("insert into membership_userrecords set tableName='customers', pkValue='$recID', memberID='".getLoggedMemberID()."', dateAdded='".time()."', dateUpdated='".time()."', groupID='".getLoggedGroupID()."'", $eo);

	return (get_magic_quotes_gpc() ? stripslashes($recID) : $recID);
}

function customers_delete($selected_id, $AllowDeleteOfParents=false, $skipChecks=false){
	// insure referential integrity ...
	global $Translation;
	$selected_id=makeSafe($selected_id);

	// mm: can member delete record?
	$arrPerm=getTablePermissions('customers');
	$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='customers' and pkValue='$selected_id'");
	$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='customers' and pkValue='$selected_id'");
	if(($arrPerm[4]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[4]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[4]==3){ // allow delete?
		// delete allowed, so continue ...
	}else{
		return $Translation['You don\'t have enough permissions to delete this record'];
	}

	// hook: customers_before_delete
	if(function_exists('customers_before_delete')){
		$args=array();
		if(!customers_before_delete($selected_id, $skipChecks, getMemberInfo(), $args))
			return $Translation['Couldn\'t delete this record'];
	}

	// child table: orders
	$res = sql("select `CustomerID` from `customers` where `CustomerID`='$selected_id'", $eo);
	$CustomerID = db_fetch_row($res);
	$rires = sql("select count(1) from `orders` where `CustomerID`='".addslashes($CustomerID[0])."'", $eo);
	$rirow = db_fetch_row($rires);
	if($rirow[0] && !$AllowDeleteOfParents && !$skipChecks){
		$RetMsg = $Translation["couldn't delete"];
		$RetMsg = str_replace("<RelatedRecords>", $rirow[0], $RetMsg);
		$RetMsg = str_replace("<TableName>", "orders", $RetMsg);
		return $RetMsg;
	}elseif($rirow[0] && $AllowDeleteOfParents && !$skipChecks){
		$RetMsg = $Translation["confirm delete"];
		$RetMsg = str_replace("<RelatedRecords>", $rirow[0], $RetMsg);
		$RetMsg = str_replace("<TableName>", "orders", $RetMsg);
		$RetMsg = str_replace("<Delete>", "<input type=\"button\" class=\"button\" value=\"".$Translation['yes']."\" onClick=\"window.location='customers_view.php?SelectedID=".urlencode($selected_id)."&delete_x=1&confirmed=1';\">", $RetMsg);
		$RetMsg = str_replace("<Cancel>", "<input type=\"button\" class=\"button\" value=\"".$Translation['no']."\" onClick=\"window.location='customers_view.php?SelectedID=".urlencode($selected_id)."';\">", $RetMsg);
		return $RetMsg;
	}

	sql("delete from `customers` where `CustomerID`='$selected_id'", $eo);

	// hook: customers_after_delete
	if(function_exists('customers_after_delete')){
		$args=array();
		customers_after_delete($selected_id, getMemberInfo(), $args);
	}

	// mm: delete ownership data
	sql("delete from membership_userrecords where tableName='customers' and pkValue='$selected_id'", $eo);
}

function customers_update($selected_id){
	global $Translation;

	if($_GET['update_x']!=''){$_POST=$_GET;}

	// mm: can member edit record?
	$arrPerm=getTablePermissions('customers');
	$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='customers' and pkValue='".makeSafe($selected_id)."'");
	$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='customers' and pkValue='".makeSafe($selected_id)."'");
	if(($arrPerm[3]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[3]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[3]==3){ // allow update?
		// update allowed, so continue ...
	}else{
		return false;
	}

	$data['CustomerID'] = makeSafe($_POST['CustomerID']);
		if($data['CustomerID'] == empty_lookup_value){ $data['CustomerID'] = ''; }
	$data['CompanyName'] = makeSafe($_POST['CompanyName']);
		if($data['CompanyName'] == empty_lookup_value){ $data['CompanyName'] = ''; }
	$data['ContactName'] = makeSafe($_POST['ContactName']);
		if($data['ContactName'] == empty_lookup_value){ $data['ContactName'] = ''; }
	$data['ContactTitle'] = makeSafe($_POST['ContactTitle']);
		if($data['ContactTitle'] == empty_lookup_value){ $data['ContactTitle'] = ''; }
	$data['Address'] = br2nl(makeSafe($_POST['Address']));
	$data['City'] = makeSafe($_POST['City']);
		if($data['City'] == empty_lookup_value){ $data['City'] = ''; }
	$data['Region'] = makeSafe($_POST['Region']);
		if($data['Region'] == empty_lookup_value){ $data['Region'] = ''; }
	$data['PostalCode'] = makeSafe($_POST['PostalCode']);
		if($data['PostalCode'] == empty_lookup_value){ $data['PostalCode'] = ''; }
	$data['Country'] = makeSafe($_POST['Country']);
		if($data['Country'] == empty_lookup_value){ $data['Country'] = ''; }
	$data['Phone'] = makeSafe($_POST['Phone']);
		if($data['Phone'] == empty_lookup_value){ $data['Phone'] = ''; }
	$data['Fax'] = makeSafe($_POST['Fax']);
		if($data['Fax'] == empty_lookup_value){ $data['Fax'] = ''; }
	$data['selectedID']=makeSafe($selected_id);

	// hook: customers_before_update
	if(function_exists('customers_before_update')){
		$args=array();
		if(!customers_before_update($data, getMemberInfo(), $args)){ return false; }
	}

	$o=array('silentErrors' => true);
	sql('update `customers` set       `CustomerID`=' . (($data['CustomerID'] !== '' && $data['CustomerID'] !== NULL) ? "'{$data['CustomerID']}'" : 'NULL') . ', `CompanyName`=' . (($data['CompanyName'] !== '' && $data['CompanyName'] !== NULL) ? "'{$data['CompanyName']}'" : 'NULL') . ', `ContactName`=' . (($data['ContactName'] !== '' && $data['ContactName'] !== NULL) ? "'{$data['ContactName']}'" : 'NULL') . ', `ContactTitle`=' . (($data['ContactTitle'] !== '' && $data['ContactTitle'] !== NULL) ? "'{$data['ContactTitle']}'" : 'NULL') . ', `Address`=' . (($data['Address'] !== '' && $data['Address'] !== NULL) ? "'{$data['Address']}'" : 'NULL') . ', `City`=' . (($data['City'] !== '' && $data['City'] !== NULL) ? "'{$data['City']}'" : 'NULL') . ', `Region`=' . (($data['Region'] !== '' && $data['Region'] !== NULL) ? "'{$data['Region']}'" : 'NULL') . ', `PostalCode`=' . (($data['PostalCode'] !== '' && $data['PostalCode'] !== NULL) ? "'{$data['PostalCode']}'" : 'NULL') . ', `Country`=' . (($data['Country'] !== '' && $data['Country'] !== NULL) ? "'{$data['Country']}'" : 'NULL') . ', `Phone`=' . (($data['Phone'] !== '' && $data['Phone'] !== NULL) ? "'{$data['Phone']}'" : 'NULL') . ', `Fax`=' . (($data['Fax'] !== '' && $data['Fax'] !== NULL) ? "'{$data['Fax']}'" : 'NULL') . " where `CustomerID`='".makeSafe($selected_id)."'", $o);
	if($o['error']!=''){
		echo $o['error'];
		echo '<a href="customers_view.php?SelectedID='.urlencode($selected_id)."\">{$Translation['< back']}</a>";
		exit;
	}

	$data['selectedID'] = $data['CustomerID'];

	// hook: customers_after_update
	if(function_exists('customers_after_update')){
		$res = sql("SELECT * FROM `customers` WHERE `CustomerID`='{$data['selectedID']}' LIMIT 1", $eo);
		if($row = db_fetch_assoc($res)){
			$data = array_map('makeSafe', $row);
		}
		$data['selectedID'] = $data['CustomerID'];
		$args = array();
		if(!customers_after_update($data, getMemberInfo(), $args)){ return; }
	}

	// mm: update ownership data
	sql("update membership_userrecords set dateUpdated='".time()."', pkValue='{$data['CustomerID']}' where tableName='customers' and pkValue='".makeSafe($selected_id)."'", $eo);

}

function customers_form($selected_id = '', $AllowUpdate = 1, $AllowInsert = 1, $AllowDelete = 1, $ShowCancel = 0){
	// function to return an editable form for a table records
	// and fill it with data of record whose ID is $selected_id. If $selected_id
	// is empty, an empty form is shown, with only an 'Add New'
	// button displayed.

	global $Translation;

	// mm: get table permissions
	$arrPerm=getTablePermissions('customers');
	if(!$arrPerm[1] && $selected_id==''){ return ''; }
	$AllowInsert = ($arrPerm[1] ? true : false);
	// print preview?
	$dvprint = false;
	if($selected_id && $_REQUEST['dvprint_x'] != ''){
		$dvprint = true;
	}


	// populate filterers, starting from children to grand-parents

	// unique random identifier
	$rnd1 = ($dvprint ? rand(1000000, 9999999) : '');
	// combobox: Country
	$combo_Country = new Combo;
	$combo_Country->ListType = 0;
	$combo_Country->MultipleSeparator = ', ';
	$combo_Country->ListBoxHeight = 10;
	$combo_Country->RadiosPerLine = 1;
	if(is_file(dirname(__FILE__).'/hooks/customers.Country.csv')){
		$Country_data = addslashes(implode('', @file(dirname(__FILE__).'/hooks/customers.Country.csv')));
		$combo_Country->ListItem = explode('||', entitiesToUTF8(convertLegacyOptions($Country_data)));
		$combo_Country->ListData = $combo_Country->ListItem;
	}else{
		$combo_Country->ListItem = explode('||', entitiesToUTF8(convertLegacyOptions("Afghanistan;;Albania;;Algeria;;American Samoa;;Andorra;;Angola;;Anguilla;;Antarctica;;Antigua, Barbuda;;Argentina;;Armenia;;Aruba;;Australia;;Austria;;Azerbaijan;;Bahamas;;Bahrain;;Bangladesh;;Barbados;;Belarus;;Belgium;;Belize;;Benin;;Bermuda;;Bhutan;;Bolivia;;Bosnia, Herzegovina;;Botswana;;Bouvet Is.;;Brazil;;Brunei Darussalam;;Bulgaria;;Burkina Faso;;Burundi;;Cambodia;;Cameroon;;Canada;;Canary Is.;;Cape Verde;;Cayman Is.;;Central African Rep.;;Chad;;Channel Islands;;Chile;;China;;Christmas Is.;;Cocos Is.;;Colombia;;Comoros;;Congo, D.R. Of;;Congo;;Cook Is.;;Costa Rica;;Croatia;;Cuba;;Cyprus;;Czech Republic;;Denmark;;Djibouti;;Dominica;;Dominican Republic;;Ecuador;;Egypt;;El Salvador;;Equatorial Guinea;;Eritrea;;Estonia;;Ethiopia;;Falkland Is.;;Faroe Is.;;Fiji;;Finland;;France;;French Guiana;;French Polynesia;;French Territories;;Gabon;;Gambia;;Georgia;;Germany;;Ghana;;Gibraltar;;Greece;;Greenland;;Grenada;;Guadeloupe;;Guam;;Guatemala;;Guernsey;;Guinea-bissau;;Guinea;;Guyana;;Haiti;;Heard, Mcdonald Is.;;Honduras;;Hong Kong;;Hungary;;Iceland;;India;;Indonesia;;Iran;;Iraq;;Ireland;;Israel;;Italy;;Ivory Coast;;Jamaica;;Japan;;Jersey;;Jordan;;Kazakhstan;;Kenya;;Kiribati;;Korea, D.P.R Of;;Korea, Rep. Of;;Kuwait;;Kyrgyzstan;;Lao Peoples D.R.;;Latvia;;Lebanon;;Lesotho;;Liberia;;Libyan Arab Jamahiriya;;Liechtenstein;;Lithuania;;Luxembourg;;Macao;;Macedonia, F.Y.R Of;;Madagascar;;Malawi;;Malaysia;;Maldives;;Mali;;Malta;;Mariana Islands;;Marshall Islands;;Martinique;;Mauritania;;Mauritius;;Mayotte;;Mexico;;Micronesia;;Moldova;;Monaco;;Mongolia;;Montserrat;;Morocco;;Mozambique;;Myanmar;;Namibia;;Nauru;;Nepal;;Netherlands Antilles;;Netherlands;;New Caledonia;;New Zealand;;Nicaragua;;Niger;;Nigeria;;Niue;;Norfolk Island;;Norway;;Oman;;Pakistan;;Palau;;Palestinian Terr.;;Panama;;Papua New Guinea;;Paraguay;;Peru;;Philippines;;Pitcairn;;Poland;;Portugal;;Puerto Rico;;Qatar;;Reunion;;Romania;;Russian Federation;;Rwanda;;Samoa;;San Marino;;Sao Tome, Principe;;Saudi Arabia;;Senegal;;Seychelles;;Sierra Leone;;Singapore;;Slovakia;;Slovenia;;Solomon Is.;;Somalia;;South Africa;;South Georgia;;South Sandwich Is.;;Spain;;Sri Lanka;;St. Helena;;St. Kitts, Nevis;;St. Lucia;;St. Pierre, Miquelon;;St. Vincent, Grenadines;;Sudan;;Suriname;;Svalbard, Jan Mayen;;Swaziland;;Sweden;;Switzerland;;Syrian Arab Republic;;Taiwan;;Tajikistan;;Tanzania;;Thailand;;Timor-leste;;Togo;;Tokelau;;Tonga;;Trinidad, Tobago;;Tunisia;;Turkey;;Turkmenistan;;Turks, Caicoss;;Tuvalu;;Uganda;;Ukraine;;United Arab Emirates;;United Kingdom;;United States;;Uruguay;;Uzbekistan;;Vanuatu;;Vatican City;;Venezuela;;Viet Nam;;Virgin Is. British;;Virgin Is. U.S.;;Wallis, Futuna;;Western Sahara;;Yemen;;Yugoslavia;;Zambia;;Zimbabwe")));
		$combo_Country->ListData = $combo_Country->ListItem;
	}
	$combo_Country->SelectName = 'Country';

	if($selected_id){
		// mm: check member permissions
		if(!$arrPerm[2]){
			return "";
		}
		// mm: who is the owner?
		$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='customers' and pkValue='".makeSafe($selected_id)."'");
		$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='customers' and pkValue='".makeSafe($selected_id)."'");
		if($arrPerm[2]==1 && getLoggedMemberID()!=$ownerMemberID){
			return "";
		}
		if($arrPerm[2]==2 && getLoggedGroupID()!=$ownerGroupID){
			return "";
		}

		// can edit?
		if(($arrPerm[3]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[3]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[3]==3){
			$AllowUpdate=1;
		}else{
			$AllowUpdate=0;
		}

		$res = sql("select * from `customers` where `CustomerID`='".makeSafe($selected_id)."'", $eo);
		if(!($row = db_fetch_array($res))){
			return error_message($Translation['No records found']);
		}
		$urow = $row; /* unsanitized data */
		$hc = new CI_Input();
		$row = $hc->xss_clean($row); /* sanitize data */
		$combo_Country->SelectedData = $row['Country'];
	}else{
		$combo_Country->SelectedText = ( $_REQUEST['FilterField'][1]=='9' && $_REQUEST['FilterOperator'][1]=='<=>' ? (get_magic_quotes_gpc() ? stripslashes($_REQUEST['FilterValue'][1]) : $_REQUEST['FilterValue'][1]) : "");
	}
	$combo_Country->Render();

	// code for template based detail view forms

	// open the detail view template
	if($dvprint){
		$templateCode = @file_get_contents('./templates/customers_templateDVP.html');
	}else{
		$templateCode = @file_get_contents('./templates/customers_templateDV.html');
	}

	// process form title
	$templateCode = str_replace('<%%DETAIL_VIEW_TITLE%%>', 'Detail View', $templateCode);
	$templateCode = str_replace('<%%RND1%%>', $rnd1, $templateCode);
	$templateCode = str_replace('<%%EMBEDDED%%>', ($_REQUEST['Embedded'] ? 'Embedded=1' : ''), $templateCode);
	// process buttons
	if($arrPerm[1] && !$selected_id){ // allow insert and no record selected?
		if(!$selected_id) $templateCode=str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-success" id="insert" name="insert_x" value="1" onclick="return customers_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save New'] . '</button>', $templateCode);
		$templateCode=str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="insert" name="insert_x" value="1" onclick="return customers_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save As Copy'] . '</button>', $templateCode);
	}else{
		$templateCode=str_replace('<%%INSERT_BUTTON%%>', '', $templateCode);
	}

	// 'Back' button action
	if($_REQUEST['Embedded']){
		$backAction = 'window.parent.jQuery(\'.modal\').modal(\'hide\'); return false;';
	}else{
		$backAction = '$$(\'form\')[0].writeAttribute(\'novalidate\', \'novalidate\'); document.myform.reset(); return true;';
	}

	if($selected_id){
		if(!$_REQUEST['Embedded']) $templateCode=str_replace('<%%DVPRINT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="dvprint" name="dvprint_x" value="1" onclick="$$(\'form\')[0].writeAttribute(\'novalidate\', \'novalidate\'); document.myform.reset(); return true;"><i class="glyphicon glyphicon-print"></i> ' . $Translation['Print Preview'] . '</button>', $templateCode);
		if($AllowUpdate){
			$templateCode=str_replace('<%%UPDATE_BUTTON%%>', '<button type="submit" class="btn btn-success btn-lg" id="update" name="update_x" value="1" onclick="return customers_validateData();"><i class="glyphicon glyphicon-ok"></i> ' . $Translation['Save Changes'] . '</button>', $templateCode);
		}else{
			$templateCode=str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		}
		if(($arrPerm[4]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[4]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[4]==3){ // allow delete?
			$templateCode=str_replace('<%%DELETE_BUTTON%%>', '<button type="submit" class="btn btn-danger" id="delete" name="delete_x" value="1" onclick="return confirm(\'' . $Translation['are you sure?'] . '\');"><i class="glyphicon glyphicon-trash"></i> ' . $Translation['Delete'] . '</button>', $templateCode);
		}else{
			$templateCode=str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		}
		$templateCode=str_replace('<%%DESELECT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>', $templateCode);
	}else{
		$templateCode=str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		$templateCode=str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		$templateCode=str_replace('<%%DESELECT_BUTTON%%>', ($ShowCancel ? '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>' : ''), $templateCode);
	}

	// set records to read only if user can't insert new records and can't edit current record
	if(($selected_id && !$AllowUpdate) || (!$selected_id && !$AllowInsert)){
		$jsReadOnly .= "\tjQuery('#CustomerID').replaceWith('<div class=\"form-control-static\" id=\"CustomerID\">' + (jQuery('#CustomerID').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#CompanyName').replaceWith('<div class=\"form-control-static\" id=\"CompanyName\">' + (jQuery('#CompanyName').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#ContactName').replaceWith('<div class=\"form-control-static\" id=\"ContactName\">' + (jQuery('#ContactName').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#ContactTitle').replaceWith('<div class=\"form-control-static\" id=\"ContactTitle\">' + (jQuery('#ContactTitle').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#Address').replaceWith('<div class=\"form-control-static\" id=\"Address\">' + (jQuery('#Address').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#City').replaceWith('<div class=\"form-control-static\" id=\"City\">' + (jQuery('#City').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#Region').replaceWith('<div class=\"form-control-static\" id=\"Region\">' + (jQuery('#Region').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#PostalCode').replaceWith('<div class=\"form-control-static\" id=\"PostalCode\">' + (jQuery('#PostalCode').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#Country').replaceWith('<div class=\"form-control-static\" id=\"Country\">' + (jQuery('#Country').val() || '') + '</div>'); jQuery('#Country-multi-selection-help').hide();\n";
		$jsReadOnly .= "\tjQuery('#Phone').replaceWith('<div class=\"form-control-static\" id=\"Phone\">' + (jQuery('#Phone').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#Fax').replaceWith('<div class=\"form-control-static\" id=\"Fax\">' + (jQuery('#Fax').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('.select2-container').hide();\n";

		$noUploads = true;
	}elseif(($AllowInsert && !$selected_id) || ($AllowUpdate && $selected_id)){
		$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', true);"; // temporarily disable form change handler
			$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', false);"; // re-enable form change handler
	}

	// process combos
	$templateCode=str_replace('<%%COMBO(Country)%%>', $combo_Country->HTML, $templateCode);
	$templateCode=str_replace('<%%COMBOTEXT(Country)%%>', $combo_Country->SelectedData, $templateCode);

	/* lookup fields array: 'lookup field name' => array('parent table name', 'lookup field caption') */
	$lookup_fields = array();
	foreach($lookup_fields as $luf => $ptfc){
		$pt_perm = getTablePermissions($ptfc[0]);

		// process foreign key links
		if($pt_perm['view'] || $pt_perm['edit']){
			$templateCode = str_replace("<%%PLINK({$luf})%%>", '<button type="button" class="btn btn-default view_parent hspacer-lg" id="' . $ptfc[0] . '_view_parent" title="' . htmlspecialchars($Translation['View'] . ' ' . $ptfc[1], ENT_QUOTES, 'iso-8859-1') . '"><i class="glyphicon glyphicon-eye-open"></i></button>', $templateCode);
		}

		// if user has insert permission to parent table of a lookup field, put an add new button
		if($pt_perm['insert'] && !$_REQUEST['Embedded']){
			$templateCode = str_replace("<%%ADDNEW({$ptfc[0]})%%>", '<button type="button" class="btn btn-success add_new_parent" id="' . $ptfc[0] . '_add_new" title="' . htmlspecialchars($Translation['Add New'] . ' ' . $ptfc[1], ENT_QUOTES, 'iso-8859-1') . '"><i class="glyphicon glyphicon-plus-sign"></i></button>', $templateCode);
		}
	}

	// process images
	$templateCode=str_replace('<%%UPLOADFILE(CustomerID)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(CompanyName)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(ContactName)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(ContactTitle)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(Address)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(City)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(Region)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(PostalCode)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(Country)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(Phone)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(Fax)%%>', '', $templateCode);

	// process values
	if($selected_id){
		$templateCode=str_replace('<%%VALUE(CustomerID)%%>', htmlspecialchars($row['CustomerID'], ENT_QUOTES, 'iso-8859-1'), $templateCode);
		$templateCode=str_replace('<%%URLVALUE(CustomerID)%%>', urlencode($urow['CustomerID']), $templateCode);
		$templateCode=str_replace('<%%VALUE(CompanyName)%%>', htmlspecialchars($row['CompanyName'], ENT_QUOTES, 'iso-8859-1'), $templateCode);
		$templateCode=str_replace('<%%URLVALUE(CompanyName)%%>', urlencode($urow['CompanyName']), $templateCode);
		$templateCode=str_replace('<%%VALUE(ContactName)%%>', htmlspecialchars($row['ContactName'], ENT_QUOTES, 'iso-8859-1'), $templateCode);
		$templateCode=str_replace('<%%URLVALUE(ContactName)%%>', urlencode($urow['ContactName']), $templateCode);
		$templateCode=str_replace('<%%VALUE(ContactTitle)%%>', htmlspecialchars($row['ContactTitle'], ENT_QUOTES, 'iso-8859-1'), $templateCode);
		$templateCode=str_replace('<%%URLVALUE(ContactTitle)%%>', urlencode($urow['ContactTitle']), $templateCode);
		if($dvprint){
			$templateCode = str_replace('<%%VALUE(Address)%%>', nl2br(htmlspecialchars($row['Address'], ENT_QUOTES, 'iso-8859-1')), $templateCode);
		}else{
			$templateCode = str_replace('<%%VALUE(Address)%%>', htmlspecialchars($row['Address'], ENT_QUOTES, 'iso-8859-1'), $templateCode);
		}
		$templateCode=str_replace('<%%URLVALUE(Address)%%>', urlencode($urow['Address']), $templateCode);
		$templateCode=str_replace('<%%VALUE(City)%%>', htmlspecialchars($row['City'], ENT_QUOTES, 'iso-8859-1'), $templateCode);
		$templateCode=str_replace('<%%URLVALUE(City)%%>', urlencode($urow['City']), $templateCode);
		$templateCode=str_replace('<%%VALUE(Region)%%>', htmlspecialchars($row['Region'], ENT_QUOTES, 'iso-8859-1'), $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Region)%%>', urlencode($urow['Region']), $templateCode);
		$templateCode=str_replace('<%%VALUE(PostalCode)%%>', htmlspecialchars($row['PostalCode'], ENT_QUOTES, 'iso-8859-1'), $templateCode);
		$templateCode=str_replace('<%%URLVALUE(PostalCode)%%>', urlencode($urow['PostalCode']), $templateCode);
		$templateCode=str_replace('<%%VALUE(Country)%%>', htmlspecialchars($row['Country'], ENT_QUOTES, 'iso-8859-1'), $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Country)%%>', urlencode($urow['Country']), $templateCode);
		$templateCode=str_replace('<%%VALUE(Phone)%%>', htmlspecialchars($row['Phone'], ENT_QUOTES, 'iso-8859-1'), $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Phone)%%>', urlencode($urow['Phone']), $templateCode);
		$templateCode=str_replace('<%%VALUE(Fax)%%>', htmlspecialchars($row['Fax'], ENT_QUOTES, 'iso-8859-1'), $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Fax)%%>', urlencode($urow['Fax']), $templateCode);
	}else{
		$templateCode=str_replace('<%%VALUE(CustomerID)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(CustomerID)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(CompanyName)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(CompanyName)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(ContactName)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(ContactName)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(ContactTitle)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(ContactTitle)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(Address)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Address)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(City)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(City)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(Region)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Region)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(PostalCode)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(PostalCode)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(Country)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Country)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(Phone)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Phone)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(Fax)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Fax)%%>', urlencode(''), $templateCode);
	}

	// process translations
	foreach($Translation as $symbol=>$trans){
		$templateCode=str_replace("<%%TRANSLATION($symbol)%%>", $trans, $templateCode);
	}

	// clear scrap
	$templateCode=str_replace('<%%', '<!-- ', $templateCode);
	$templateCode=str_replace('%%>', ' -->', $templateCode);

	// hide links to inaccessible tables
	if($_POST['dvprint_x'] == ''){
		$templateCode .= "\n\n<script>\$j(function(){\n";
		$arrTables = getTableList();
		foreach($arrTables as $name => $caption){
			$templateCode .= "\t\$j('#{$name}_link').removeClass('hidden');\n";
			$templateCode .= "\t\$j('#xs_{$name}_link').removeClass('hidden');\n";
		}

		$templateCode .= $jsReadOnly;
		$templateCode .= $jsEditable;

		if(!$selected_id){
		}

		$templateCode.="\n});</script>\n";
	}

	// ajaxed auto-fill fields
	$templateCode .= '<script>';
	$templateCode .= '$j(function() {';


	$templateCode.="});";
	$templateCode.="</script>";
	$templateCode .= $lookups;

	// handle enforced parent values for read-only lookup fields

	// don't include blank images in lightbox gallery
	$templateCode=preg_replace('/blank.gif" rel="lightbox\[.*?\]"/', 'blank.gif"', $templateCode);

	// don't display empty email links
	$templateCode=preg_replace('/<a .*?href="mailto:".*?<\/a>/', '', $templateCode);

	// hook: customers_dv
	if(function_exists('customers_dv')){
		$args=array();
		customers_dv(($selected_id ? $selected_id : FALSE), getMemberInfo(), $templateCode, $args);
	}

	return $templateCode;
}
?>