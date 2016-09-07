# SilverStripe Blocks Enhancements (WIP)

*Work in progress!*

This module adds enhancements to the Silverstripe Blocks module, namely:

![image](https://cloud.githubusercontent.com/assets/1005986/13769387/69ccd7d2-ea7f-11e5-833e-24b1102f0bc3.png)
Drag to assign block area, block-preview images, inline-editing

* Visual display/preview of block layouts
* Assign blocks to available block-areas by drag & drop
* Inline editing of blocks, blocktype & content
* Adds a 'Publish + blocks' button to publish all blocks along with the page in one go
* Some other small Ux improvements

## Usage:
* Install as usual (remember to run a 'composer update' as well)
* There's a 'images/BlockIcons.svg' file containing examples of block layout previews. 
* Create a BlockClassName.png image for each BlockClassName in mysite/block_images/

## Installation

#### Composer

	composer require micschk/silverstripe-block_enhancements

Install via composer, *+ run 'composer update'* (seems needed for the select2 module to create web-accessible 'components' dir).

Then run dev/build and see the README of sheadawson/silverstripe-blocks for further instructions

### Requirements (all pulled in by composer)

* SilverStripe CMS ~3.1
* Silverstripe Blocks module + requirements
* (This module contains a slightly modified copie of EditableRow by Milkyway Multimedia, thanks)
