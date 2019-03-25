<?php
/**
 * HooksMultisiteLocationAcf class file
 *
 * @author  spidlace
 *
 */


/**
 * Class HooksMultisiteLocationAcf
 *
 */
class HooksMultisiteLocationAcf {

    function __construct() {

        if (is_multisite()) {
            add_filter('acf/location/rule_types', array($this, 'acf_location_rules_types'));
            add_filter('acf/location/rule_values/theme', array($this, 'acf_location_rules_values_theme'));
            add_filter('acf/location/rule_values/cpt', array($this, 'acf_location_rules_values_cpt'));
            add_filter('acf/location/rule_values/modelpage', array($this, 'acf_location_rules_values_modelpage'));
            add_filter('acf/location/rule_match/theme', array($this, 'acf_location_rules_match_theme'), 10, 3);
            add_filter('acf/location/rule_match/cpt', array($this, 'acf_location_rules_match_cpt'), 10, 3);
            add_filter('acf/location/rule_match/modelpage', array($this, 'acf_location_rules_match_modelpage'), 10, 3);
        }
    }

    // Add new location
    function acf_location_rules_types( $choices ) {
        $name_choice = __('Multisite', 'acf');
        $choices[$name_choice]['theme'] = __('Theme', 'acf');
        $choices[$name_choice]['custom_post_type'] = __('Post Type', 'acf');
        $choices[$name_choice]['model_page'] = __('Default Template', 'acf');

        return $choices;
    }

    // Add values for new rule
    function acf_location_rules_values_theme( $choices ) {
        $themes = wp_get_themes();

        if( $themes ) {
            foreach ( $themes as $theme ) {
                $choices[$theme->stylesheet] = $theme->stylesheet;
            }
        }

        return $choices;
    }

    function acf_location_rules_values_cpt( $choices ) {
        $sites = get_sites();

        if( $sites ) {
            $all_post_types = array();
            foreach ($sites as $key => $site) {
                switch_to_blog( $site->blog_id );
                $post_types = get_option( 'my_prefix_post_types', array() );
                $all_post_types = array_merge($all_post_types, $post_types);
                restore_current_blog();
            }

            usort($all_post_types, function ($a, $b) {
                return strcmp($a->label, $b->label);
            });

            foreach ( $all_post_types as $post_type ) {
                $choices[$post_type->name] = $post_type->label;
            }
        }

        return $choices;
    }

    function acf_location_rules_values_modelpage( $choices ) {
        $sites = get_sites();

        if( $sites ) {
            $all_templates = array();
            foreach ($sites as $key => $site) {
                switch_to_blog( $site->blog_id );
                $templates = get_page_templates();
                $all_templates = array_merge($templates, $all_templates);
                restore_current_blog();
            }

            ksort($all_templates);

            foreach ( $all_templates as $label => $template ) {
                $choices[$template] = $label;
            }
        }

        return $choices;
    }

    function acf_location_rules_match_theme( $match, $rule, $options ) {
        $current_theme = wp_get_theme();
        $selected_theme = $rule['value'];

        if( $rule['operator'] == "==" ) {
            $match = ( $current_theme->stylesheet == $selected_theme );
        } elseif( $rule['operator'] == "!=" ) {
            $match = ( $current_theme->stylesheet != $selected_theme );
        }

        return $match;
    }

    function acf_location_rules_match_cpt( $match, $rule, $options ) {
        $post_type = get_post_type();
        $selected_cpt = $rule['value'];

        if( $rule['operator'] == "==" ) {
            $match = ( $post_type == $selected_cpt );
        } elseif( $rule['operator'] == "!=" ) {
            $match = ( $post_type != $selected_cpt );
        }

        return $match;
    }

    function acf_location_rules_match_modelpage( $match, $rule, $options ) {

        if ( !isset( $options['post_id'] ) ) {
            return false;
        }

        $post = get_post( $options['post_id'] );
        $page_template = '';
        if ( isset( $post->page_template ) && ! empty( $post->page_template )) {
            $page_template = $post->page_template;
        }
        $selected_cpt = $rule['value'];

        if( $rule['operator'] == "==" ) {
            $match = ( $page_template == $selected_cpt );
        } elseif( $rule['operator'] == "!=" ) {
            $match = ( $page_template != $selected_cpt );
        }

        return $match;
    }
}

