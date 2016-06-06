if (Garmin == undefined) var Garmin = {};
/**
 * Copyright &copy; 2010 Garmin Ltd. or its subsidiaries.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License')
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @fileoverview Garmin.DirectoryUtils - Garmin Directory XML utility functions.
 * @version 1.9
 */
 
/**Utility functions for working with Garmin Directory Listing XML
 * conforming to schema: <a href="http://www.garmin.com/xmlschemas/DirectoryListingV1.xsd">http://www.garmin.com/xmlschemas/DirectoryListingV1.xsd</a>
 * @class Garmin.DirectoryUtils
 * @requires Garmin.File, Garmin.DirectoryFactory
 */
Garmin.DirectoryUtils = function(){};
Garmin.DirectoryUtils = {
   
   /**Merge the contents of two directory xml documents that
    * conform to schema: <a href="http://www.garmin.com/xmlschemas/DirectoryListingV1.xsd">http://www.garmin.com/xmlschemas/DirectoryListingV1.xsd</a>
	* @param {String} aXmlString1 First directory XML string
	* @param {String} aXmlString2 Second directory XML string
	* @return {Document} the result of merging the two directory listing XML strings.
	*/
	merge: function(aXmlString1, aXmlString2) {
							  
		var theFirstDoc = Garmin.XmlConverter.toDocument(aXmlString1);
		var theFirstDir = theFirstDoc.getElementsByTagName(Garmin.DirectoryFactory.SCHEMA_TAGS.directoryListing);
		var theSecondDoc = Garmin.XmlConverter.toDocument(aXmlString2);		
		var theSecondDir = theSecondDoc.getElementsByTagName(Garmin.DirectoryFactory.SCHEMA_TAGS.directoryListing);

		if(theFirstDir.length > 0 && theSecondDir.length > 0) {
			//Grab the file nodes
			var theFileNodes = theSecondDoc.getElementsByTagName(Garmin.DirectoryFactory.SCHEMA_TAGS.file);
			var theDir = theFirstDir[0];
			//loop through all files in the second document
			for (var i = 0; i < theFileNodes.length; ++i) {
				var theFileNode = Try.these(
						function() { return theFirstDoc.importNode(theFileNodes[i],true);},
						function() { return Garmin.DOM._importNode(theFirstDoc,theFileNodes[i],true);} 
						);
				if(!this._dirHasFileNode(theDir, theFileNode)) {
					theDir.appendChild(theFileNode);
				}
			}
		} else {
		  theFirstDoc = null;
		}
		return theFirstDoc;	  
	},						  
	
	/*
	* Detect duplicate file nodes
	* @private
	*/
	_dirHasFileNode: function(aDirNode, aFileNode) {
		var theExists = false;
		var theNodes = aDirNode.getElementsByTagName(Garmin.DirectoryFactory.SCHEMA_TAGS.file);
		var theFilePath = aFileNode.getAttribute(Garmin.File.ATTRIBUTE_KEYS.path).toLowerCase();
		for( var i = 0; i < theNodes.length; ++i )
		{
			var theChildPath = theNodes[i].getAttribute(Garmin.File.ATTRIBUTE_KEYS.path).toLowerCase();
			if(!theChildPath)
			{
				continue;
			}
			if(theChildPath == theFilePath)
			{
				theExists = true;
				break;
			}
		}		
		return theExists;
	},
	
    toString: function() {
        return "[DirectoryUtils]";
    }	
};

/*
 * A List Apart importNode implementation for Internet Explorer
 * Anthony T. Holdener III
 * http://www.alistapart.com/d/crossbrowserscripting/xbImportNode.js
 */
if (!document.ELEMENT_NODE) {
	document.ELEMENT_NODE = 1;
	document.ATTRIBUTE_NODE = 2;
	document.TEXT_NODE = 3;
	document.CDATA_SECTION_NODE = 4;
	document.ENTITY_REFERENCE_NODE = 5;
	document.ENTITY_NODE = 6;
	document.PROCESSING_INSTRUCTION_NODE = 7;
	document.COMMENT_NODE = 8;
	document.DOCUMENT_NODE = 9;
	document.DOCUMENT_TYPE_NODE = 10;
	document.DOCUMENT_FRAGMENT_NODE = 11;
	document.NOTATION_NODE = 12;
}

/*
 * Slightly modified importNode implementation from of A List Apart for Internet Explorer
 * Anthony T. Holdener III
 * http://www.alistapart.com/d/crossbrowserscripting/xbImportNode.js
 * @private
 */
Garmin.DOM = function(){};
Garmin.DOM._importNode = function(aDoc, node, allChildren) {
	/* find the node type to import */
	switch (node.nodeType) {
		case document.ELEMENT_NODE:
			/* create a new element */
			var newNode = aDoc.createElement(node.nodeName);
			/* does the node have any attributes to add? */
			if (node.attributes && node.attributes.length > 0)
				/* add all of the attributes */
				for (var i = 0, il = node.attributes.length; i < il;)
					newNode.setAttribute(node.attributes[i].nodeName, node.getAttribute(node.attributes[i++].nodeName));
			/* are we going after children too, and does the node have any? */
			if (allChildren && node.childNodes && node.childNodes.length > 0)
				/* recursively get all of the child nodes */
				for (var i = 0, il = node.childNodes.length; i < il;)
					newNode.appendChild(Garmin.DOM._importNode(aDoc, node.childNodes[i++], allChildren));
			return newNode;
			break;
		case document.TEXT_NODE:
		case document.CDATA_SECTION_NODE:
		case document.COMMENT_NODE:
			return aDoc.createTextNode(node.nodeValue);
			break;
	}
};