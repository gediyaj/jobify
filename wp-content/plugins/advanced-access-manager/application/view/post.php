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
class aam_View_Post extends aam_View_Abstract {

    /**
     *
     * @var type
     */
    private $_post_type = 'post';

    /**
     *
     * @var type
     */
    private $_term = 0;

    /**
     *
     */
    public function __construct() {
        parent::__construct();
        $this->_post_type = aam_Core_Request::request('post_type');
        $this->_term = intval(aam_Core_Request::request('term'));
    }

    /**
     *
     * @global type $wp_post_types
     * @return type
     */
    public function retrievePostTypeList() {
        global $wp_post_types;

        $response = array(
            'aaData' => array()
        );

        if (is_array($wp_post_types)) {
            foreach ($wp_post_types as $post_type => $data) {
                //show only list of post type which have User Interface
                if ($data->show_ui) {
                    $response['aaData'][] = array(
                        $post_type,
                        $data->label,
                        ''
                    );
                }
            }
        }

        return json_encode($response);
    }

    /**
     *
     * @global type $wp_post_statuses
     * @global type $wp_post_types
     * @return type
     */
    public function retrievePostList() {
        global $wp_post_statuses, $wp_post_types, $wp_taxonomies;

        $term = trim(aam_Core_Request::request('term'));
        
        //default behavior
        if (empty($term)) {
            $post_type = 'post';
        //root for each Post Type
        } elseif (isset($wp_post_types[$term])) {
            $post_type = $term;
            $term = '';
        } else {
            $taxonomy = $this->getTaxonomy($term);
            if (isset($wp_taxonomies[$taxonomy])){
                //take in consideration only first object type
                $post_type = $wp_taxonomies[$taxonomy]->object_type[0];
            } else {
                $post_type = 'post';
            }
        }
        
        $args = array(
            'numberposts' => aam_Core_Request::request('iDisplayLength'),
            'offset' => aam_Core_Request::request('iDisplayStart'),
            //'category' => $term,
            'term' => $term,
            'taxonomy' => (!empty($taxonomy) ? $taxonomy : ''),
            'post_type' => $post_type,
            's' => aam_Core_Request::request('sSearch'),
            'post_status' => array()
        );

        $argsAll = array(
            'numberposts' => '999999',
            'fields' => 'ids',
            //'category' => $term,
            'term' => $term,
            'taxonomy' => (!empty($taxonomy) ? $taxonomy : ''),
            'post_type' => $post_type,
            's' => aam_Core_Request::request('sSearch'),
            'post_status' => array()
        );

        foreach ($wp_post_statuses as $status => $data) {
            if ($data->show_in_admin_status_list) {
                $args['post_status'][] = $status;
                $argsAll['post_status'][] = $status;
            }
        }

        $total = 0;
        foreach (wp_count_posts($post_type) as $status => $number) {
            if ($wp_post_statuses[$status]->show_in_admin_status_list) {
                $total += $number;
            }
        }
        
        //get displayed total
        $displayTotal = count(get_posts($argsAll));

        $response = array(
            'iTotalRecords' => $total,
            'iTotalDisplayRecords' => $displayTotal,
            'sEcho' => aam_Core_Request::request('sEcho'),
            'aaData' => array(),
        );

        foreach (get_posts($args) as $post) {
            $response['aaData'][] = array(
                $post->ID,
                $post->post_status,
                get_edit_post_link($post->ID),
                $post->post_title,
                $wp_post_statuses[$post->post_status]->label,
                ''
            );
        }

        return json_encode($response);
    }
    
    /**
     * Get Taxonomy by Term ID
     * 
     * @global type $wpdb
     * @param type $object_id
     * @return type
     */
    private function getTaxonomy($object_id) {
        global $wpdb;

        $query = "SELECT taxonomy FROM {$wpdb->term_taxonomy} ";
        $query .= "WHERE term_id = {$object_id}";

        return $wpdb->get_var($query);
    }

