<?php

class BlockEnhancements_SiteTreeExt extends SiteTreeExtension {

    public function updateCMSActions(FieldList $actions)
    {
        if(!$this->owner->Blocks()->count()) return;

        $actions->fieldByName('MajorActions')->push(
            $publish = FormAction::create('publishPageAndBlocks', 'Published (+Blocks)')
                ->setAttribute('data-icon', 'accept')
                ->setAttribute('data-icon-alternate', 'disk')
                ->setAttribute('data-text-alternate', 'Save & publish (+Blocks)')
        );

        // Set up the initial state of the button to reflect the state of the blocks
        foreach ($this->owner->Blocks() as $block) {
            if ($block->stagesDiffer('Stage', 'Live')) {
                $publish->addExtraClass('ss-ui-alternate');
                break;
            }
        }

    }


}