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
class aam_View_Metabox extends aam_View_Abstract {

    /**
     * Metabox Group - WIDGETS
     * 
     * Is used to retrieve the list of all wigets on the frontend
     */
    const GROUP_WIDGETS = 'widgets';

    /**
     *
     * @var type
     */
    private $_cache = array();

    /**
     *
     * @global type $wp_meta_boxes
     * @param type $post_type
     */
    public function run($post_type) {
        $this->_cache = aam_Core_API::getBlogOption(
                        'aam_metabox_cache', array()
        );

        if ($post_type === '') {
            $this->collectWidgets();
        } else {
            $this->collectMetaboxes($post_type);
        }
        aam_Core_API::updateBlogOption('aam_metabox_cache', $this->_cache);
    }

    /**
     *
     * @global type $wp_registered_widgets
     */
    protected function collectWidgets() {
        global $wp_registered_widgets;
        
        if (!isset($this->_cache['widgets'])) {
            $this->_cache['widgets'] = array();
        }

        //get frontend widgets
        if (is_array($wp_registered_widgets)) {
            foreach ($wp_registered_widgets as $id => $data) {
                if (is_object($data['callback'][0])) {
                    $callback = get_class($data['callback'][0]);
                } elseif (is_string($data['callback'][0])) {
                    $callback = $data['callback'][0];
                } else {
                    $callback = null;
                }

                if (!is_null($callback)) { //exclude any junk
                    $this->_cache['widgets'][$callback] = array(
                        'title' => $this->removeHTML($data['name']),
                        'id' => $callback
                    );
                }
            }
        }

        //now collect Admin Dashboard Widgets
        $this->collectMetaboxes('dashboard');
    }

    protected function collectMetaboxes($post_type) {
        global $wp_meta_boxes;
        
        if (!isset($this->_cache[$post_type])) {
            $this->_cache[$post_type] = array();
        }

        if (isset($wp_meta_boxes[$post_type]) && is_array($wp_meta_boxes[$post_type])) {
            foreach ($wp_meta_boxes[$post_type] as $levels) {
                if (is_array($levels)) {
                    foreach ($levels as $boxes) {
                        if (is_array($boxes)) {
                            foreach ($boxes as $data) {
                                if (trim($data['id'])) { //exclude any junk
                                    $this->_cache[$post_type][$data['id']] = array(
                                        'id' => $data['id'],
                                        'title' => $this->removeHTML($data['title'])
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * @return type
     */
    public function initLink() {
        $link = filter_var(aam_Core_Request::post('link'), FILTER_VALIDATE_URL);
        if ($link) {
            $url = add_query_arg('aam_meta_init', 1, $link);
            aam_Core_API::cURL($url);
            $response = array('status' => 'success');
        } else {
            $response = array('status' => 'failure');
        }

        return json_encode($response);
    }

    /**
     *
     * @global type $wp_post_types
     * @return type
     */
    public function retrieveList() {
        global $wp_post_types;

        if (aam_Core_Request::post('refresh') == 1) {
            aam_Core_API::deleteBlogOption('aam_metabox_cache');
            $type_list = array_keys($wp_post_types);
            array_unshift($type_list, self::GROUP_WIDGETS);

            foreach ($type_list as $type) {
                if ($type == 'widgets') {
                    $url = add_query_arg(
                            'aam_meta_init', 
                            1,
                            admin_url('index.php')
                    );
                } else {
                    $url = add_query_arg(
                            'aam_meta_init', 
                            1, 
                            admin_url('post-new.php?post_type=' . $type)
                    );
                }
                //grab metaboxes
                aam_Core_API::cURL($url);
            }
        }

        return $this->buildMetaboxList();
    }

    /**
     *
     * @global type $wp_post_types
     * @return type
     */
    protected function buildMetaboxList() {
        global $wp_post_types;

        $cache = aam_Core_API::getBlogOption('aam_metabox_cache', array());
        if ($this->getSubject()->getUID() == 'visitor') {
            $list = array(
                'widgets' => (isset($cache['widgets']) ? $cache['widgets'] : array())
            );
        } else {
            $list = $cache;
        }
        $content = '<div id="metabox_list">';
        foreach ($list as $screen => $metaboxes) {
            $content .= '<div class="group">';
            switch ($screen) {
                case 'dashboard':
                    $content .= '<h4>' . __('Dashboard Widgets', 'aam') . '</h4>';
                    break;

                case 'widgets':
                    $content .= '<h4>' . __('Frontend Widgets', 'aam') . '</h4>';
                    break;

                default:
                    $content .= '<h4>' . $wp_post_types[$screen]->labels->name;
                    $content .= '</h4>';
                    break;
            }
            $content .= '<div>';
            $content .= '<div class="metabox-group">';
            $i = 1;
            $metaboxControl = $this->getSubject()->getObject(
                    aam_Control_Object_Metabox::UID
            );
            foreach ($metaboxes as $metabox) {
                if ($i++ == 1) {
                    $content .= '<div class=metabox-row>';
                }
                //prepare title
                if (strlen($metabox['title']) > 18) {
                    $title = substr($metabox['title'], 0, 15) . '...';
                } else {
                    $title = $metabox['title'];
                }
                //prepare selected
                if ($metaboxControl->has($screen, $metabox['id'])) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = '';
                }

                $metabox_id = "metabox_{$screen}_{$metabox['id']}";

                $content .= '<div class="metabox-item">';
                $content .= sprintf(
                        '<label for="%s" tooltip="%s">%s</label>', 
                        $metabox_id, 
                        esc_js($metabox['title']), 
                        $title
                );
                $content .= sprintf(
                        '<input type="checkbox" id="%s" name="aam[%s][%s][%s]" %s />', 
                        $metabox_id, 
                        aam_Control_Object_Metabox::UID, 
                        $screen, 
                        $metabox['id'], 
                        $checked
                );
                $content .= '<label for="' . $metabox_id . '"><span></span></label>';
                $content .= '</div>';
                if ($i > 3) {
                    $content .= '</div>';
                    $i = 1;
                }
            }
            if ($i != 1) {
                $content .= '</div>';
            }
            $content .= '</div></div></div>';
        }
        $content .= '</div>';

        return json_encode(array('content' => $content));
    }

    /**
     *
     * @param type $text
     * @return type
     */
    public function removeHTML($text) {
        return strip_tags($text);
    }

    /**
     *
     * @return type
     */
    public function content() {
        return $this->loadTemplate(dirname(__FILE__) . '/tmpl/metabox.phtml');
    }

}