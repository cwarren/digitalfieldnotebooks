<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfNotebookPageField extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testNotebookPageFieldAtributesExist() {
			$this->assertEqual(count(Notebook_Page_Field::$fields), 8);

            $this->assertTrue(in_array('notebook_page_field_id', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('created_at', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('updated_at', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('notebook_page_id', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('label_metadata_structure_id', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('value_metadata_term_value_id', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('value_open', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('flag_delete', Notebook_Page_Field::$fields));
		}

		//// static methods

		function testCmp() {
            $pfs = Notebook_Page_Field::getAllFromDb([],$this->DB);

            usort($pfs,'Notebook_Page_Field::cmp');

            $this->assertEqual(1204,$pfs[0]->notebook_page_field_id);
            $this->assertEqual(1201,$pfs[1]->notebook_page_field_id);
            $this->assertEqual(1202,$pfs[2]->notebook_page_field_id);
            $this->assertEqual(1203,$pfs[3]->notebook_page_field_id);
            $this->assertEqual(1205,$pfs[4]->notebook_page_field_id);
        }

        function testCreateNewNotebookPageFieldForNotebookPage() {
            $npf = Notebook_Page_Field::createNewNotebookPageFieldForNotebookPage(1101,$this->DB);

            $this->assertEqual('NEW',$npf->notebook_page_field_id);
            $this->assertNotEqual('',$npf->created_at);
            $this->assertNotEqual('',$npf->updated_at);
            $this->assertEqual(1101,$npf->notebook_page_id);
            $this->assertEqual(0,$npf->label_metadata_structure_id);
            $this->assertEqual(0,$npf->value_metadata_term_value_id);
            $this->assertEqual('',$npf->value_open);
            $this->assertEqual(false,$npf->flag_delete);
        }

        function testRenderFormInteriorForNewNotebookPageField() {
            global $DB;
            $DB = $this->DB;

            $canonical = '';

            $canonical .= '<div class="notebook_page_field-label_metadata new-notebook-field-data">';
            $canonical .= '<div class="form-field-label">'.util_lang('metadata','properize').'</div>';
            $canonical .= Metadata_Structure::renderControlSelectAllMetadataStructures('notebook_page_field-label_metadata_structure_id_ABC')."\n";
            $canonical .= '</div>';

            $canonical .= '<div class="notebook_page_field-value_specific_metadata new-notebook-field-data">';
            $canonical .= '<div class="form-field-label">'.util_lang('metadata_specific_value','properize').'</div>';
            $canonical .= '<select name="notebook_page_field-value_metadata_term_value_id_ABC" id="notebook_page_field-value_metadata_term_value_id_ABC" class="metadata_term_value_select_control">'."\n";
            $canonical .= '  <option value="-1">-- '.util_lang('nothing_from_the_list').' --</option>'."\n";
            $canonical .= '</select>'."\n";
            $canonical .= '</div>';

            $canonical .= '<div class="notebook_page_field-value_open_metadata new-notebook-field-data">';
            $canonical .= '<div class="form-field-label">'.util_lang('metadata_open_value','properize').'</div>';
            $canonical .= '<input type="text" name="notebook_page_field-value_open_ABC" id="notebook_page_field-value_open_ABC" class="page_field_open_value" value=""/>'."\n";
            $canonical .= '</div>';


            $rendered = Notebook_Page_Field::renderFormInteriorForNewNotebookPageField('ABC');

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        //// instance methods - related data

        function testGetNotebookPage() {
            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1201],$this->DB);
            $p = $npf->getNotebookPage();
            $this->assertEqual(1101,$p->notebook_page_id);
        }

        function testGetMetadataStructure() {
            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1201],$this->DB);
            $s = $npf->getMetadataStructure();
            $this->assertEqual(6002,$s->metadata_structure_id);
        }

        function testGetMetadataTermValue() {
            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1201],$this->DB);
            $v = $npf->getMetadataTermValue();
            $this->assertEqual(6202,$v->metadata_term_value_id);
        }

        //// instance methods - object itself

        function testRenderAsListItemBasic() {
            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1201],$this->DB);

            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id'=>6202],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $canonical = '<li data-notebook_page_field_id="1201" data-created_at="'.$npf->created_at.'" data-updated_at="'.$npf->updated_at.'" data-notebook_page_id="1101" data-label_metadata_structure_id="6002" data-value_metadata_term_value_id="6202" data-value_open="" data-flag_delete="0">'.
                '<div class="notebook-page-field-label field-label" title="'.htmlentities($mds->description).'">'.$mds->renderAsFullName().'</div> : <div class="notebook-page-field-value field-value" title="'.htmlentities($mdtv->description).'">'.htmlentities($mdtv->name).'</div>'.
                '</li>';

            $rendered = $npf->renderAsListItem();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }


        function testRenderAsListItemOpenValue() {
            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1204],$this->DB);

            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6004],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $canonical = '<li data-notebook_page_field_id="1204" data-created_at="'.$npf->created_at.'" data-updated_at="'.$npf->updated_at.'" data-notebook_page_id="1101" data-label_metadata_structure_id="6004" data-value_metadata_term_value_id="0" data-value_open="wavy-ish" data-flag_delete="0">'.
                '<div class="notebook-page-field-label field-label" title="'.htmlentities($mds->description).'">'.htmlentities($mds->name).'</div> : <div class="notebook-page-field-value field-value"><span class="open-value">wavy-ish</span></div>'.
                '</li>';

            $rendered = $npf->renderAsListItem();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }


        function testRenderAsListItemReferencedAndOpenValue() {
            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1205],$this->DB);

            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id'=>6205],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $canonical = '<li data-notebook_page_field_id="1205" data-created_at="'.$npf->created_at.'" data-updated_at="'.$npf->updated_at.'" data-notebook_page_id="1104" data-label_metadata_structure_id="6002" data-value_metadata_term_value_id="6205" data-value_open="rare" data-flag_delete="0">'.
                '<div class="notebook-page-field-label field-label" title="'.htmlentities($mds->description).'">'.$mds->renderAsFullName().'</div> : <div class="notebook-page-field-value field-value" title="'.htmlentities($mdtv->description).'">'.htmlentities($mdtv->name).'; <span class="open-value">rare</span></div>'.
                '</li>';

            $rendered = $npf->renderAsListItem();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsListItemEdit_standard() {
            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1205],$this->DB);

            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id'=>6101],$this->DB);
