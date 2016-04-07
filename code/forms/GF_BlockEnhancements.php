<?php

//	GridField_ColumnProvider, GridField_DataManipulator,
class GF_BlockEnhancements extends RequestHandler implements
	GridField_HTMLProvider,
	GridField_URLHandler
    {
    
    private static $unassigned_area_description = '[none/inactive]';

	private static $allowed_actions = array(
		'handleAreaAssignment',
		'handleBlockTypeAssignment'
	);
    
    public static function include_requirements() {

		$moduleDir = BLOCKS_ENH_DIR;
//        $jsVars = array(
//            //"ThemeDir" => ViewableData::create()->ThemeDir(),
//            "ThemeDir" => SSViewer::get_theme_folder(),
//            "ProjectDir" => project(),
//            "AreaNoneTitle" => Config::inst()->get(get_class(), 'unassigned_area_description'),
////                "BlockAreas" => json_encode( $blockAreas )
//        );
		//->setAttribute('data-project-dir', project());
//        Requirements::javascriptTemplate($moduleDir.'/js/BlockEnhancements.js', $jsVars, 'BlockEnhancements');
        Requirements::javascript($moduleDir.'/js/BlockEnhancements.js');
        Requirements::css($moduleDir.'/css/BlockEnhancements.css');

        Requirements::javascript($moduleDir.'/js/EditableBlockRow.js');
        Requirements::css($moduleDir.'/css/EditableBlockRow.css');

        Requirements::javascript($moduleDir.'/js/display_logic_editablerow-fixes.js');
	}

	public function getURLHandlers($grid) {
		return array(
			'POST area_assignment'    => 'handleAreaAssignment',
			'POST blocktype_assignment'    => 'handleBlockTypeAssignment',
		);
	}
    
//    public function __construct()
//    {
//        parent::__construct();
//        self::include_requirements();
//    }

	/**
	 * @param GridField $field
	 */
	public function getHTMLFragments($field) {
        
        self::include_requirements();
        
        // set ajax urls / vars
		$field->addExtraClass('ss-gridfield-blockenhancements');
		$field->setAttribute('data-url-area-assignment', $field->Link('area_assignment'));
		$field->setAttribute('data-url-blocktype-assignment', $field->Link('blocktype_assignment'));
		$field->setAttribute('data-block-area-none-title', Config::inst()->get(get_class(), 'unassigned_area_description'));
        
        // Get available Areas (for page) enhancements inactive when in ModelAdmin/BlockAdmin
        if (Controller::curr() && Controller::curr()->class == 'CMSPageEditController') {
            // Provide defined blockAreas to JS
            $blockManager = Injector::inst()->get('BlockManager');
//            $blockAreas = $blockManager->getAreasForPageType( Controller::curr()->currentPage()->ClassName );
            $blockAreas = $blockManager->getAreasForPageType( Controller::curr()->currentPage()->ClassName );
            $field->setAttribute('data-block-areas', json_encode( $blockAreas ));
        }
        // add no-chozen to dropdown
//        $field->getConfig()->getComponentByType('GridFieldAddNewMultiClass')->
//        $field->getConfig()->getComponentByType('GridFieldDetailForm')->setAttribute('data-project-dir', project());

	}
    
	/**
	 * Handles requests to assign a new block area to a block item
	 *
	 * @param GridField $grid
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	public function handleAreaAssignment($grid, $request) {
		$list = $grid->getList();
        
        // @TODO: do we need this? (copied from GridFieldOrderableRows::handleReorder)
//		$modelClass = $grid->getModelClass();
//		if ($list instanceof ManyManyList && !singleton($modelClass)->canView()) {
//			$this->httpError(403);
//		} else if(!($list instanceof ManyManyList) && !singleton($modelClass)->canEdit()) {
//			$this->httpError(403);
//		}

		$blockid   = $request->postVar('blockarea_block_id');
		$blockarea   = $request->postVar('blockarea_area');
        if($blockarea=='none') $blockarea = '';
		$block = $list->byID($blockid);

		// Update item with correct Area assigned (custom query required to write m_m_extraField)
//		$block->BlockArea = $blockarea;
//        $block->write();
        // @TODO: improve this custom query to be more robust?
        DB::query(sprintf(
            "UPDATE `%s` SET `%s` = '%s' WHERE `BlockID` = %d",
            'SiteTree_Blocks',
            'BlockArea',
            $blockarea,
            $blockid
        ));
        
        // Forward the request to GridFieldOrderableRows::handleReorder
		return $grid->getConfig()
            ->getComponentByType('GridFieldOrderableRows')
            ->handleReorder($grid, $request);
	}
    
    /**
	 * Handles requests to assign a new block area to a block item
	 *
	 * @param GridField $grid
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	public function handleBlockTypeAssignment($grid, $request) {
		$list = $grid->getList();
        
        // @TODO: do we need this? (copied from GridFieldOrderableRows::handleReorder)
//		$modelClass = $grid->getModelClass();
//		if ($list instanceof ManyManyList && !singleton($modelClass)->canView()) {
//			$this->httpError(403);
//		} else if(!($list instanceof ManyManyList) && !singleton($modelClass)->canEdit()) {
//			$this->httpError(403);
//		}

		$blockid   = $request->postVar('block_id');
		$blocktype   = $request->postVar('block_type');
		$block = $list->byID($blockid);

		// Update item with correct Area assigned (custom query required to write m_m_extraField)
		$block->ClassName = $blocktype;
        $block->write();
        //print_r($block->record);
//        // @TODO: improve this custom query to be more robust?
//        DB::query(sprintf(
//            "UPDATE `%s` SET `%s` = '%s' WHERE `BlockID` = %d",
//            'SiteTree_Blocks',
//            'BlockArea',
//            $blockarea,
//            $blockid
//        ));
        return $grid->FieldHolder();
        
	}

}
