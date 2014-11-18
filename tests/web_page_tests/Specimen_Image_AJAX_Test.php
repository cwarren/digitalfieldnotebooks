<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class Specimen_Image_AJAX_Test extends WMSWebTestCase {

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

    //-----------------------------------------------------------------------------------------------------------------

//    function testNewPageFieldForm() {
//        $this->doLoginBasic();
//
//        global $DB;
//        $DB = $this->DB;
//
//        $this->get('http://localhost/digitalfieldnotebooks/ajax_actions/specimen.php?action=create&unique=ABC123&notebook_page_id=1101');
//        $this->checkBasicAsserts();
//
//        $expected = '<div class="specimen embedded">'."\n".Specimen::renderFormInteriorForNewSpecimen('ABC123',$this->DB)."\n</div>";
//
//        $results = json_decode($this->getBrowser()->getContent());
//        $this->assertEqual('success',$results->status);
//        $this->assertEqual($expected,$results->html_output);
//        $this->assertNoPattern('/IMPLEMENTED/');
//    }

    function testToDo() {
        $this->todo('image upload test');
        $this->todo('image delete test');
    }
}