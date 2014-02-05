<?php
/**
  Plugin Name: Advanced Access Manager
  Description: Manage User and Role Access to WordPress Backend and Frontend.
  Version: 2.1.1
  Author: Vasyl Martyniuk <support@wpaam.com>
  Author URI: http://www.wpaam.com

 *
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

require(dirname(__FILE__) . '/config.php');

/**
 * Main Plugin Class
 *
 * Responsible for initialization and handling user requests to Advanced Access
 * Manager
 *
 * @package AAM
 * @author Vasyl Martyniuk <support@wpaam.com>
 * @copyright Copyright C 2013 Vasyl Martyniuk
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class aam {

    /**
     * Single instance of itself
     *
     * @var aam
     *
     * @access private
     */
    private static $_aam = null;

    /**
     * User Subject
     *
     * @var aam_Control_Subject_User
     *
     * @access private
     */
    private $_user = null;

    /**
     * Initialize the AAM Object
     *
     * @return void
     *
     * @access protected
     */
    protected function __construct() {
        //use some internal hooks to extend functionality
        add_filter('aam_access_objects', array($this, 'internalHooks'), 1, 2);

        //initialize the user subject
        $this->initializeUser();

        if (is_admin()) {
            //check if system requires update
            $this->checkUpdate();

            //print required JS & CSS
            add_action('admin_print_scripts', array($this, 'printScripts'));
            add_action('admin_print_styles', array($this, 'printStyles'));

            //manager Admin Menu
            if (aam_Core_API::isNetworkPanel()) {
                add_action('network_admin_menu', array($this, 'adminMenu'), 999);
            } else {
                add_action('admin_menu', array($this, 'adminMenu'), 999);
            }
            //manager AAM Features Content rendering
            add_action('admin_action_features', array($this, 'features'));
            //manager AAM Ajax Requests
            add_action('wp_ajax_aam', array($this, 'ajax'));
            //manager WordPress metaboxes
            add_action("in_admin_header", array($this, 'metaboxes'), 999);
            //manager user search and authentication control
            add_filter('user_search_columns', array($this, 'searchColumns'));
            //terms & post restriction handlers
            add_filter('get_terms', array($this, 'getBackendTerms'), 10, 3);
            //post restrictions
            add_action('post_updated', array($this, 'postUpdate'), 10, 3);
            add_filter('page_row_actions', array($this, 'postRowActions'), 10, 2);
            add_filter('post_row_actions', array($this, 'postRowActions'), 10, 2);
            add_filter('tag_row_actions', array($this, 'tagRowActions'), 10, 2);
            add_action('admin_action_edit', array($this, 'adminActionEdit'), 10);
            //wp die hook
            add_filter('wp_die_handler', array($this, 'wpDie'), 10);
            //***For UI purposes***
            add_action('parse_tax_query', array($this, 'parseTaxQuery'), 10, 1);
        } else {
            //control WordPress frontend
            add_action('wp', array($this, 'wp'), 999);
            //filter navigation pages & taxonomies
            add_filter('get_pages', array($this, 'getPages'));
            add_filter('wp_get_nav_menu_items', array($this, 'getNavigationMenu'));
            //widget filters
            add_filter('sidebars_widgets', array($this, 'widgetFilter'), 999);
            //get control over commenting stuff
            add_filter('comments_open', array($this, 'commentOpen'), 10, 2);
            //user login control
            add_filter('wp_authenticate_user', array($this, 'authenticate'), 1, 2);
            //terms & post restriction handlers
            add_filter('get_terms', array($this, 'getFrontendTerms'), 10, 3);
        }

        //load extensions only when admin
        $this->loadExtensions();

        //add shutdown action
        add_action('shutdown', array($this, 'shutdown'), 1);
    }

    /**
     * Check if system requires update
     *
     * @return void
     *
     * @access public
     */
    public function checkUpdate() {
        if (aam_Core_API::getBlogOption('aam_updated', '', 1) != AAM_VERSION) {
            $update = new aam_Core_Update($this);
            $update->run();
        }
    }

    /**
     * Control Frontend commenting freature
     *
     * @param boolean $open
     * @param int $post_id
     *
     * @return boolean
     *
     * @access public
     */
    public function commentOpen($open, $post_id) {
        $control = $this->getUser()->getObject(
                aam_Control_Object_Post::UID, $post_id
        );
        if ($control->has('frontend', aam_Control_Object_Post::ACTION_COMMENT)) {
            $open = false;
        }

        return $open;
    }

    /**
     * Get Post ID
     *
     * Replication of the same mechanism that is in wp-admin/post.php
     *
     * @return WP_Post|null
     *
     * @access public
     */
    public function getPost() {
        if (get_post()) {
            $post = get_post();
        } elseif ($post_id = aam_Core_Request::get('post')) {
            $post = get_post($post_id);
        } elseif ($post_id = aam_Core_Request::get('post_ID')) {
            $post = get_post($post_id);
        } else {
            $post = null;
        }

        return $post;
    }

    /**
     * Filter backend term list
     *
     * @param array $terms
     * @param array $taxonomies
     * @param array $args
     *
     * @return array
     *
     * @access public
     */
    public function getBackendTerms($terms, $taxonomies, $args) {
        return $this->getTerms('backend', $terms);
    }

    /**
     * Filter frontend term list
     *
     * @param array $terms
     * @param array $taxonomies
     * @param array $args
     *
     * @return array
     *
     * @access public
     */
    public function getFrontendTerms($terms, $taxonomies, $args) {
        return $this->getTerms('frontend', $terms);
    }

    /**
     * Filter terms based on area
     *
     * @param string $area
     * @param array $terms
     *
     * @return array
     *
     * @access public
     */
    public function getTerms($area, $terms) {
        if (is_array($terms)) {
            foreach ($terms as $i => $term) {
                if (is_object($term)) {
                    $object = $this->getUser()->getObject(
                        aam_Control_Object_Term::UID, $term->term_id
                    );
                    if ($object->has($area, aam_Control_Object_Term::ACTION_LIST)) {
                        unset($terms[$i]);
                    }
                }
            }
        }

        return $terms;
    }

    /**
     * Filter Pages that should be excluded in frontend
     *
     * @param array $pages
     *
     * @return array
     *
     * @access public
     * @todo Cache this process
     */
    public function getPages($pages){
        if (is_array($pages)){
            foreach($pages as $i => $page){
                $object = $this->getUser()->getObject(
                    aam_Control_Object_Post::UID, $page->ID
                );
                if ($object->has('frontend', aam_Control_Object_Post::ACTION_EXCLUDE)){
                    unset($pages[$i]);
                }
            }
        }

        return $pages;
    }

    /**
     * Filter Navigation menu
     *
     * @param array $pages
     *
     * @return array
     *
     * @access public
     */
    public function getNavigationMenu($pages){
        if (is_array($pages)){
            foreach($pages as $i => $page){
                if ($page->type === 'taxonomy'){
                    $object = $this->getUser()->getObject(
                        aam_Control_Object_Term::UID, $page->object_id
                    );
                    $exclude = aam_Control_Object_Term::ACTION_EXCLUDE;
                } else {
                    $object = $this->getUser()->getObject(
                        aam_Control_Object_Post::UID, $page->object_id
                    );
                    $exclude = aam_Control_Object_Post::ACTION_EXCLUDE;
                }

                if ($object->has('frontend', $exclude)){
                    unset($pages[$i]);
                }
            }
        }

        return $pages;
    }

    /**
     * Filter Frontend widgets
     *
     * @param array $widgets
     *
     * @return array
     *
     * @access public
     */
    public function widgetFilter($widgets) {
        return $this->getUser()->getObject(
                        aam_Control_Object_Metabox::UID)->filterFrontend($widgets);
    }

    /**
     * Control Edit Post/Term
     *
     * Make sure that current user does not have access to edit Post or Term
     *
     * @return void
     *
     * @access public
     */
    public function adminActionEdit() {
        $user = $this->getUser();
        if (aam_Core_Request::request('taxonomy')) {
            $control = $user->getObject(
                    aam_Control_Object_Term::UID, aam_Core_Request::request('tag_ID')
            );
            if ($control->has('backend', aam_Control_Object_Post::ACTION_EDIT)) {
                $this->reject();
            }
        } elseif ($post = $this->getPost()) {
            $control = $user->getObject(aam_Control_Object_Post::UID, $post->ID);
            if ($control->has('backend', aam_Control_Object_Post::ACTION_EDIT)) {
                $this->reject();
            }
        }
    }

    /**
     * Reject the request
     *
     * Redirect or die the execution based on ConfigPress settings
     *
     * @return void
     *
     * @access public
     */
    public function reject() {
        $cpress = $this->getUser()->getObject(aam_Control_Object_ConfigPress::UID);
        if (is_admin()) {
            $redirect = $cpress->getParam('backend.access.deny.redirect');
            $message = $cpress->getParam(
                    'backend.access.deny.message', __('Access denied', 'aam')
            );
        } else {
            $redirect = $cpress->getParam('frontend.access.deny.redirect');
            $message = $cpress->getParam(
                    'frontend.access.deny.message', __('Access denied', 'aam')
            );
        }

        if (filter_var($redirect, FILTER_VALIDATE_URL)) {
            wp_redirect($redirect);
            exit;
        } elseif (is_int($redirect)) {
            wp_redirect(get_post_permalink($redirect));
            exit;
        } else {
            wp_die($message);
        }
    }

    /**
     * Take control over wp_die function
     *
     * @param callback $function
     *
     * @return void
     *
     * @access public
     */
    public function wpDie($function) {
        $cpress = $this->getUser()->getObject(aam_Control_Object_ConfigPress::UID);
        $redirect = $cpress->getParam('backend.access.deny.redirect');
        $message = $cpress->getParam(
                'backend.access.deny.message', __('Access denied', 'aam')
        );

        if (filter_var($redirect, FILTER_VALIDATE_URL)) {
            wp_redirect($redirect);
            exit;
        } elseif (is_int($redirect)) {
            wp_redirect(get_post_permalink($redirect));
            exit;
        } else {
            call_user_func($function, $message, '', array());
        }
    }

    /**
     * Term Quick Menu Actions Filtering
     *
     * @param array $actions
     * @param object $term
     *
     * @return array
     *
     * @access public
     */
    public function tagRowActions($actions, $term) {
        $control = $this->getUser()->getObject(
                aam_Control_Object_Term::UID, $term->term_id
        );
        //filter edit menu
        if ($control->has('backend', aam_Control_Object_Post::ACTION_EDIT)) {
            if (isset($actions['edit'])) {
                unset($actions['edit']);
            }
            if (isset($actions['inline hide-if-no-js'])) {
                unset($actions['inline hide-if-no-js']);
            }
        }

        //filter delete menu
        if ($control->has('backend', aam_Control_Object_Post::ACTION_DELETE)) {
            if (isset($actions['delete'])) {
                unset($actions['delete']);
            }
        }

        return $actions;
    }

    /**
     * Post Quick Menu Actions Filtering
     *
     * @param array $actions
     * @param WP_Post $post
     *
     * @return array
     *
     * @access public
     */
    public function postRowActions($actions, $post) {
        $control = $this->getUser()->getObject(
                aam_Control_Object_Post::UID, $post->ID
        );
        //filter edit menu
        if ($control->has('backend', aam_Control_Object_Post::ACTION_EDIT)) {
            if (isset($actions['edit'])) {
                unset($actions['edit']);
            }
            if (isset($actions['inline hide-if-no-js'])) {
                unset($actions['inline hide-if-no-js']);
            }
        }
        //filter trash menu
        if ($control->has('backend', aam_Control_Object_Post::ACTION_TRASH)) {
            if (isset($actions['trash'])) {
                unset($actions['trash']);
            }
        }

        //filter delete menu
        if ($control->has('backend', aam_Control_Object_Post::ACTION_DELETE)) {
            if (isset($actions['delete'])) {
                unset($actions['delete']);
            }
        }

        return $actions;
    }

    /**
     * Main Frontend access control hook
     *
     * @return void
     *
     * @access public
     * @global WP_Query $wp_query
     * @global WP_Post $post
     */
    public function wp() {
        global $wp_query, $post;

        $user = $this->getUser();
        if (is_category()) {
            $category = $wp_query->get_queried_object();
            if ($user->getObject(aam_Control_Object_Term::UID, $category->term_id
                    )->has('frontend', aam_Control_Object_Term::ACTION_BROWSE)) {
                $this->reject();
            }
        } elseif (!$wp_query->is_home() && ($post instanceof WP_Post)) {
            if ($user->getObject(aam_Control_Object_Post::UID, $post->ID
                    )->has('frontend', aam_Control_Object_Post::ACTION_READ)) {
                $this->reject();
            }
        }
    }

    /**
     * Register Internal miscellenious functionality
     *
     * @param array               $objects
     * @param aam_Control_Subject $subject
     *
     * @return array
     *
     * @access public
     */
    public function internalHooks($objects, $subject) {
        $objects[aam_Control_Object_Event::UID] = new aam_Control_Object_Event(
                $subject
        );

        $configPress = new aam_Control_Object_ConfigPress($subject, 1);
        $objects[aam_Control_Object_ConfigPress::UID] = $configPress;

        return $objects;
    }

    /**
     * Event Handler
     *
     * @param int $post_ID
     * @param WP_Post $post_after
     * @param WP_Post $post_before
     *
     * @return void
     *
     * @access public
     */
    public function postUpdate($post_ID, $post_after, $post_before = null) {
        $events = $this->getUser()->getObject(
                        aam_Control_Object_Event::UID)->getOption();

        foreach ($events as $event) {
            if ($post_after->post_type == $event['post_type']) {
                if ($event['event'] == 'status_change') {
                    if ($event['event_specifier'] == $post_after->post_status) {
                        $this->triggerAction(
                                $event, $post_ID, $post_after, $post_before
                        );
                    }
                } elseif ($post_before && $event['event'] == 'content_change') {
                    if ($post_before->post_content != $post_after->post_content) {
                        $this->triggerAction(
                                $event, $post_ID, $post_after, $post_before
                        );
                    }
                }
            }
        }
    }

    /**
     * Trigger Action based on settings
     *
     * @param array   $event
     * @param int     $post_ID
     * @param WP_Post $post_after
     * @param WP_Post $post_before
     *
     * @global wpdb  $wpdb
     * @global array $wp_post_types
     *
     * @return void
     *
     * @access public
     */
    public function triggerAction($event, $post_ID, $post_after, $post_before) {
        global $wpdb, $wp_post_types;

        if ($event['action'] == 'notify') {
            $subject = $wp_post_types[$event['post_type']]->labels->name . ' ';
            $subject .= $post_ID . ' has been changed by ' . get_current_user();
            $subject = apply_filters('aam_notification_subject', $subject);

            $message = apply_filters(
                    'aam_notification_message', get_edit_post_link($post_ID)
            );

            wp_mail($event['action_specifier'], $subject, $message);
        } else if ($event['action'] == 'change_status') {
            $wpdb->update(
                    $wpdb->posts,
                    array('post_status' => $event['action_specifier']),
                    array('ID' => $post_ID)
            );
        } else if ($event['action'] == 'custom') {
            if (is_callable($event['callback'])) {
                call_user_func(
                        $event['callback'], $post_ID, $post_after, $post_before
                );
            }
        }
    }

    /**
     * Add extra column to search in for User search
     *
     * @param array $columns
     *
     * @return array
     *
     * @access public
     */
    public function searchColumns($columns) {
        $columns[] = 'display_name';

        return $columns;
    }

    /**
     * Control User Block flag
     *
     * @param WP_Error $user
     *
     * @return WP_Error|WP_User
     *
     * @access public
     */
    public function authenticate($user) {
        if ($user->user_status == 1) {
            $user = new WP_Error();
            $user->add(
                    'authentication_failed', '<strong>ERROR</strong>: User is blocked'
            );
        }

        return $user;
    }

    /**
     * Make sure that AAM UI Page is used
     *
     * @return boolean
     *
     * @access public
     */
    public function isAAMScreen() {
        return (aam_Core_Request::get('page') == 'aam' ? true : false);
    }

    /**
     * Make sure that AAM Extension UI Page is used
     *
     * @return boolean
     *
     * @access public
     */
    public function isAAMExtensionScreen() {
        return (aam_Core_Request::get('page') == 'aam-ext' ? true : false);
    }

    /**
     * Print necessary styles
     *
     * @return void
     *
     * @access public
     */
    public function printStyles() {
        if ($this->isAAMScreen()) {
            wp_enqueue_style('dashboard');
            wp_enqueue_style('global');
            wp_enqueue_style('wp-admin');
            wp_enqueue_style('aam-ui-style', AAM_MEDIA_URL . 'css/jquery-ui.css');
            wp_enqueue_style('aam-style', AAM_MEDIA_URL . 'css/aam.css');
            wp_enqueue_style('aam-datatables', AAM_MEDIA_URL . 'css/jquery.dt.css');
            wp_enqueue_style('aam-codemirror', AAM_MEDIA_URL . 'css/codemirror.css');
            wp_enqueue_style(
                    'aam-treeview', AAM_MEDIA_URL . 'css/jquery.treeview.css'
            );
        } elseif ($this->isAAMExtensionScreen()) {
            wp_enqueue_style('dashboard');
            wp_enqueue_style('global');
            wp_enqueue_style('wp-admin');
            wp_enqueue_style('aam-ui-style', AAM_MEDIA_URL . 'css/jquery-ui.css');
            wp_enqueue_style('aam-style', AAM_MEDIA_URL . 'css/extension.css');
            wp_enqueue_style('aam-datatables', AAM_MEDIA_URL . 'css/jquery.dt.css');
        }

        //migration functionality. TODO - remove in July 15 2014
        if (class_exists('aam_Core_Migrate')){
            wp_enqueue_style('aam-migrate', AAM_MEDIA_URL . 'css/migrate.css');
        }
    }

    /**
     * Print necessary scripts
     *
     * @return void
     *
     * @access public
     */
    public function printScripts() {
        if ($this->isAAMScreen()) {
            wp_enqueue_script('postbox');
            wp_enqueue_script('dashboard');
            wp_enqueue_script('aam-admin', AAM_MEDIA_URL . 'js/aam.js');
            wp_enqueue_script('aam-datatables', AAM_MEDIA_URL . 'js/jquery.dt.js');
            wp_enqueue_script('aam-codemirror', AAM_MEDIA_URL . 'js/codemirror.js');
            wp_enqueue_script('aam-cmini', AAM_MEDIA_URL . 'js/properties.js');
            wp_enqueue_script(
                    'aam-treeview', AAM_MEDIA_URL . 'js/jquery.treeview.js'
            );
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-effects-core');
            wp_enqueue_script('jquery-ui-widget');
            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_script('jquery-ui-accordion');
            wp_enqueue_script('jquery-ui-progressbar');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script('jquery-ui-button');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-menu');
            wp_enqueue_script('jquery-effects-highlight');

            $localization = array(
                'nonce' => wp_create_nonce('aam_ajax'),
                'siteURI' => admin_url('index.php'),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'addUserURI' => admin_url('user-new.php'),
                'editUserURI' => admin_url('user-edit.php'),
                'defaultSegment' => array(
                    'role' => 'administrator',
                    'blog' => get_current_blog_id(),
                    'user' => 0
                ),
                'labels' => aam_View_Manager::uiLabels()
            );
            wp_localize_script('aam-admin', 'aamLocal', $localization);
        } elseif ($this->isAAMExtensionScreen()) {
            wp_enqueue_script('postbox');
            wp_enqueue_script('dashboard');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-effects-core');
            wp_enqueue_script('jquery-ui-widget');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script('jquery-ui-button');
            wp_enqueue_script('jquery-effects-highlight');
            wp_enqueue_script('aam-admin', AAM_MEDIA_URL . 'js/extension.js');
            wp_enqueue_script('aam-datatables', AAM_MEDIA_URL . 'js/jquery.dt.js');

            $localization = array(
                'nonce' => wp_create_nonce('aam_ajax'),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'labels' => aam_View_Manager::uiLabels()
            );
            wp_localize_script('aam-admin', 'aamLocal', $localization);
        }

        //migration functionality. TODO - remove in July 15 2014
        if (class_exists('aam_Core_Migrate')){
            wp_enqueue_script('aam-migrate', AAM_MEDIA_URL . 'js/migrate.js');
            $localization = array(
                'nonce' => wp_create_nonce('aam_ajax'),
                'ajaxurl' => admin_url('admin-ajax.php'),
            );
            wp_localize_script('aam-migrate', 'aamMigrateLocal', $localization);
        }
    }

    /**
     * Render list of AAM Features
     *
     * Must be separate from Ajax call because WordPress ajax does not load a lot of
     * UI stuff
     *
     * @return void
     *
     * @access public
     */
    public function features() {
        check_ajax_referer('aam_ajax');

        $model = new aam_View_Manager;
        $model->retrieveFeatures();
        die();
    }

    /**
     * Handle Ajax calls to AAM
     *
     * @return void
     *
     * @access public
     */
    public function ajax() {
        check_ajax_referer('aam_ajax');

        //clean buffer to make sure that nothing messing around with system
        while (@ob_end_clean());

        //process ajax request
        $model = new aam_View_Ajax;
        echo $model->run();
        die();
    }

    /**
     * Hanlde Metabox initialization process
     *
     * @return void
     *
     * @access public
     */
    public function metaboxes() {
        global $post;

        //make sure that nobody is playing with screen options
        if ($post instanceof WP_Post){
            $screen = $post->post_type;
        } elseif($screen_object = get_current_screen()) {
            $screen = $screen_object->id;
        } else {
            $screen = '';
        }

        if (aam_Core_Request::get('aam_meta_init')) {
            $model = new aam_View_Metabox;
            $model->run($screen);
        } else {
             $this->getUser()->getObject(aam_Control_Object_Metabox::UID)
                                            ->filterBackend($screen);
        }
    }

    /**
     * Register Admin Menu
     *
     * @return void
     *
     * @access public
     */
    public function adminMenu() {
        //register the menu
        add_menu_page(
                __('AAM', 'aam'),
                __('AAM', 'aam'),
                'administrator',
                'aam',
                array($this, 'content'),
                AAM_BASE_URL . 'active-menu.png'
        );
        //register submenus
        add_submenu_page(
                'aam',
                __('Access Control', 'aam'),
                __('Access Control', 'aam'),
                'administrator',
                'aam',
                array($this, 'content')
        );
        add_submenu_page(
                'aam',
                __('Extensions', 'aam'),
                __('Extensions', 'aam'),
                'administrator',
                'aam-ext',
                array($this, 'extensionContent')
        );

        //filter admin menu
        $this->getUser()->getObject(aam_Control_Object_Menu::UID)->filter();
    }
    
    /**
     * Take control over Tax Query parser
     * 
     * By default WordPress consider non-empty term & category pair as search by
     * slug. This is weird assumption and there is no other way to force core to
     * search posts within custom taxonomy rather than take control over it with
     * action parse_tax_query.
     * 
     * @param WP_Query $query
     * 
     * @return void
     * 
     * @access public
     */
    public function parseTaxQuery($query){
        if (!empty($query->query['term']) && !empty($query->query['taxonomy'])){
            foreach($query->tax_query->queries as $id => $dump){
                $query->tax_query->queries[$id]['field'] = 'term_id';
            }
        }
        
    }

    /**
     * Render Main Content page
     *
     * @return void
     *
     * @access public
     */
    public function content() {
        $manager = new aam_View_Manager();
        echo $manager->run();
    }

    /**
     * Extension content page
     *
     * @return void
     *
     * @access public
     */
    public function extensionContent() {
        $manager = new aam_View_Extension();
        echo $manager->run();
    }

    /**
     * Initialize the AAM plugin
     *
     * @return void
     *
     * @access public
     * @static
     */
    public static function initialize() {
        if (is_null(self::$_aam)) {
            self::$_aam = new self;
        }
    }

    /**
     * Initialize the current user
     *
     * Whether it is logged in user or visitor
     *
     * @return void
     *
     * @access public
     */
    public function initializeUser() {
        if ($user_id = get_current_user_id()) {
            $this->setUser(new aam_Control_Subject_User($user_id));
        } else {
            $this->setUser(new aam_Control_Subject_Visitor(''));
        }
    }

    /**
     * Uninstall hook
     *
     * Remove all leftovers from AAM execution
     *
     * @return void
     *
     * @access public
     */
    public static function uninstall() {
        global $wp_filesystem;

        //remove the content directory
        if (!defined(AAM_CONTENT_DIR_FAILURE) && WP_Filesystem()) {
            $wp_filesystem->rmdir(AAM_TEMP_DIR, true);
        }
    }

    /**
     * Get Current User Subject
     *
     * @return aam_Control_Subject_User
     *
     * @access public
     */
    public function getUser() {
        return $this->_user;
    }

    /**
     * Set Current User Subject
     *
     * @param aam_Control_Subject $user
     *
     * @return void
     *
     * @access public
     */
    public function setUser(aam_Control_Subject $user) {
        $this->_user = $user;
    }

    /**
     * Execute before shutdown actions
     *
     * @return void
     *
     * @access public
     */
    public function shutdown(){
        $this->getUser()->saveCache();
    }

    /**
     * Load Installed extensions
     *
     * @return void
     *
     * @access protected
     */
    protected function loadExtensions() {
        $model = new aam_Core_Extension($this);
        $model->load();
    }

}

add_action('init', 'aam::initialize');

//register_activation_hook(__FILE__, array('aam', 'activate'));
register_uninstall_hook(__FILE__, array('aam', 'uninstall'));