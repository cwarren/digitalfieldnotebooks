<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class MetadataViewTest extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
        global $CUR_LANG_SET;
        $CUR_LANG_SET = 'en';
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    function doLoginBasic() {
        $this->get('http://localhost/digitalfieldnotebooks/');
        $this->assertCookie('PHPSESSID');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);

        $this->click('Sign in');

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');
    }

    function doLoginAdmin() {
        makeAuthedTestUserAdmin($this->DB);
        $this->doLoginBasic();
    }

    function goToView($id) {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=view&metadata_structure_id='.$id);
    }

    //-----------------------------------------------------------------------------------------------------------------

    function testListOfAll() {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=list');

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');
        $this->assertNoText('IMPLEMENTED');

        $this->assertText(util_lang('all_metadata'));

        $this->assertLink('flower');
        $this->assertLink('flower size');
        $this->assertLink('flower primary color');
        $this->assertLink('leaf');
    }

    function testViewIsDefaultActionForSpecific() {
        $this->doLoginBasic();

        $this->goToView(6001);

        $view_content = $this->getBrowser()->getContent();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?metadata_structure_id=6001');

        $no_action_given_content = $this->getBrowser()->getContent();

        $this->assertEqual($view_content,$no_action_given_content);
    }

    function testMissingIdRedirectsToFullList() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=view');

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('metadata')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('all_metadata'));
    }

    function testNonexistentRedirectsToFullList() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?metadata_structure_id=999');

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('metadata')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('all_metadata'));
    }

    function testActionNotAllowedRedirectsToFullList() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=edit&metadata_structure_id=6004');

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('metadata')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('all_metadata'));
    }

    function testViewNotEditable_LEAF() {
//        $this->doLoginBasic();

        $this->goToView(6002);

//        echo htmlentities($this->getBrowser()->getContent());

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');
        $this->assertNoText('IMPLEMENTED');

        $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);

//        util_prePrintR($n);

        // page heading
        $this->assertLink(util_lang('metadata'));
        $this->assertLink('flower');

        $this->assertText(ucfirst(util_lang('metadata')).' : '.$mds->name);

        $this->assertText($mds->description);
        $this->assertText($mds->details);
        $this->assertEltByIdHasAttrOfValue('rendered_metadata_reference_6302','class','rendered_metadata_reference rendered_metadata_reference_text');

        // NO 'edit' control
        $this->assertNoLink(util_lang('edit'));

        // MORE!!!!
//        $this->todo('additional metadata view checks/asserts');
        // term set & values

        $this->assertText('flower size');
        $this->assertText('small lengths');
        $this->assertText('3 mm - 1cm');


    }

    function testViewNotEditable_BRANCH() {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=view&metadata_structure_id=6001');

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');
        $this->assertNoText('IMPLEMENTED');

        $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

        $this->assertText($mds->description);

        $this->assertLink(util_lang('metadata'));
        $this->assertNoLink('flower');
        $this->assertLink('flower size');
        $this->assertLink('flower primary color');
        $this->assertNoLink('leaf');

        $this->assertEltByIdHasAttrOfValue('rendered_metadata_reference_6301','class','rendered_metadata_reference rendered_metadata_reference_text');
    }


    function testViewNotEditable_BUD() {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=view&metadata_structure_id=6004');

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');
        $this->assertNoText('IMPLEMENTED');

        $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6004],$this->DB);

        $this->assertText($mds->description);

        $this->assertLink(util_lang('metadata'));

        $this->assertNoLink('flower');
        $this->assertNoLink('flower size');
        $this->assertNoLink('flower primary color');
        $this->assertNoLink('leaf');

        $this->assertText(util_lang('metadata_no_children_no_values'));
    }
}