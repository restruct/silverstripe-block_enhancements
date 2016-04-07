<?php

// https://docs.silverstripe.org/en/3.1/developer_guides/customising_the_admin_interface/how_tos/extend_cms_interface/

class LeftAndMain_BlockActions extends LeftAndMainExtension
{

    private static $allowed_actions = array(
        'publishPageAndBlocks',
//        'toggleshowpast',
    );

//	public function toggleshowpast($data, $form){
//		Session::set('calendar_show_past_events', !Session::get('calendar_show_past_events'));
//		// refresh the page
//		return $this->owner->getResponseNegotiator()->respond($this->owner->request);
//	}

// Example from leftandmain.php:
//    public function save($data, $form) {
//        $className = $this->stat('tree_class');
//
//        // Existing or new record?
//        $id = $data['ID'];
//        if(substr($id,0,3) != 'new') {
//            $record = DataObject::get_by_id($className, $id);
//            if($record && !$record->canEdit()) return Security::permissionFailure($this);
//            if(!$record || !$record->ID) $this->httpError(404, "Bad record ID #" . (int)$id);
//        } else {
//            if(!singleton($this->stat('tree_class'))->canCreate()) return Security::permissionFailure($this);
//            $record = $this->getNewItem($id, false);
//        }
//
//        // save form data into record
//        $form->saveInto($record, true);
//        $record->write();
//        $this->extend('onAfterSave', $record);
//        $this->setCurrentPageID($record->ID);
//
//        $this->getResponse()->addHeader('X-Status', rawurlencode(_t('LeftAndMain.SAVEDUP', 'Saved.')));
//        return $this->getResponseNegotiator()->respond($this->getRequest());
//    }

    public function publishPageAndBlocks($data, $form)
    {

        // regular save
        $this->owner->save($data, $form);

        // Now publish the whole bunch
        if ( $page = SiteTree::get()->byID($data['ID']) ) {
            if (!$page->canPublish()) {
                throw new SS_HTTPResponse_Exception("Publish page not allowed", 403);
            }
            // else: publish page (also triggers editable columns/rows write())
            $page->doPublish();

            // and publish any blocks which the user's allowed to publish (have already been written)
            if (is_callable(array($page, 'Blocks'))) {
                foreach ($page->Blocks() as $block){
                    // skip any blocks that we cannot publish
                    if (!$block->canPublish()) continue;
                    // publish
                    $block->invokeWithExtensions('onBeforePublish', $block);
                    $block->publish('Stage', 'Live');
                    $block->invokeWithExtensions('onAfterPublish', $block);
                }
            }
        } else {
            throw new SS_HTTPResponse_Exception(
                "Bad page ID #" . (int)$data['ID'], 404);
        }

        // this generates a message that will show up in the CMS
        $this->owner->response->addHeader(
            'X-Status',
            rawurlencode("Page + blocks published")
        );

        return $this->owner->getResponseNegotiator()->respond($this->owner->request);
    }

//    public function doAction($data, $form){
//        $className = $this->owner->stat('tree_class');
//        $SQL_id = Convert::raw2sql($data['ID']);
//
//        $record = DataObject::get_by_id($className, $SQL_id);
//
//        if(!$record || !$record->ID){
//            throw new SS_HTTPResponse_Exception(
//                "Bad record ID #" . (int)$data['ID'], 404);
//        }
//
//        // at this point you have a $record, 
//        // which is your page you can work with!
//
//        // this generates a message that will show up in the CMS
//        $this->owner->response->addHeader(
//            'X-Status',
//            rawurlencode('Success message!') 
//        );
//
//        return $this->owner->getResponseNegotiator()
//               ->respond($this->owner->request);
//    }

}