<?php
/**
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

/**
 *
 * @package AAM
 * @author Vasyl Martyniuk <support@wpaam.com>
 * @copyright Copyright C 2013 Vasyl Martyniuk
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class aam_View_Manager extends aam_View_Abstract {

    /**
     *
     * @var type
     */
    private $_cmanager = array();

    /**
     *
     * @var type
     */
    private $_features = array();

    /**
     * Constructor
     *
     * The filter "aam_cpanel" can be used to control the Control Panel items.
     *
     * @return void
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();

        $this->setCManager(
                apply_filters('aam_ui_subjects', $this->getDefaultSubjects())
        );
        $this->setFeatures(
                apply_filters('aam_ui_features', $this->getDefaultFeatures())
        );
    }

    /**
     *
     * @return type
     */
    protected function getDefaultSubjects() {
        return array(
            'roles' => array(
                'position' => 5,
                'segment' => 'role',
                'label' => __('Roles', 'aam'),
                'title' => __('Role Manager', 'aam'),
                'class' => 'manager-item manager-item-role',
                'id' => 'aam_role',
                'content' => array(new aam_View_Role(), 'content')
            ),
            'users' => array(
                'position' => 10,
                'segment' => 'user',
                'label' => __('Users', 'aam'),
                'title' => __('User Manager', 'aam'),
                'class' => 'manager-item manager-item-user',
                'id' => 'aam_user',
                'content' => array(new aam_View_User(), 'content')
            ),
            'visitor' => array(
                'position' => 15,
                'segment' => 'visitor',
                'label' => __('Visitor', 'aam'),
                'title' => __('Visitor Manager', 'aam'),
                'class' => 'manager-item manager-item-visitor',
                'id' => 'aam_visitor',
                'content' => array(new aam_View_Visitor(), 'content')
            )
        );
    }

    /**
     *
     * @return type
     */
    protected function getDefaultFeatures() {
        return array(
            'admin_menu' => array(
                'id' => 'admin_menu',
                'position' => 5,
                'title' => __('Admin Menu', 'aam'),
                'anonimus' => false,
                'content' => array(new aam_View_Menu(), 'content'),
                'help' => __('Control Access to Admin Menu. Restrict access to entire Menu or Submenu. <b>Notice</b>, the menu is rendered based on Role\'s or User\'s capabilities.', 'aam')
            ),
            'metabox' => array(
                'id' => 'metabox',
                'position' => 10,
                'title' => __('Metabox & Widget', 'aam'),
                'anonimus' => true,
                'content' => array(new aam_View_Metabox(), 'content'),
                'help' => __('Filter the list of Metaboxes or Widgets for selected Role or User. If metabox or widget is not listed, try to click <b>Refresh the List</b> button or Copy & Paste direct link to page where specific metabox or widget is shown and hit <b>Retrieve Metaboxes from Link</b> button.', 'aam')
            ),
            'capability' => array(
                'id' => 'capability',
                'position' => 15,
                'title' => __('Capability', 'aam'),
                'anonimus' => false,
                'content' => array(new aam_View_Capability(), 'content'),
                'help' => __('Manage the list of Capabilities for selected User or Role. <b>Notice</b>, list of user\'s capabilities are inherited from user\'s Role.<br/><b>Warning!</b> Be very careful with capabilities. Deleting or unchecking any capability may cause temporary or permanent constrol lost over some features or WordPress dashboard.', 'aam')
            ),
            'post_access' => array(
                'id' => 'post_access',
                'position' => 20,
                'title' => __('Posts & Categories', 'aam'),
                'anonimus' => true,
                'content' => array(new aam_View_Post(), 'content'),
                'help' => __('Manage access to individual <b>Post</b> or <b>Term</b>. Notice, under <b>Post</b>, we assume any post, page or custom post type. And under <b>Term</b> - any term like Post Categories.', 'aam')
            ),
            'event_manager' => array(
                'id' => 'event_manager',
                'position' => 25,
                'title' => __('Event Manager', 'aam'),
                'anonimus' => false,
                'content' => array(new aam_View_Event(), 'content'),
                'help' => __('Define your own action when some event appeared in your WordPress blog. This sections allows you to trigger an action on event like post content change, or page status update. You can setup to send email notification, change the post status or write your own custom event handler.', 'aam')
            ),
            'config_press' => array(
                'id' => 'configpress',
                'position' => 30,
                'title' => __('ConfigPress', 'aam'),
                'anonimus' => true,
                'content' => array(new aam_View_ConfigPress(), 'content'),
                'help' => __('Control <b>AAM</b> behavior with ConfigPress. For more details please check <b>ConfigPress tutorial</b>.', 'aam')
            )
        );
    }

    /**
     * Set Control Panel items
     *
     * @param array $cpanel
     *
     * @return void
     *
     * @access public
     */
    public function setCManager(array $cmanager) {
        $final = array();
        foreach ($cmanager as $item) {
            if (!isset($final[$item['position']])) {
                $final[$item['position']] = $item;
            } else {
                aam_Extension_Console::log(
                        "Control Manager position {$item['position']} reserved already"
                );
            }
        }
        ksort($final);

        $this->_cmanager = $final;
    }

    /**
     * Get Control Panel items
     *
     * @return array
     *
     * @access public
     */
    public function getCManager() {
        return $this->_cmanager;
    }

    /**
     *
     * @param type $list
     */
    public function setFeatures($list) {
        $final = array();
        foreach ($list as $item) {
            if (!isset($final[$item['position']])) {
                $final[$item['position']] = $item;
            } else {
                aam_Extension_Console::log(
                        "Feature position {$item['position']} reserved already"
                );
            }
        }
        ksort($final);

        $this->_features = $final;
    }

    /**
     *
     * @return type
     */
    public function getFeatures() {
        return $this->_features;
    }

    /**
     * Run the Manager
     *
     * @return string
     *
     * @access public
     */
    public function run() {
        return $this->loadTemplate(dirname(__FILE__) . '/tmpl/manager.phtml');
    }

    /**
     *
     * @return type
     */
    public function isVisitor() {
        return ($this->getSubject()->getUID() === 'visitor' ? true : false);
    }

    /**
     *
     */
    public function retrieveFeatures() {
        ?>
        <div class="aam-help">
            <?php
            foreach ($this->getFeatures() as $feature) {
                if (!$this->isVisitor() || $feature['anonimus']) {
                    echo '<span id="feature_help_' . $feature['id'] . '">', $feature['help'], '</span>';
                }
            }
            ?>
        </div>
        <div class="feature-list">
            <?php
            foreach ($this->getFeatures() as $feature) {
                if (!$this->isVisitor() || $feature['anonimus']) {
                    echo '<div class="feature-item" feature="' . $feature['id'] . '">';
                    echo '<span>' . $feature['title'] . '</span></div>';
                }
            }
            ?>
        </div>
        <div class="feature-content">
            <?php
            foreach ($this->getFeatures() as $feature) {
                if (!$this->isVisitor() || $feature['anonimus']) {
                    echo call_user_func($feature['content']);
                }
            }
            ?>
        </div>
        <br class="clear" />
        <?php
        do_action('aam_retrieve_features');
    }

    /**
     *
     * @return type
     */
    public function save() {
        $this->getSubject()->save(
                $this->prepareSaveOptions(aam_Core_Request::post('aam'))
        );
        return json_encode(array('status' => 'success'));
    }

    /**
     *
     * @param type $options
     * @return type
     */
    protected function prepareSaveOptions($options) {
        //make sure that some parts are always in place
        if (!isset($options[aam_Control_Object_Menu::UID])) {
            $options[aam_Control_Object_Menu::UID] = array();
        }
        if (!isset($options[aam_Control_Object_Metabox::UID])) {
            $options[aam_Control_Object_Metabox::UID] = array();
        }
        if (!isset($options[aam_Control_Object_Event::UID])) {
            $options[aam_Control_Object_Event::UID] = array();
        }

        return apply_filters('aam_prepare_option_list', $options);
    }

    /**
     *
     * @return type
     */
    public function roleback() {
        $params = $this->getSubject()->getObject(
                        aam_Control_Object_Backup::UID)->roleback();

        $this->getSubject()->save($this->prepareSaveOptions($params));

        //clear cache
        $this->getSubject()->clearCache();

        return json_encode(
                array(
                    'status' => 'success',
                    'more' => intval(
                            $this->getSubject()->getObject(
                                    aam_Control_Object_Backup::UID)->has()
                    )
                )
        );
    }

    /**
     *
     * @return type
     */
    public function checkRoleback() {
        return json_encode(
                array(
                    'status' => intval($this->getSubject()->getObject(
                                    aam_Control_Object_Backup::UID)->has()
                    )
                )
        );
    }

    /**
     *
     * @return type
     */
    public static function uiLabels(){
        return apply_filters('aam_localization_labels', array(
            'Rollback Settings' => __('Rollback Settings', 'aam'),
            'Cancel' => __('Cancel', 'aam'),
            'Send E-mail' => __('Send E-mail', 'aam'),
            'Add New Role' => __('Add New Role', 'aam'),
            'Manage' => __('Manage', 'aam'),
            'Edit' => __('Edit', 'aam'),
            'Delete' => __('Delete', 'aam'),
            'Filtered' => __('Filtered', 'aam'),
            'Clear' => __('Clear', 'aam'),
            'Add New Role' => __('Add New Role', 'aam'),
            'Save Changes' => __('Save Changes', 'aam'),
            'Delete Role with Users Message' => __('System detected %d user(s) with this role. All Users with Role <b>%s</b> will be deleted automatically!', 'aam'),
            'Delete Role Message' => __('Are you sure that you want to delete role <b>%s</b>?', 'aam'),
            'Delete Role' => __('Delete Role', 'aam'),
            'Add User' => __('Add User', 'aam'),
            'Filter Users' => __('Filter Users', 'aam'),
            'Refresh List' => __('Refresh List', 'aam'),
            'Block' => __('Block', 'aam'),
            'Delete User Message' => __('Are you sure you want to delete user <b>%s</b>?', 'aam'),
            'Filter Capabilities by Category' => __('Filter Capabilities by Category', 'aam'),
            'Inherit Capabilities' => __('Inherit Capabilities', 'aam'),
            'Add New Capability' => __('Add New Capability', 'aam'),
            'Delete Capability Message' => __('Are you sure that you want to delete capability <b>%s</b>?', 'aam'),
            'Delete Capability' => __('Delete Capability', 'aam'),
            'Select Role' => __('Select Role', 'aam'),
            'Add Capability' => __('Add Capability', 'aam'),
            'Add Event' => __('Add Event', 'aam'),
            'Edit Event' => __('Edit Event', 'aam'),
            'Delete Event' => __('Delete Event', 'aam'),
            'Save Event' => __('Save Event', 'aam'),
            'Delete Event' => __('Delete Event', 'aam'),
            'Filter Posts by Post Type' => __('Filter Posts by Post Type', 'aam'),
            'Refresh List' => __('Refresh List', 'aam'),
            'Restore Default' => __('Restore Default', 'aam'),
            'Apply' => __('Apply', 'aam'),
            'Edit Term' => __('Edit Term', 'aam'),
            'Manager Access' => __('Manager Access', 'aam'),
            'Unlock Default Accesss Control' => __('Unlock Default Accesss Control', 'aam'),
            'Close' => __('Close', 'aam'),
            'Edit Role' => __('Edit Role', 'aam'),
            'Restore Default Capabilities' => __('Restore Default Capabilities', 'aam')
        ));
    }

}