    /**
     *
     * @global type $wp_post_types
     * @return type
     */
    public function getPostTree() {
        global $wp_post_types;

        $type = $_REQUEST['root'];
        $tree = array();

        if ($type == "source") {
            if (is_array($wp_post_types)) {
                foreach ($wp_post_types as $post_type => $data) {
                    //show only list of post type which have User Interface
                    if ($data->show_ui) {
                        $tree[] = (object) array(
                                    'text' => $data->label,
                                    'expanded' => FALSE,
                                    'hasChildren' => TRUE,
                                    'id' => $post_type,
                                    'classes' => 'important',
                        );
                    }
                }
            }
        } else {
            if (preg_match('/^[\d]+$/', $type)) {
                $taxonomy = $this->getTaxonomyByTerm($type);
                $tree = $this->buildBranch(NULL, $taxonomy, $type);
            } else {
                $tree = $this->buildBranch($type);
            }
        }

        if (!count($tree)) {
            $tree[] = (object) array(
                        'text' => '<i>' . __('[empty]', 'aam') . '</i>',
                        'hasChildren' => FALSE,
                        'classes' => 'post-ontree',
                        'id' => 'empty-' . uniqid()
            );
        }

        return json_encode($tree);
    }

    /**
     *
     * @global type $wpdb
     * @param type $term_id
     * @return boolean
     */
    protected function getTaxonomyByTerm($term_id) {
        global $wpdb;

        if ($term_id) {
            $query = "SELECT taxonomy FROM {$wpdb->term_taxonomy} ";
            $query .= "WHERE term_id = {$term_id}";
            $result = $wpdb->get_var($query);
        } else {
            $result = FALSE;
        }

        return $result;
    }

    /**
     *
     * @param type $post_type
     * @param type $taxonomy
     * @param type $parent
     * @return type
     */
    private function buildBranch($post_type, $taxonomy = FALSE, $parent = 0) {
        $tree = array();
        if (!$parent && !$taxonomy) { //root of a branch
            $tree = $this->buildCategories($post_type);
        } elseif ($taxonomy) { //build sub categories
            $tree = $this->buildCategories('', $taxonomy, $parent);
        }

        return $tree;
    }

    /**
     *
     * @param type $post_type
     * @param type $taxonomy
     * @param type $parent
     * @return type
     */
    private function buildCategories($post_type, $taxonomy = FALSE, $parent = 0) {

        $tree = array();

        if ($parent) {
            //$taxonomy = $this->get_taxonomy_get_term($parent);
            //firstly render the list of sub categories
            $cat_list = get_terms(
                    $taxonomy, array(
                'get' => 'all',
                'parent' => $parent,
                'hide_empty' => FALSE
                    )
            );
            if (is_array($cat_list)) {
                foreach ($cat_list as $category) {
                    $tree[] = $this->buildCategory($category);
                }
            }
        } else {
            $taxonomies = get_object_taxonomies($post_type);
            foreach ($taxonomies as $taxonomy) {
                if (is_taxonomy_hierarchical($taxonomy)) {
                    $term_list = get_terms($taxonomy, array(
                        'parent' => $parent,
                        'hide_empty' => FALSE
                    ));
                    if (is_array($term_list)) {
                        foreach ($term_list as $term) {
                            $tree[] = $this->buildCategory($term);
                        }
                    }
                }
            }
        }

        return $tree;
    }

    /**
     *
     * @param type $category
     * @return type
     */
    private function buildCategory($category) {
        $branch = (object) array(
                    'text' => $category->name,
                    'expanded' => FALSE,
                    'classes' => 'important folder',
        );
        if ($this->hasCategoryChilds($category)) {
            $branch->hasChildren = TRUE;
        } else {
            $branch->hasChildren = FALSE;
        }
        $branch->id = $category->term_id;

        return $branch;
    }

    /**
     * Check if category has children
     *
     * @param int category ID
     * @return bool TRUE if has
     */
    protected function hasCategoryChilds($cat) {
        global $wpdb;

        //get number of categories
        $query = "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE parent={$cat->term_id}";
        $counter = $wpdb->get_var($query);

        return ($counter ? TRUE : FALSE);
    }

