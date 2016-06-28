<?php namespace Milkyway\SS\Core\Extensions;
/**
 * Milkyway Multimedia
 * UploadField.php
 *
 * @package milkyway-multimedia/ss-mwm
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 * @credit micschk <https://github.com/micschk>
 */

use SS_HTTPRequest as SS_HTTPRequest;
use Extension as Extension;
use Folder as Folder;

class UploadFieldExtension extends Extension {
	private static $allowed_actions = [
		'index',
	];

	public function beforeCallActionHandler($request, &$action) {
		if($this->owner->hasClass('ss-upload-to-folder'))
			$this->setFolderFromRequest($request);

		if($action == 'upload')
			$action = 'fixedUpload';
	}

	public function index($request) {
		return $this->owner->FieldHolder();
	}

	protected function setFolderFromRequest($request) {
		if(($folder = $this->getFolderFromRequest($request)) && $folder->canView()) {
			$path = strpos($folder->RelativePath, ASSETS_DIR . '/') === 0 ? substr($folder->RelativePath, strlen(ASSETS_DIR . '/')) : $folder->RelativePath;
			$this->owner->FolderName = $path;
		}
	}

	protected function getFolderFromRequest($request) {
		$folderId = $request->getVar('folder');
		return $folderId ? Folder::get()->byID($folderId) : null;
	}

	public function fixedUpload(SS_HTTPRequest $request) {
		// Use a new request that fixes the postVars to use the $_FILES passed in correct order
		if(strpos(trim($this->owner->Name, '[]'), '[') !== false) {
			$request = $this->fixRequestForArrayFields($request);
		}

		return $this->owner->upload($request);
	}

	protected function fixRequestForArrayFields(SS_HTTPRequest $request) {
		$postVars = $request->postVars();
		$fileVars = array_intersect_key($postVars, $_FILES);
		$fieldName = $this->owner->Name;

		foreach($fileVars as $name => $attributes) {
			$nameParts = explode('][', trim(substr($fieldName, strlen($name) + 1), ']'));
			$newAttributes = [];
			$newValue = [];
			$values = null;

			foreach($attributes as $attributeName => $attributeValues) {
				$values = array_get($attributeValues, implode('.', $nameParts));

				if(!$values || (is_array($values) && empty($values)))
					break;

				array_set($newAttributes, implode('.', $nameParts).'.'.$attributeName, $values);
				$newValue[$attributeName] = $values;
			}

			if(!$values)
				continue;

			$postVars[$name] = $newAttributes;
			$postVars[$fieldName] = $newValue;
		}

		return new SS_HTTPRequest(
			$request->httpMethod(),
			$request->getURL(true),
			$request->getVars(),
			$postVars,
			$request->getBody()
		);
	}
} 
