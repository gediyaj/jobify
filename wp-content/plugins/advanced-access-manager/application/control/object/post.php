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
class aam_Control_Object_Post extends aam_Control_Object {

    /**
     *
     */
    const UID = 'post';

    /**
     *
     */
    const ACTION_COMMENT = 'comment';

    /**
     *
     */
    const ACTION_READ = 'read';

    /**
     *
     */
    const ACTION_EXCLUDE = 'exclude';

    /**
     *
     */
    const ACTION_TRASH = 'trash';

    /**
     *
     */
    const ACTION_DELETE = 'delete';

    /**
     *
     */
    const ACTION_EDIT = 'edit';

    /**
     *
     * @var type
     */
    private $_post;

    /**
     *
     * @var type
     */
    private $_option = array();

    /**
     * @inheritdoc
     */
    public function __sleep(){
        return array('_post', '_option');
    }

    /**
     * @inheritdoc
     */
    public function cacheObject(){
        return true;
    }

    /**
     *
     * @param type $params
     */
    public function save($params = null) {
        if (is_array($params)) {
            update_post_meta($this->getPost()->ID, $this->getOptionName(), $params);
        }
    }

    /**
     *
     * @param type $area
     * @return type
     */
    public function getAccessList($area) {
        if ($area == 'frontend') {
            $response = array(
                self::ACTION_READ, self::ACTION_EXCLUDE, self::ACTION_COMMENT
            );
        } elseif ($area == 'backend') {
            $response = array(
                self::ACTION_TRASH, self::ACTION_DELETE, self::ACTION_EDIT
            );
        } else {
            $response = array();
        }

        return apply_filters('aam_post_access_list', $response, $area);
    }

    /**
     *
     * @return type
     */
    public function getUID() {
        return self::UID;
    }

    /**
     *
     * @return type
     */
    protected function getOptionName() {
        $subject = $this->getSubject();
        //prepare option name
        $meta_key = 'aam_' . self::UID . '_access_' . $subject->getUID();
        $meta_key .= ($subject->getId() ? $subject->getId() : '');

        return $meta_key;
    }

    /**
     * Init Post Object
     *
     * @param WP_Post|Int $object
     *
     * @return void
     *
     * @access public
     */
    public function init($object) {
        //make sure that we are dealing with WP_Post object
        if ($object instanceof WP_Post){
            $this->setPost($object);
        } elseif (intval($object)) {
            $this->setPost(get_post($object));
        }
        //read options
        if ($this->getPost()) {
            $this->read();
        }
    }

    /**
     * Read the Post AAM Metadata
     *
     * Get all settings related to specified post
     *
     * @return void
     *
     * @access public
     */
    public function read() {
        $option = get_post_meta($this->getPost()->ID, $this->getOptionName(), true);
        //try to inherit it from parent category
        if (empty($option)) {
            $terms = $this->retrievePostTerms();
            //use only first term for inheritance
            $term_id = array_shift($terms);
            //try to get any parent access
            $option = $this->inheritAccess($term_id);
        }

        $this->setOption(
                apply_filters('aam_post_access_option', $option, $this->getSubject())
        );
    }

    /**
     *
     * @return type
     */
    public function delete() {
        return delete_post_meta($this->getPost()->ID, $this->getOptionName());
    }

    /**
     *
     * @return array
     */
    private function retrievePostTerms() {
        $taxonomies = get_object_taxonomies($this->getPost());
        if (is_array($taxonomies) && count($taxonomies)) {
            //filter taxonomies to hierarchical only
            $filtered = array();
            foreach ($taxonomies as $taxonomy) {
                if (is_taxonomy_hierarchical($taxonomy)) {
                    $filtered[] = $taxonomy;
                }
            }
            $terms = wp_get_object_terms(
                    $this->getPost()->ID, $filtered, array('fields' => 'ids')
            );
        } else {
            $terms = array();
        }

        return $terms;
    }

    /**
     *
     * @param type $term_id
     * @return array
     */
    private function inheritAccess($term_id) {
        $term = new aam_Control_Object_Term($this->getSubject(), $term_id);
        $access = $term->getOption();
        if (isset($access['post']) && $access['post']) {
            $result = array('post' => $access['post']);
        } elseif (is_object($term->getTerm()) && $term->getTerm()->parent) {
            $result = $this->inheritAccess($term->getTerm()->parent);
        } else {
            $result = array();
        }

        return $result;
    }

    /**
     * Set Post. Cover all unexpectd wierd issues with WP Core
     *
     * @param WP_Post $post
     *
     * @return void
     *
     * @access public
     */
    public function setPost($post) {
        if ($post instanceof WP_Post){
            $this->_post = $post;
        } else {
            $this->_post = (object) array('ID' => 0);
        }
    }

    /**
     * Get Post
     *
     * @return WP_Post|stdClass
     *
     * @access public
     */
    public function getPost() {
        return $this->_post;
    }

    /**
     *
     * @param type $option
     */
    public function setOption($option) {
        $this->_option = (is_array($option) ? $option : array());
    }

    /**
     *
     * @return type
     */
    public function getOption() {
        return $this->_option;
    }

    /**
     *
     * @param type $area
     * @param type $action
     * @return type
     */
    public function has($area, $action) {
        $response = false;
        if (isset($this->_option['post'][$area][$action])) {
            $response = (intval($this->_option['post'][$area][$action]) ? true : false);
        }

        return $response;
    }

}