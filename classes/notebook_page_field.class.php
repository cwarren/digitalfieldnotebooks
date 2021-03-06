<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Notebook_Page_Field extends Db_Linked {
		public static $fields = array('notebook_page_field_id', 'created_at', 'updated_at',
                                      'notebook_page_id', 'label_metadata_structure_id', 'value_metadata_term_value_id', 'value_open', 'flag_delete');
		public static $primaryKeyField = 'notebook_page_field_id';
		public static $dbTable = 'notebook_page_fields';
        public static $entity_type_label = 'notebook_page_field';

		public function __construct($initsHash) {
			parent::__construct($initsHash);


			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user

			//		$this->flag_is_system_admin = false;
			//		$this->flag_is_banned = false;
		}

		public static function cmp($a, $b) {
            if ($a->notebook_page_id == $b->notebook_page_id) {

                if ($a->label_metadata_structure_id == $b->label_metadata_structure_id) {

                    if (($a->value_metadata_term_value_id == 0) && ($b->value_metadata_term_value_id == 0)) {
                        return strcmp($a->value_open,$b->value_open);
                    }

                    if ($a->value_metadata_term_value_id == 0) {
                        return 1;
                    }

                    if ($b->value_metadata_term_value_id == 0) {
                        return -1;
                    }

                    return Metadata_Structure::cmp($a->getMetadataTermValue(),$b->getMetadataTermValue());
                }

                return Metadata_Structure::cmp($a->getMetadataStructure(),$b->getMetadataStructure());

            }
            return Notebook_Page::cmp($a->getNotebookPage(),$b->getNotebookPage());
        }

        public static function createNewNotebookPageFieldForNotebookPage($notebook_page_id,$db_connection) {
            $npf = new Notebook_Page_Field([
                'notebook_page_field_id' => 'NEW',
                'created_at' => util_currentDateTimeString_asMySQL(),
                'updated_at' => util_currentDateTimeString_asMySQL(),
                'notebook_page_id' => $notebook_page_id,
                'label_metadata_structure_id' => 0,
                'value_metadata_term_value_id' => 0,
                'value_open' => '',
                'flag_delete' => false,
                'DB'=>$db_connection]);
            return $npf;
        }

        public static function renderFormInteriorForNewNotebookPageField($unique_string) {
            $rendered = '';

            $rendered .= '<div class="notebook_page_field-label_metadata new-notebook-field-data">';
            $rendered .= '<div class="form-field-label">'.util_lang('metadata','properize').'</div>';
            $rendered .= Metadata_Structure::renderControlSelectAllMetadataStructures('notebook_page_field-label_metadata_structure_id_'.$unique_string)."\n";
            $rendered .= '</div>';

            $rendered .= '<div class="notebook_page_field-value_specific_metadata new-notebook-field-data">';
            $rendered .= '<div class="form-field-label">'.util_lang('metadata_specific_value','properize').'</div>';
            $rendered .= '<select name="notebook_page_field-value_metadata_term_value_id_'.$unique_string.'" id="notebook_page_field-value_metadata_term_value_id_'.$unique_string.'" class="metadata_term_value_select_control">'."\n";
            $rendered .= '  <option value="-1">-- '.util_lang('nothing_from_the_list').' --</option>'."\n";
            $rendered .= '</select>'."\n";
            $rendered .= '</div>';

            $rendered .= '<div class="notebook_page_field-value_open_metadata new-notebook-field-data">';
            $rendered .= '<div class="form-field-label">'.util_lang('metadata_open_value','properize').'</div>';
            $rendered .= '<input type="text" name="notebook_page_field-value_open_'.$unique_string.'" id="notebook_page_field-value_open_'.$unique_string.'" class="page_field_open_value" value=""/>'."\n";
            $rendered .= '</div>';

            return $rendered;
        }

        //------------------------------------------

        public function getNotebookPage() {
            return Notebook_Page::getOneFromDb(['notebook_page_id'=>$this->notebook_page_id],$this->dbConnection);
        }

        public function getMetadataStructure() {
            return Metadata_Structure::getOneFromDb(['metadata_structure_id'=>$this->label_metadata_structure_id],$this->dbConnection);
        }

        public function getMetadataTermValue() {
            return Metadata_Term_Value::getOneFromDb(['metadata_term_value_id'=>$this->value_metadata_term_value_id],$this->dbConnection);
        }

        //------------------------------------------

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().'>';

            $mds = $this->getMetadataStructure();

//            $li_elt .= '<div class="notebook-page-field-label field-label" title="'.htmlentities($mds->description).'">'.htmlentities($mds->name).'</div> : ';
            $li_elt .= '<div class="notebook-page-field-label field-label" title="'.htmlentities($mds->description).'">'.$mds->renderAsFullName().'</div> : ';

            $val_title = '';
            $val_name = '';
            $val_open = '';
            if ($this->value_metadata_term_value_id > 0) {
                $mdtv = $this->getMetadataTermValue();
                $val_title = ' title="'.htmlentities($mdtv->description).'"';
                $val_name = htmlentities($mdtv->name);
            }
            if ($this->value_open) {
                $val_open = '<span class="open-value">'.htmlentities($this->value_open).'</span>';
                if ($val_name) {
                    $val_open = '; '.$val_open;
                }
            }
            $li_elt .= '<div class="notebook-page-field-value field-value"'.$val_title.'>'.$val_name.$val_open.'</div>';

            $li_elt .= '</li>';
            return $li_elt;
        }

        public function renderAsListItemEdit($idstr='',$classes_array = [],$other_attribs_hash = []) {
            if (! $idstr) {
                $idstr = 'list_item-notebook_page_field_'.$this->notebook_page_field_id;
            }

            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().'>';

            $mds = $this->getMetadataStructure();
            $mds->loadTermSetAndValues();

//            util_prePrintR($mds);


//            $li_elt .= '<div class="notebook-page-field-label field-label" title="'.htmlentities($mds->description).'">'.htmlentities($mds->name).'</div> : <div class="notebook-page-field-value field-value">';
            $li_elt .= '<div class="notebook-page-field-label field-label" title="'.htmlentities($mds->description).'">'.$mds->renderAsFullName().'</div> : <div class="notebook-page-field-value field-value">';
            if ($mds->term_set) {
                $li_elt .= $mds->term_set->renderAsSelectControl('page_field_select_'.$this->notebook_page_field_id,$this->value_metadata_term_value_id);
            }
            else {
                $li_elt .= util_lang('metadata_structure_has_no_term_set');
            }
            $li_elt .= '; <input type="text" name="page_field_open_value_'.$this->notebook_page_field_id.'" id="page_field_open_value_'.$this->notebook_page_field_id.'" class="page_field_open_value" value="'.htmlentities($this->value_open).'"/>';

            $li_elt .= '</div> <button class="btn btn-danger button-mark-pagefield-for-delete" title="'.util_lang('mark_for_delete','ucfirst').'" data-do-mark-title="'.util_lang('mark_for_delete','ucfirst').'" data-remove-mark-title="'.util_lang('unmark_for_delete','ucfirst').'" data-for_dom_id="'.$idstr.'" data-notebook_page_field_id="'.$this->notebook_page_field_id.'"><i class="icon-remove-sign icon-white"></i></button></li>';
            return $li_elt;
        }

	}