    /**
     *
     * @global type $wp_post_types
     * @global type $wp_taxonomies
     * @return type
     */
    public function getPostBreadcrumb() {
        global $wp_post_types, $wp_taxonomies;

        $id = aam_Core_Request::post('id');
        $response = array();
        if (preg_match('/^[\d]+$/', $id)) {
            $taxonomy = $this->getTaxonomyByTerm($id);
            //get post type
            if (isset($wp_taxonomies[$taxonomy])) {
                $post_type = $wp_taxonomies[$taxonomy]->object_type[0];
                if (isset($wp_post_types[$post_type])) {
                    $response['post_type'] = $post_type;
                    $response['term'] = $id;
                    $response['link'] = get_edit_term_link($id, $taxonomy);
                    $term = get_term($id, $taxonomy);
                    $tree = $this->renderBreadcrumbTree($term, array());
                    $tree[] = array($post_type, $wp_post_types[$post_type]->label);
                    $response['breadcrumb'] = array_reverse($tree);
                }
            }
        } else {
            if (isset($wp_post_types[$id])) {
                $response = array(
                    'post_type' => $id,
                    'term' => 0,
                    'breadcrumb' => array(
                        array($id, $wp_post_types[$id]->label),
                        array('#', __('All', 'aam'))
                    )
                );
            } else {
                $response = array(
                    'term' => '',
                    'breadcrumb' => array(
                        array('post', $wp_post_types['post']->label),
                        array('#', __('All', 'aam'))
                    )
                );
            }
        }

        return json_encode($response);
    }

    /**
     *
     * @param type $term
     * @param type $tree
     * @return type
     */
    protected function renderBreadcrumbTree($term, $tree) {
        $tree[] = array($term->term_id, $term->name);
        if ($term->parent) {
            $tree = $this->renderBreadcrumbTree(
                    get_term($term->parent, $term->taxonomy), $tree
            );
        }

        return $tree;
    }

    /**
     *
     * @return type
     */
    public function content() {
        return $this->loadTemplate(dirname(__FILE__) . '/tmpl/post.phtml');
    }

    /**
     *
     * @return type
     */
    public function saveAccess() {
        $object_id = aam_Core_Request::post('id');

        $limit_counter = apply_filters(
                'wpaccess_restrict_limit',
                aam_Core_API::getBlogOption('aam_access_limit', 0)
        );

        if ($limit_counter == -1 || $limit_counter <= 5) {
            $access = aam_Core_Request::post('access');
            if (aam_Core_Request::post('type') == 'term') {
                $object = $this->getSubject()->getObject(
                        aam_Control_Object_Term::UID, $object_id
                );
                if ($limit_counter !== -1 && isset($access['post'])){
                    unset($access['post']);
                }
            } else {
                $object = $this->getSubject()->getObject(
                        aam_Control_Object_Post::UID, $object_id
                );
            }
            $object->save($access);
            aam_Core_API::updateBlogOption('aam_access_limit', $limit_counter + 1);

            //clear cache
            $this->getSubject()->clearCache();

            $response = array('status' => 'success');
        } else {
            $response = array(
                'status' => 'failure',
                'reason' => 'limitation',
                'extension' => 'AAM Unlimited Basic'
            );
        }

        return json_encode($response);
    }

    /**
     *
     * @return type
     */
    public function getAccess() {
        $type = aam_Core_Request::post('type');
        $object_id = aam_Core_Request::post('id');

        if ($type === 'term') {
            $object = $this->getSubject()->getObject(
                    aam_Control_Object_Term::UID, $object_id
            );
        } else {
            $object = $this->getSubject()->getObject(
                    aam_Control_Object_Post::UID, $object_id
            );
        }

        return json_encode(array(
            'settings' => $object->getOption(),
            'counter' => apply_filters(
                    'wpaccess_restrict_limit',
                    aam_Core_API::getBlogOption('aam_access_limit', 0)
            )
        ));
    }

    /**
     *
     * @return type
     */
    public function clearAccess() {
        $type = aam_Core_Request::post('type');
        $object_id = aam_Core_Request::post('id');

        if ($type === 'term') {
            $object = $this->getSubject()->getObject(
                    aam_Control_Object_Term::UID, $object_id
            );
        } else {
            $object = $this->getSubject()->getObject(
                    aam_Control_Object_Post::UID, $object_id
            );
        }

        return json_encode(array(
            'status' => ($object->delete() ? 'success' : 'failure')
        ));
    }

}