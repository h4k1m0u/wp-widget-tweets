<?php
/*
Plugin Name: Tweets
Plugin URI: http://plugin.url
Description: A widget that gets tweets and display them.
Version: 1.0
Author: Hakim Benoudjit
Author URI: http://author.url
*/

// include twitter php api
require "vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

class Tweets_Widget extends WP_Widget {
    public function __construct() {
        // init widget
        wp_enqueue_style('tweets.css', plugins_url('css/tweets.css', __FILE__));
        parent::WP_Widget(
            'tweets',
            'Tweets', 
            array(
                'classname' => 'tweets',
                'description' => 'A widget that gets tweets and display them'
            )
        );
    }

    public function form($instance) {
        // Outputs the options form on admin
        $consumer_key = $instance['consumer_key'];
        $consumer_secret = $instance['consumer_secret'];
        $access_token = $instance['access_token'];
        $access_token_secret = $instance['access_token_secret'];
        $query = ($instance['query'] ? $instance['query'] : '@BBCBreaking');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('consumer_key'); ?>">Consumer key</label>
            <input id="<?php echo $this->get_field_id('consumer_key'); ?>" name="<?php echo $this->get_field_name('consumer_key'); ?>" value="<?php echo esc_attr($consumer_key); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('consumer_secret'); ?>">Consumer secret</label>
            <input id="<?php echo $this->get_field_id('consumer_secret'); ?>" name="<?php echo $this->get_field_name('consumer_secret'); ?>" value="<?php echo esc_attr($consumer_secret); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('access_token'); ?>">Access token</label>
            <input id="<?php echo $this->get_field_id('access_token'); ?>" name="<?php echo $this->get_field_name('access_token'); ?>" value="<?php echo esc_attr($access_token); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('access_token_secret'); ?>">Access token</label>
            <input id="<?php echo $this->get_field_id('access_token_secret'); ?>" name="<?php echo $this->get_field_name('access_token_secret'); ?>" value="<?php echo esc_attr($access_token_secret); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('query'); ?>">Twitter query</label>
            <input id="<?php echo $this->get_field_id('query'); ?>" name="<?php echo $this->get_field_name('query'); ?>" value="<?php echo esc_attr($query); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        // Processing widget options on save
        $instance = array();
        $instance['consumer_key'] = $new_instance['consumer_key'];
        $instance['consumer_secret'] = $new_instance['consumer_secret'];
        $instance['access_token'] = $new_instance['access_token'];
        $instance['access_token_secret'] = $new_instance['access_token_secret'];
        $instance['query'] = ($new_instance['query'] ? strip_tags($new_instance['query']) : '@BBCBreaking');

        return $instance;
    }

    public function widget($args, $instance) {
        // Outputs the content of the widget
        $query = $instance['query'];
        $connection = new TwitterOAuth($instance['consumer_key'], $instance['consumer_secret'], $instance['access_token'], $instance['access_token_secret']);
        ?>
        <aside id="tweets" class="widget widget_tweets">
            <h1 class="widget-title">
                <a href="https://twitter.com/<?php echo substr($query, 1); ?>" target="_blank">
                    Tweets <?php echo $query; ?>
                </a>
            </h1>
            <div>
                <?php try { ?>
                    <?php
                        // retreive tweets
                        $content = $connection->get('search/tweets', array('q' => $query));
                        $statuses = $content->statuses;

                        if ($statuses) {
                    ?>
                            <ul>
                                <?php foreach($statuses as $status) {?>
                                    
                                    <li>
                                        <p>
                                            <?php echo substr($status->created_at, 0, 19); ?>
                                        </p>
                                        <a href="<?php echo $status->entities->media[0]->url; ?>" target="_blank">
                                            <?php echo substr($status->text, 0, 50) . '...'; ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                    <?php } else { ?>
                        No status retreived.
                    <?php } ?>
                <?php } catch (Exception $e) { ?>
                    <?php echo $e->getMessage(); ?>
                <?php } ?>
            </div>
        </aside>
        <?php
    }
}

// register the widget
add_action('widgets_init', function() {
    register_widget('Tweets_Widget');
});
