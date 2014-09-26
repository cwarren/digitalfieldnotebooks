<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class NotebookPageEditAndCreateTest extends WMSWebTestCase {

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
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=view&notebook_page_id='.$id);
    }

    function goToEdit($id) {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id='.$id);
    }

    function checkBasicAsserts() {
        $this->assertNoText('IMPLEMENTED');
        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/fatal error/i');
    }

    //-----------------------------------------------------------------------------------------------------------------

    function testMissingNotebookPageIdShowsNotebookListInstead() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit');
//
////        $this->showContent();
//
        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_notebook_page_specified'));
        $this->assertEltByIdHasAttrOfValue('list-of-user-notebooks','id','list-of-user-notebooks');
//        exit;
    }

    function testNonexistentNotebookPageShowsNotebookListInstead() {
//        $this->todo();
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=999');

        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_notebook_page_found'));
        $this->assertEltByIdHasAttrOfValue('list-of-user-notebooks','id','list-of-user-notebooks');
    }

    function testNoEditPermDefaultsToView() {
//        $this->todo();
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=1104');

        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook_page')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_permission'));
        $this->assertEltByIdHasAttrOfValue('rendered_notebook_page_1104','class','rendered_notebook_page');

//        $this->showContent();
    }

    function testEditAccessControl_public() {
//        $this->todo('basic public access check - no access defaults to view');
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=1104');

        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook_page')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_permission'));
        $this->assertEltByIdHasAttrOfValue('rendered_notebook_page_1104','class','rendered_notebook_page');
    }

    function testEditAccessControl_owner() {
//        $this->todo('basic access check as owner - no edit notebook owned by another');
        // NOTE: the latter is handled by testNoEditPermDefaultsToView

        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=1101');

        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook_page')) ,$this->getBrowser()->getTitle());
//
////        $this->todo('basic access check as owner - can edit owned notebook');
        $this->assertNoText(util_lang('no_permission'));

//        $this->todo('editable fields exist');
        $this->assertFieldById('authoritative-plant-id');

//        $this->todo('publish option, no verify option');
        $this->assertFieldById('notebook-workflow-publish-control');
        $this->assertNoFieldById('notebook-workflow-validate-control');

        $this->assertEltByIdHasAttrOfValue('edit-submit-control','value',util_lang('update','properize'));
    }

    function testEditAccessControl_admin() {
        $this->todo('basic access check as admin');
//        $this->doLoginAdmin();
//
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id=1004');
//
//        $this->checkBasicAsserts();
//        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook')) ,$this->getBrowser()->getTitle());
//
////        $this->todo('basic access check as owner - can edit owned notebook');
//        $this->assertNoText(util_lang('no_permission'));
//
////        $this->todo('editable fields');
//        $this->assertFieldById('notebook-name');
//        $this->assertFieldById('notebook-notes');
//
////        $this->todo('publish option, no verify option');
//        $this->assertFieldById('notebook-workflow-publish-control');
//        $this->assertFieldById('notebook-workflow-validate-control');
//
//        $this->assertEltByIdHasAttrOfValue('edit-submit-control','value',util_lang('update','properize'));
    }

//
//    function testFormFieldLookups() {
//        $this->todo();
//    }

    function testRelatedDataListing() {
        $this->todo();
//        $u = User::getOneFromDb(['user_id'=>101],$this->DB);
//        $pages = Notebook_Page::getAllFromDb(['notebook_id'=>1001],$this->DB);
//
//        $this->doLoginBasic();
//
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id=1001');
//
//        $this->checkBasicAsserts();
//
////        $this->todo('owner name has link to user page');
//        $this->assertLink(htmlentities($u->screen_name));
//
////        $this->todo('notebook pages are listed and linked');
//        $this->assertLink($pages[0]->getAuthoritativePlant()->renderAsShortText());
//        $this->assertLink($pages[1]->getAuthoritativePlant()->renderAsShortText());
//
////        util_prePrintR(htmlentities($this->getBrowser()->getContent()));

    }

    function testBaseDataUpdate() {
//        $this->doLoginBasic();
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id=1001');
//
////      NOTE: the identifier to use for setField is the value of the name attribute of the field
//        $this->setField('name','new name for testnotebook1');
////        NOTE: the identifier to use for buttons is the value of the value attribute of the button
//        $this->click(util_lang('update','properize'));
//
//
//        $this->checkBasicAsserts();
//        $this->assertText('new name for testnotebook1');
//
//        $n = Notebook::getOneFromDb(['notebook_id'=>1001],$this->DB);
//        $this->assertEqual($n->name,'new name for testnotebook1');

//        util_prePrintR(htmlentities($this->getBrowser()->getContent()));
        $this->todo();
    }

    function testCreateButton() {
        $this->todo();
//        $this->doLoginBasic();
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=list');
//
//        $this->click(util_lang('add_notebook'));
//
//        $this->checkBasicAsserts();
//        $this->assertPattern('/'.util_lang('new_notebook_title').'/');

//        $this->showContent();
    }

    function testToDo() {
//        $this->todo('test fall backs and default behaviors');
//        $this->todo('test access control to edit page');
// NOTE: nothing here for notebooks       $this->todo('test look-up data form fields (not much for this, but gets messy once we get to pages)');
//        $this->todo('test data pre-population');
//        $this->todo('test existence of dynamic elements for in-place related data');
//        $this->todo('  ----------  build in-place editing fragments for related data, and associated tests (not much for this, but gets messy once we get to pages)');
//        $this->todo('test updating base data');
//        $this->todo('test updating related data via ajax');
    }

}