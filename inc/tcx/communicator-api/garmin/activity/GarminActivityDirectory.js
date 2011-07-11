if (Garmin == undefined) var Garmin = {};
/** The list of activities on the device, including additional information
 * associated with each activity related to state, such as:
 * 
 * - whether it failed/succeeded upload
 * - the input checkbox ID for the table
 * - etc.
 * 
 * The internal object is an array of Garmin.ActivityDirectory.Entry objects.
 * The array is in descending order--order shouldn't matter though since you 
 * are accessing objects by ID anyway, like a hash.
 * @author Diana Chow diana.chow.at.garmin.com
 * @version 1.0
 * @class Garmin.ActivityDirectory
 */ 
Garmin.ActivityDirectory = function(){}; //just here for jsdoc
Garmin.ActivityDirectory = Class.create({
    initialize: function() {
        this.entries = new Array();
    },
    
    /** Add an entry to the directory by activity ID.
     */
    addEntry: function(activityId, name, duration, displayElementId) {
        var entry = new Garmin.ActivityDirectory.Entry(activityId, name, duration, null, displayElementId);
        this.entries.push(entry);
        return entry;
    },
    
     /** Get the Garmin.ActivityDirectory.Entry object for the given activity ID.
     * Null if not found.
     */
    getEntry: function(activityId) {
        var entrizzle = null;
        for(var i=0; i < this.entries.length; i++) {
            if( this.entries[i].id == activityId) {
                entrizzle = this.entries[i];
            }
        }
        return entrizzle;
    },
    
    /** Returns the array of entries in the same order they were initialized in.
     */
    getEntries: function() {
        return this.entries;
    },
    
    /** Returns the first entry in the directory. 
     */
    getFirstEntry: function() {
        return this.entries[0];
    },
    
    /** Get the number of activities that failed upload.
     */
    getFailureCount: function() {
        var failureCount = 0;
        for(var i=0; i < this.entries.length; i++) {
            if( this.entries[i].successfulUpload == false) {
                failureCount++;
            }
        }
        return failureCount;
    },
    
    /** Returns a list (array) of just the IDs.
     */
    getIds: function() {
        var idList = new Array();
        for( var i=0; i < this.entries.length; i++) {
            idList.push(this.entries[i].id);
        }
        return idList;        
    },
    
    /** Get the number of activities that succeeded upload.
     */
    getSuccessCount: function() {
        var successCount = 0;
        for(var i=0; i < this.entries.length; i++) {
            if( this.entries[i].successfulUpload == true) {
                successCount++;
            }
        }
        return successCount;
    },
    
    /** Indicate that an activity failed upload.
     */
    setFailed: function(activityId) {
        if( this.getEntry(activityId) != null) { 
            this.getEntry(activityId).successfulUpload = false;
        }
    },
    
    /** Indicate that an activity succeeded upload.
     */
    setSuccess: function(activityId) {
        if( this.getEntry(activityId) != null) { 
            this.getEntry(activityId).successfulUpload = true;
        }
    },
    
    /** Get the number of activities in the directory. 
     */
    size: function() {
        return this.entries ? this.entries.length : 0; 
    }
});

/** An activity entry in the directory.
 *  Only the activity ID is required. The rest are optional.
 */
Garmin.ActivityDirectory.Entry = Class.create({
    initialize: function(activityId, name, duration, successfulUpload, displayElementId) {
        this.id = activityId;
        this.name = name;
        this.duration = duration;
        this.successfulUpload = successfulUpload;
        this.displayElementId = displayElementId;
        this.path = null;
    }
});

