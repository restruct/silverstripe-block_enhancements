<?php
/**
 * GFConf_BlockManagerEnhanced
 * Provides an enhanced reusable GridFieldConfig for managing Blocks.
 *
 */
class GFConf_BlockManagerEnhanced extends GridFieldConfig
{
    public $blockManager;

    public function __construct($canAdd = true, $canEdit = true, $canDelete = true, $editableRows = false, $aboveOrBelow = false)
    {
        parent::__construct();

        $this->blockManager = Injector::inst()->get('BlockManager');
        $controllerClass = Controller::curr()->class;
        // Get available Areas (for page) or all in case of ModelAdmin
        if ($controllerClass == 'CMSPageEditController') {
            $currentPage = Controller::curr()->currentPage();
            $areasFieldSource = $this->blockManager->getAreasForPageType($currentPage->ClassName);
        } else {
            $areasFieldSource = $this->blockManager->getAreasForTheme();
        }
        // EDIT
        $blockTypeArray = $this->blockManager->getBlockClasses();
        // /EDIT

        // EditableColumns only makes sense on Saveable parenst (eg Page), or inline changes won't be saved
        if ($editableRows) {

            // set project-dir in cookie to be accessible as fallback from js
            Cookie::set('js-project-dir',project(),90,null,null,false,false);

            $this->addComponent($editable = new GridFieldEditableColumns());
            $displayfields = array(
                // EDIT
                //'singular_name' => array('title' => _t('Block.BlockType', 'Block Type'), 'field' => 'ReadonlyField'),
                'ClassName' => array(
                    'title' => _t('Block.BlockType', 'Block Type').'
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                    // the &nbsp;s prevent wrapping of dropdowns
                    'callback' => function () use ($blockTypeArray) {

                        return DropdownField::create('ClassName', 'Block Type', $blockTypeArray)
//                                ->setHasEmptyDefault(true)
                            ->addExtraClass('select2blocktype')
                            ->setAttribute('data-project-dir', project());
                    },
                ),
                'Title' => array(
                    'title' => _t('Block.TitleName', 'Block Name'),
//                    'field' => 'ReadonlyField'
                    'field' => 'TextField'
                ),
                // /EDIT
                'BlockArea' => array(
                    'title' => _t('Block.BlockArea', 'Block Area').'
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                        // the &nbsp;s prevent wrapping of dropdowns
                    'callback' => function () use ($areasFieldSource) {
                            return DropdownField::create('BlockArea', 'Block Area', $areasFieldSource)
                                ->setHasEmptyDefault(true);
                        },
                ),
                'isPublishedNice' => array('title' => _t('Block.IsPublishedField', 'Published'), 'field' => 'ReadonlyField'),
                'UsageListAsString' => array('title' => _t('Block.UsageListAsString', 'Used on'), 'field' => 'ReadonlyField'),
            );

            if ($aboveOrBelow) {
                $displayfields['AboveOrBelow'] = array(
                    'title' => _t('GridFieldConfigBlockManager.AboveOrBelow', 'Above or Below'),
                    'callback' => function () {
                        return DropdownField::create('AboveOrBelow', _t('GridFieldConfigBlockManager.AboveOrBelow', 'Above or Below'), BlockSet::config()->get('above_or_below_options'));
                    },
                );
            }
            $editable->setDisplayFields($displayfields);
            // EDIT
            $this->addComponent($erow = new EditableBlockRow());
            // /EDIT
        } else {
            $this->addComponent($dcols = new GridFieldDataColumns());

            $displayfields = array(
                'singular_name' => _t('Block.BlockType', 'Block Type'),
                // EDIT
//                'Title' => _t('Block.Title', 'Description'),
                'Title' => _t('Block.TitleName', 'Block Name'),
                // /EDIT
                'BlockArea' => _t('Block.BlockArea', 'Block Area'),
                'isPublishedNice' => _t('Block.IsPublishedField', 'Published'),
                'UsageListAsString' => _t('Block.UsageListAsString', 'Used on'),
            );
            $dcols->setDisplayFields($displayfields);
            $dcols->setFieldCasting(array('UsageListAsString' => 'HTMLText->Raw'));
        }

        $this->addComponent(new GridFieldButtonRow('before'));
        // EDIT
        $this->addComponent(new GridFieldButtonRow('after'));
        // /EDIT
        $this->addComponent(new GridFieldToolbarHeader());
        $this->addComponent(new GridFieldDetailForm());
        // EDIT
        //$this->addComponent($sort = new GridFieldSortableHeader());
        //$this->addComponent($filter = new GridFieldFilterHeader());
        //$this->addComponent(new GridFieldDetailForm());

        //$filter->setThrowExceptionOnBadDataType(false);
        //$sort->setThrowExceptionOnBadDataType(false);

        // load enhancements module (eg inline editing etc, needs save action @TODO: move to SiteTree only?
        if(class_exists('GF_BlockEnhancements')){
            $this->addComponent(new GF_BlockEnhancements());
        }

        // stuff only for BlockAdmin
        if ($controllerClass == 'BlockAdmin') {
            $this->addComponent($sort = new GridFieldSortableHeader());
            $sort->setThrowExceptionOnBadDataType(false);
            $this->addComponent($filter = new GridFieldFilterHeader());
            $filter->setThrowExceptionOnBadDataType(false);
        } else {
            // only for GF on SiteTree
            $this->addComponent(new GridFieldTitleHeader());
            $this->addComponent(new GridFieldFooter());
            // groupable
            $this->addComponent(new GridFieldGroupable(
                'BlockArea',
                'Area',
                'none',
                $areasFieldSource
            ));
//            var_dump($areasFieldSource);

//            // Get available Areas (for page) enhancements inactive when in ModelAdmin/BlockAdmin
//            if (Controller::curr() && Controller::curr()->class == 'CMSPageEditController') {
//                // Provide defined blockAreas to JS
//                $blockManager = Injector::inst()->get('BlockManager');
////            $blockAreas = $blockManager->getAreasForPageType( Controller::curr()->currentPage()->ClassName );
//                $blockAreas = $blockManager->getAreasForPageType( Controller::curr()->currentPage()->ClassName );
//            }
        }

        if ($canAdd) {
            $multiClass = new GridFieldAddNewMultiClass('after');
            $classes = $this->blockManager->getBlockClasses();
            $multiClass->setClasses($classes);
            $this->addComponent($multiClass);
            //$this->addComponent(new GridFieldAddNewButton());
        }
        // /EDIT

        if ($controllerClass == 'BlockAdmin' && class_exists('GridFieldCopyButton')) {
            $this->addComponent(new GridFieldCopyButton());
        }

        if ($canEdit) {
            $this->addComponent(new GridFieldEditButton());
        }

        if ($canDelete) {
            $this->addComponent(new GridFieldDeleteAction(true));
        }

        return $this;
    }

    /**
     * Add the GridFieldAddExistingSearchButton component to this grid config.
     *
     * @return $this
     **/
    public function addExisting()
    {
        // EDIT
        //$this->addComponent($add = new GridFieldAddExistingSearchButton());
        $this->addComponent($add = new GridFieldAddExistingSearchButton('buttons-after-right'));
        // /EDIT
        $add->setSearchList(Block::get());

        return $this;
    }

    /**
     * Add the GridFieldBulkManager component to this grid config.
     *
     * @return $this
     **/
    public function addBulkEditing()
    {
        if (class_exists('GridFieldBulkManager')) {
            $this->addComponent(new GridFieldBulkManager());
        }

        return $this;
    }
}
