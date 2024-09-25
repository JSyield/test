<?php
global $codigoplus_promote_banner_plugins;

if(empty($codigoplus_promote_banner_plugins)) {
    $codigoplus_promote_banner_plugins = array();
}

if(!function_exists( 'codigoplus_add_promote_banner' ))
{
	function codigoplus_add_promote_banner($wp_admin_bar)
	{
		global $codigoplus_promote_banner_plugins;

		if( empty($codigoplus_promote_banner_plugins) || !is_admin() ) return;

		$screen = get_current_screen();
		if ( ($screen->post_type == 'page' || $screen->post_type == 'post') && $screen->base == 'post') return;

		// Take action over the banner

		if(isset($_POST['codigoplus_promote_banner_nonce']) && wp_verify_nonce($_POST['codigoplus_promote_banner_nonce'], __FILE__))
		{
			if(
				!empty($_POST['codigoplus_promote_banner_plugin']) &&
				!empty($codigoplus_promote_banner_plugins[$_POST['codigoplus_promote_banner_plugin']])
			)
			{
				set_transient( 'codigoplus_promote_banner_'.$_POST['codigoplus_promote_banner_plugin'], -1, 0);
				if(
					!empty($_POST['codigoplus_promote_banner_action']) &&
					$_POST['codigoplus_promote_banner_action'] == 'set-review' &&
					!empty($codigoplus_promote_banner_plugins[$_POST['codigoplus_promote_banner_plugin']]['plugin_url'])
				)
				{
					print '<script>document.location.href="'.esc_js($codigoplus_promote_banner_plugins[$_POST['codigoplus_promote_banner_plugin']]['plugin_url']).'";</script>';
				}
			}
		}

		$minimum_days = 86400*7;
		$now = time();

		foreach($codigoplus_promote_banner_plugins as $plugin_slug => $plugin_data )
		{
			$value = get_transient( 'codigoplus_promote_banner_'.$plugin_slug );
			if( $value === false )
			{
				$value = $now;
				set_transient( 'codigoplus_promote_banner_'.$plugin_slug, $value, 0 );
			}

			if($minimum_days <= abs($now-$value) && 0<$value)
			{
				?>
				<style>
                    #codigoplus-review-banner { width:calc( 100% - 40px );width:-webkit-calc( 100% - 40px );width:-moz-calc( 100% - 40px );width:-o-calc( 100% - 40px );border:8px solid #f19951;background:#FFF;display:table;margin-top:10px; margin-bottom: 10px; border-radius: 30px; border-top-left-radius: 0px;}
                    #codigoplus-review-banner form{float:left; padding:0 5px;}
                    #codigoplus-review-banner .codigoplus-review-banner-picture{width: auto;padding:10px 10px 10px 10px;float:left;text-align:center;}
                    #codigoplus-review-banner .codigoplus-review-banner-content{float: left;padding:10px;width: calc( 100% - 160px );width: -webkit-calc( 100% - 160px );width: -moz-calc( 100% - 160px );width: -o-calc( 100% - 160px );}
                    #codigoplus-review-banner  .codigoplus-review-banner-buttons{padding-top:20px;}
                    #codigoplus-review-banner  .no-thank-button,
                    #codigoplus-review-banner  .main-button{height: 28px;border-width:1px;border-style:solid;border-radius:5px;text-decoration: none;}
                    #codigoplus-review-banner  .main-button{background: #0085ba;border-color: #0073aa #006799 #006799;-webkit-box-shadow: 0 1px 0 #006799;box-shadow: 0 1px 0 #006799;color: #fff;text-decoration: none;text-shadow: 0 -1px 1px #006799,1px 0 1px #006799,0 1px 1px #006799,-1px 0 1px #006799;}
                    #codigoplus-review-banner  .no-thank-button {color: #555;border-color: #cccccc;background: #f7f7f7;-webkit-box-shadow: 0 1px 0 #cccccc;box-shadow: 0 1px 0 #cccccc;vertical-align: top;}
                    #codigoplus-review-banner  .main-button:hover,#codigoplus-review-banner  .main-button:focus{background: #008ec2;border-color: #006799;color: #fff;}
                    #codigoplus-review-banner  .no-thank-button:hover,
                    #codigoplus-review-banner  .no-thank-button:focus{background: #fafafa;border-color: #999;color: #23282d;}
                    @media screen AND (max-width:760px)
                    {
                        #codigoplus-review-banner{position:relative;top:50px;}
                        #codigoplus-review-banner .codigoplus-review-banner-picture{display:none;}
                        #codigoplus-review-banner .codigoplus-review-banner-content{width:calc( 100% - 20px );width:-webkit-calc( 100% - 20px );width:-moz-calc( 100% - 20px );width:-o-calc( 100% - 20px );}
                    }
				</style>
				<div id="codigoplus-review-banner">
					<div class="codigoplus-review-banner-picture">
                        <svg style="width:100px; height: 100px" width="500px" height="500px" viewBox="0 0 500 500" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" data-v-e762bbe2="">
                            <g id="favicon" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <polyline id="Path-9" stroke="#4338ca" stroke-width="73" points="151.541808 386.178919 222.329194 142.178919 273.162059 142.178919 342.541808 386.178919"></polyline>
                                <line x1="187.666729" y1="257.178919" x2="496" y2="257" id="Path-7" stroke="#4338ca" stroke-width="56"></line>
                                <polyline id="Path-10" stroke="#4338ca" stroke-width="54" points="469 187.146188 469 31 31 31 31 469 469 469 469 329.95509"></polyline>
                            </g>
                        </svg>
					</div>
					<div class="codigoplus-review-banner-content">
						<div class="codigoplus-review-banner-text">
							<p>Hey, I noticed you are using the "<?php print $plugin_data[ 'plugin_name' ]; ?>" plugin – <strong>that’s fantastic!</strong>
                                <br>Could you please do me a <strong>BIG</strong> favor and <a href="https://wordpress.org/support/plugin/<?php echo $plugin_slug; ?>/reviews/#new-post" style="color:#1582AB;font-weight:bold;text-decoration: underline;">give it a 5-star rating on WordPress</a>? <br>Just to help us spread the word and boost our motivation. <strong>Thank you!</strong></p>
						</div>
						<div class="codigoplus-review-banner-buttons">
							<form method="post" target="_blank">
								<button class="main-button" onclick="jQuery(this).closest('[id=\'codigoplus-review-banner\']').hide();">Ok, you deserve it</button>
								<input type="hidden" name="codigoplus_promote_banner_plugin" value="<?php echo esc_attr($plugin_slug); ?>" />
								<input type="hidden" name="codigoplus_promote_banner_action" value="set-review" />
								<input type="hidden" name="codigoplus_promote_banner_nonce" value="<?php echo esc_attr(wp_create_nonce(__FILE__)); ?>" />
							</form>
							<form method="post">
								<button class="no-thank-button">No Thanks</button>
								<input type="hidden" name="codigoplus_promote_banner_plugin" value="<?php echo esc_attr($plugin_slug); ?>" />
								<input type="hidden" name="codigoplus_promote_banner_action" value="not-thanks" />
								<input type="hidden" name="codigoplus_promote_banner_nonce" value="<?php echo esc_attr(wp_create_nonce(__FILE__)); ?>" />
							</form>
							<div style="clear:both;display:block;"></div>
							<br />
							<p>Need help using the plugin? Feel free to <a href="https://wordpress.org/support/plugin/<?php echo $plugin_slug; ?>">open a support ticket</a> and I will be happy to help you!</p>
						</div>
						<div style="clear:both;"></div>
					</div>
					<div style="clear:both;"></div>
				</div>
				<?php
				return;
			}
		}
	}
	add_action( 'admin_bar_menu', 'codigoplus_add_promote_banner' );
} // End codigoplus_promote_banner block