//            $mdtvs = Metadata_Term_Value::getAllFromDb(['metadata_term_set_id'=>6101],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $canonical = '<li id="list_item-notebook_page_field_1205" data-notebook_page_field_id="1205" data-created_at="'.$npf->created_at.'" data-updated_at="'.$npf->updated_at.'" data-notebook_page_id="1104" data-label_metadata_structure_id="6002" data-value_metadata_term_value_id="6205" data-value_open="rare" data-flag_delete="0">'.
                '<div class="notebook-page-field-label field-label" title="'.htmlentities($mds->description).'">'.$mds->renderAsFullName().'</div> : <div class="notebook-page-field-value field-value">'.
                $mdts->renderAsSelectControl('page_field_select_1205',6205).
                '; <input type="text" name="page_field_open_value_1205" id="page_field_open_value_1205" class="page_field_open_value" value="rare"/>'.
                '</div> <button class="btn btn-danger button-mark-pagefield-for-delete" title="'.util_lang('mark_for_delete','ucfirst').'" data-do-mark-title="'.util_lang('mark_for_delete','ucfirst').'" data-remove-mark-title="'.util_lang('unmark_for_delete','ucfirst').'" data-for_dom_id="list_item-notebook_page_field_1205" data-notebook_page_field_id="1205"><i class="icon-remove-sign icon-white"></i></button></li>';

            $rendered = $npf->renderAsListItemEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n--------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsListItemEdit_noTermSet() {
            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1204],$this->DB);

            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6004],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $canonical = '<li id="list_item-notebook_page_field_1204" data-notebook_page_field_id="1204" data-created_at="'.$npf->created_at.'" data-updated_at="'.$npf->updated_at.'" data-notebook_page_id="1101" data-label_metadata_structure_id="6004" data-value_metadata_term_value_id="0" data-value_open="wavy-ish" data-flag_delete="0">'.
                '<div class="notebook-page-field-label field-label" title="'.htmlentities($mds->description).'">'.$mds->renderAsFullName().'</div> : <div class="notebook-page-field-value field-value">'.
                util_lang('metadata_structure_has_no_term_set').
                '; <input type="text" name="page_field_open_value_1204" id="page_field_open_value_1204" class="page_field_open_value" value="wavy-ish"/>'.
                '</div> <button class="btn btn-danger button-mark-pagefield-for-delete" title="'.util_lang('mark_for_delete','ucfirst').'" data-do-mark-title="'.util_lang('mark_for_delete','ucfirst').'" data-remove-mark-title="'.util_lang('unmark_for_delete','ucfirst').'" data-for_dom_id="list_item-notebook_page_field_1204" data-notebook_page_field_id="1204"><i class="icon-remove-sign icon-white"></i></button></li>';

            $rendered = $npf->renderAsListItemEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n--------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

    }