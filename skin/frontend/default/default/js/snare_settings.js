/* General
 ***********************************************************************
 * iovation’s JavaScript is configured through a set of parameters
 * defined in a separate script section before inclusion of snare.js.
 * The following sections contains the basic configuration values
 **********************************************************************/
 
/*
 * io_install_flash
 * Type: boolean, optional
 * Default: true
 * 
 * Indicates whether or not the user will be prompted to install or
 * upgrade Flash when Flash 8 is not installed. If Flash is already
 * installed, this setting has no effect.
 */

var io_install_flash = false;

/* 
 * io_install_stm
 * Type: boolean, optional
 * Default: true
 * 
 * Indicates whether or not the ActiveX ReputationShield™ client should
 * be installed. If the client is already installed, this setting has no
 * effect.
 */

var io_install_stm = false;

/* io_exclude_stm
 * Type: numeric, optional
 * Default: 0
 * 
 * This variable is a bitmask that indicates which Windows versions the
 * Active X control should not run on. This is useful to avoid warnings
 * in Vista or IE 8 when the control is present, but has not been used
 * in the browser before.
 * 
 * The values for the flag are:
 * 
 *  1 – Windows 9x (including Windows 3.1, 95, 98 and Me)
 *  2 – Windows CE
 *  4 – Windows XP (including Windows NT, 2000 and 2003)
 *  8 – Vista
 * 
 * The values are meant to be combined. A value of 12 (4 + 8) indicates
 * that the control should not run on Windows XP or Vista
 */
 
var io_exclude_stm = 12;

/*
 * io_stm_cab_url
 * Type: string, optional
 * Default: iovation hosted location
 * 
 * HTTP location where StmOCX.cab file is hosted. This URL should point
 * to the version of the cab signed with your company certificate.
 * 
 * Include #version=2,8,0,0 in the url to ensure the latest version of
 * the client is installed.
 */
 
//var io_stm_cab_url = null;
 
/* 
 * io_bbout_element_id
 * Type: string, optional
 * Default: none
 * 
 * The id of the HTML element to populate with the blackbox. If
 * io_bb_callback is specified, this parameter will have no effect.
 */

var io_bbout_element_id = 'ioBlackBox';

/* 
 * io_bb_callback
 * Type: function, optional
 * Default: Hidden form field update function
 * 
 * A JavaScript function that will be called as the blackbox is updated.
 * The blackbox will be updated as each component (Flash, JavaScript,
 * etc) finish their collection process.
 * 
 * The function has the following declaration:
 * 
 *   function io_callback( bb, complete)
 *  
 * Where
 * 
 *   bb – the updated value of the blackbox
 *   complete – a Boolean indicating whether all the collection methods
 * have completed.
 */
 
//var io_bb_callback = null;


/* Latency
 ***********************************************************************
 * It is important to note that various ReputationShield™ client
 * components require time to download and run. Since the client is
 * designed to run in the background, if a user prematurely submits the
 * page prior to the client completing, a blackbox may not be generated
 * or be incomplete.
 * 
 * To address these latency issues, configuration variables are provided
 * to specify a delay prior to the form being submitted when the hidden
 * form field collection method is used. These parameters are optional.
 * If specified, the required fields are necessary. If not specified, or
 * improperly configured, there will be no delay in form submission.
 * 
 * If a form is delayed, all form or button event handlers are run after
 * the delay has expired or a blackbox has been generated.
 **********************************************************************/

/*
 * io_max_wait
 * Type: integer, required
 * 
 * Maximum time to wait (in milliseconds) for a blackbox before
 * submitting the form.
 */

//var io_max_wait = 300;

/*
 * io_submit_element_id
 * Type: string, required
 * 
 * The id of an HTML submit element. The onclick event for this element
 * will be overridden to disable the control and wait for a blackbox.
 * When either the timeout expires or the blackbox is complete, the
 * control is re-enabled, and the original onclick event handler is
 * called.
 */

//var io_submit_element_id = 'blackbox';

/*
 * io_submit_form_id
 * Type: string, optional
 * 
 * The id of the HTML form to submit. The onsubmit handler will be
 * overridden to wait for a blackbox before running.
 * 
 * If not populated, the form containing io_submit_element_id is
 * submitted and its onsubmit handler will be delayed.
 */

//var io_submit_form_id = '';

/*
 * io_disabled_image_url
 * Type: string, optional
 * 
 * URL for a disabled button image. Only used when the submit element is
 * an image. The image referenced will be displayed when the user clicks
 * the button to submit the form. If no value is provided, the image is
 * still disabled but not changed.
 */

//var io_disabled_image_url = '';


/* Error handling
 ***********************************************************************
 * Certain error conditions require that the user be notified. snare.js
 * defines two errors that can be handled. Error handlers are specified
 * in the configuration script by assigning JavaScript to the
 * appropriate variables:
 * 
 *   >> io_install_stm_error_handler – the ActiveX ReputationShield™
 * client is not installed. The default behavior for this error is to
 * display an alert indicating the control is required. If
 * io_install_stm is false, this handler will not be run.
 * 
 *   >> io_flash_needs_update_handler – Flash is not installed or the
 * installed version is less than 8.0. The default behavior is to
 * display an alert indicating Flash 7 or higher is required. If
 * io_install_flash is false, this handler will not be run.
 * 
 * NOTE: io_last_error is set to the last error encountered in the
 * script. If things do not appear to be working, you can check the
 * value of this variable to look for errors caught while processing.
 **********************************************************************/

//var io_install_stm_error_handler = null;
//var io_flash_needs_update_handler = null;
