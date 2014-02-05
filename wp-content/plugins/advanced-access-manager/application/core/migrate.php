<?php

/**
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

/**
 * Migrate the setting to 2.x version
 *
 * @package AAM
 * @author Vasyl Martyniuk <support@wpaam.com>
 * @copyright Copyright C 2013 Vasyl Martyniuk
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
final class aam_Core_Migrate {

    /**
     * Run the migration process
     *
     * @return string
     *
     * @access public
     */
    public function run() {
        @set_time_limit(300);

        ob_start(); //prevent from any kind of notice, warning printout
        switch (aam_Core_Request::post('step')) {
            case 'collect':
                $response = $this->collect();
                break;

            case 'migrate':
                $response = $this->migrate();
                break;

            case 'cleanup':
                $response = $this->cleanup();
                break;

            case 'complete':
                $response = $this->complete();
                break;

            default:
                $response = array('status' => 'failure');
                break;
        }
        ob_end_clean();

        return json_encode($response);
    }

    /**
     * Collect the list of all setting for migration
     *
     * @global type $wpdb
     * @return string
     */
    protected function collect() {
        global $wpdb;

        $collection = array(
            'users' => array(),
            'roles' => array()
        );
        aam_Core_API::deleteBlogOption('aam_migration_cache');

        //====== collect all user individual settings ======
        $query = 'SELECT * FROM ' . $wpdb->usermeta;
        $query .= ' WHERE meta_key = "wpaccess_config"';
        $user_list = $wpdb->get_results($query);
        if (count($user_list)) {
            foreach ($user_list as $user) {
                $collection['users'][$user->user_id] = $user->meta_value;
            }
        }

        //====== collect all roles settings =======
        if (is_multisite()) {
            //get all sites first and iterate through each
            $query = 'SELECT blog_id FROM ' . $wpdb->blogs;
            $blog_list = $wpdb->get_results($query);
            if (is_array($blog_list)) {
                foreach ($blog_list as $blog) {
                    $collection['roles'][$blog->blog_id] = $this->getRoleSet(
                        $blog->blog_id
                    );
                }
            }
        } else {
            $collection['roles'] = $this->getRoleSet();
        }

        //save the cache for wether migration process
        if (aam_Core_API::updateBlogOption('aam_migration_cache', $collection, 1)) {
            $response = array('status' => 'success', 'stop' => 1);
        } else {
            $response = array('status' => 'failure');
        }

        return $response;
    }

    protected function getRoleSet($blog_id = 1) {
        global $wpdb;

        $role_set = array();
        $blog_prefix = $wpdb->get_blog_prefix($blog_id);
        //get list of roles for current blog
        $query = 'SELECT * FROM ' . $blog_prefix . 'options ';
        $query .= 'WHERE option_name = "' . $blog_prefix . 'user_roles"';
        if ($row = $wpdb->get_row($query)) {
            $user_roles = unserialize($row->option_value);
            if (is_array($user_roles)) {
                foreach ($user_roles as $role => $data) {
                    $query = 'SELECT option_value FROM ' . $blog_prefix . 'options ';
                    $query .= 'WHERE option_name = "' . $blog_prefix . 'wpaccess_config_' . $role . '"';
                    $role_set[$role] = $wpdb->get_var($query);
                }
            }
        }

        return $role_set;
    }

    protected function migrate() {
        $response = array('status' => 'success', 'stop' => 1);
        //get the settings
        $collection = aam_Core_API::getBlogOption('aam_migration_cache', 1);

        if (is_array($collection)) {
            if (isset($collection['users'])) {
                $this->migrateUsers($collection);
            }

            if (isset($collection['roles'])) {
                $this->migrateRoles($collection);
            }
        }

        return $response;
    }

    protected function migrateSettings($settings) {
        $migrated = array();
        $legacy = unserialize($settings);
        if ($legacy instanceof stdClass) {
            if (isset($legacy->menu)) {
                $migrated['menu'] = $this->migrateMenu($legacy->menu);
            }
            if (isset($legacy->metaboxes)) {
                $migrated['metabox'] = $this->migrateMetaboxes(
                        $legacy->metaboxes
                );
            }
            if (isset($legacy->capabilities)) {
                $migrated['capability'] = $this->migrateCapabilities(
                        $legacy->capabilities
                );
            }
            if (isset($legacy->restrictions)) {
                $migrated['restrictions'] = $this->migrateRestrictions(
                        $legacy->restrictions
                );
            }
            if (isset($legacy->events)) {
                $migrated['events'] = $this->migrateEvents($legacy->events);
            }
        }

        return $migrated;
    }

    protected function migrateMenu($legacy) {
        $menu = array();

        if (is_array($legacy)) {
            foreach ($legacy as $id => $data) {
                if (isset($data['whole']) && intval($data['whole'])) {
                    $menu[$id] = 1;
                }
                if (isset($data['sub']) && is_array($data['sub'])) {
                    foreach ($data['sub'] as $sub_id => $sub_data) {
                        if (intval($sub_data)) {
                            $menu[$sub_id] = 1;
                        }
                    }
                }
            }
        }

        return $menu;
    }

    protected function migrateMetaboxes($legacy) {
        $metaboxes = array();
        if (is_array($legacy)) {
            foreach ($legacy as $id => $checked) {
                if (intval($checked)) {
                    $chunks = explode('-', $id);
                    if (count($chunks) == 1) { //widgets
                        if (!isset($metaboxes['widgets'])) {
                            $metaboxes['widgets'] = array();
                        }
                        $metaboxes['widgets'][$chunks[0]] = 1;
                    } elseif (count($chunks) == 2) {
                        if (!isset($metaboxes[$chunks[0]])) {
                            $metaboxes[$chunks[0]] = array();
                        }
                        $metaboxes[$chunks[0]][$chunks[1]] = 1;
                    }
                }
            }
        }

        return $metaboxes;
    }

    protected function migrateCapabilities($capabilities) {
        return (is_array($capabilities) && count($capabilities) ? $capabilities : array());
    }

    protected function migrateRestrictions($legacy) {
        $access = array(
            'post' => array(),
            'taxonomy' => array()
        );
        if (is_array($legacy)) {
            foreach ($legacy as $type => $records) {
                if (is_array($records)) {
                    foreach ($records as $id => $restrictions) {
                        $access[$type][$id] = array(
                            'frontend' => array(),
                            'backend' => array()
                        );
                        foreach ($restrictions as $restriction => $checked) {
                            if (intval($checked)) {
                                $chunks = explode('_', $restriction);
                                if ($type == 'taxonomy') {
                                    $access[$type][$id][$chunks[0]][$chunks[1]] = 1;
                                } elseif ($type == 'post') {
                                    $access[$type][$id][$chunks[0]][$chunks[2]] = 1;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $access;
    }

    protected function migrateEvents($legacy) {
        $events = array();

        if (is_array($legacy)) {
            foreach ($legacy as $event_id => $data) {
                $events[$event_id] = array();
                if (isset($data['eventType'])) {
                    $events[$event_id]['event'] = $data['eventType'];
                }
                if (isset($data['statusChange'])) {
                    $events[$event_id]['event_specifier'] = $data['statusChange'];
                }
                if (isset($data['postType'])) {
                    $events[$event_id]['post_type'] = $data['postType'];
                }
                if (isset($data['eventAction'])) {
                    //fix type from previos version
                    if ($data['eventAction'] === 'change_satus') {
                        $action = 'change_status';
                    } else {
                        $action = $data['eventAction'];
                    }
                    $events[$event_id]['action'] = $action;
                }
                if (!empty($data['eventEmail'])) {
                    $events[$event_id]['action_specifier'] = $data['eventEmail'];
                } elseif (!empty($data['statusChangeTo'])) {
                    $events[$event_id]['action_specifier'] = $data['statusChangeTo'];
                } elseif (!empty($data['callback'])) {
                    $events[$event_id]['action_specifier'] = $data['callback'];
                }
            }
        }

        return $events;
    }

    protected function migrateUsers(&$collection) {
        if (is_array($collection['users'])) {
            foreach ($collection['users'] as $id => $data) {
                $migrated = $this->migrateSettings($data);
                //unset restrictions, bad implementation in AAM 1.x
                if (isset($migrated['restrictions'])) {
                    unset($migrated['restrictions']);
                }

                $this->saveSettings('User', $id, $migrated);
            }
        }

        return 'success';
    }

    protected function migrateRoles(&$collection) {
        global $wpdb, $wp_user_roles, $blog_id;

        if (is_multisite()) {
            foreach ($collection['roles'] as $blog_id => $roles) {
                //reset roles & blog id
                $wp_user_roles = null;
                $wpdb->set_blog_id($blog_id);
                foreach ($roles as $role => $data) {
                    $this->migrateRole($role, $data, $blog_id);
                }
            }
        } else {
            foreach ($collection['roles'] as $role => $data) {
                $this->migrateRole($role, $data);
            }
        }
    }

    protected function migrateRole($role, $data) {
        if ($role !== 'administrator'){ //skip admin role. We do not have super admin anymore
            $settings = $this->migrateSettings($data);
        } elseif (isset($data['events'])) { //transfer only events
            $settings = array('events' => $data['events']);
        } else {
            $settings = array();
        }

        return $this->saveSettings('Role', $role, $settings);
    }

    protected function saveSettings($type, $id, $settings) {
        if (is_array($settings) && count($settings)) {
            $subject_class = 'aam_Control_Subject_' . $type;
            $subject = new $subject_class($id);
            //set new settings
            //Dashboard Menu settings
            if (isset($settings['menu']) && count($settings['menu'])) {
                $subject->getObject(aam_Control_Object_Menu::UID)->save(
                        $settings['menu']
                );
            }
            //Dashboard Metaboxes & Widgets
            if (isset($settings['metabox']) && count($settings['metabox'])) {
                $subject->getObject(aam_Control_Object_Metabox::UID)->save(
                        $settings['metabox']
                );
            }
            //Capability list
            if (isset($settings['capability']) && count($settings['capability'])) {
                foreach ($settings['capability'] as $capability => $grand) {
                    if (intval($grand)) {
                        $subject->addCapability($capability);
                    } else {
                        $subject->removeCapability($capability);
                    }
                }
            }
            //Posts & Categories
            if (isset($settings['restrictions']) && count($settings['restrictions'])) {
                if (count($settings['restrictions']['post'])) {
                    $post = $subject->getObject(aam_Control_Object_Post::UID);
                    foreach ($settings['restrictions']['post'] as $post_id => $data) {
                        $post = $subject->getObject(
                            aam_Control_Object_Post::UID, $post_id
                        );
                        if ($post->getPost() instanceof WP_Post) {
                            $post->save(array('post' => $data));
                        }
                    }
                }

                if (count($settings['restrictions']['taxonomy'])) {
                    foreach ($settings['restrictions']['taxonomy'] as $term_id => $data) {
                        $term = $subject->getObject(
                            aam_Control_Object_Term::UID, $term_id
                        );
                        if (is_object($term->getTerm())) {
                            $term->save(array('term' => $data));
                        }
                    }
                }
            }

            //Events
            if (isset($settings['events']) && count($settings['events'])) {
                $subject->getObject(aam_Control_Object_Event::UID)->save(
                        $settings['events']
                );
            }
        }
    }

    protected function cleanup() {
        $this->removeSuperAdmin();

        //remove migration cache
        aam_Core_API::deleteBlogOption('aam_migration_cache', 1);

        return array('status' => 'success', 'stop' => 1);
    }

    protected function removeSuperAdmin() {
        global $wp_user_roles, $wpdb;

        if (is_multisite()) {
            //get all sites first and iterate through each
            $query = 'SELECT blog_id FROM ' . $wpdb->blogs;
            $blog_list = $wpdb->get_results($query);
            if (is_array($blog_list)) {
                foreach ($blog_list as $blog) {
                    //reset roles & blog id
                    $wp_user_roles = null;
                    $wpdb->set_blog_id($blog_id);
                    $this->removeSuperAdminRole();
                }
            }
        } else {
            $this->removeSuperAdminRole();
        }
        //remove all super_admin capabilities from usermeta
        $list = $wpdb->get_results(
                'SELECT * FROM ' . $wpdb->usermeta . ' WHERE meta_key LIKE "%_capabilities"'
        );
        if (is_array($list)) {
            foreach ($list as $metadata) {
                $caps = unserialize($metadata->meta_value);
                if (isset($caps['super_admin'])) {
                    unset($caps['super_admin']);
                    $caps['administrator'] = 1;
                    $wpdb->update(
                            $wpdb->usermeta,
                            array('meta_value' => serialize($caps)),
                            array('umeta_id' => $metadata->umeta_id)
                    );
                }
            }
        }
    }

    protected function removeSuperAdminRole() {
        //update the role capabilities and remove super admin role
        $roles = new WP_Roles();
        //get all capabilities first and merge them in one array
        $capabilities = array();
        foreach ($roles->role_objects as $role) {
            $capabilities = array_merge($capabilities, $role->capabilities);
        }

        if (count($capabilities)) {
            //update administrator capability role
            if ($admin = $roles->get_role('administrator')) {
                foreach ($capabilities as $capability => $grand) {
                    $admin->add_cap($capability);
                }
            } else {
                $roles->add_role('administrator', 'Administrator', $capabilities);
            }
            //remove Super Admin Role
            $roles->remove_role('super_admin');
        }
    }

    protected function complete() {
        aam_Core_API::updateBlogOption('aam_migrated', 1, 1);

        return array('status' => 'success', 'stop' => 1);
    }

}