<?php 

if ( ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'wp-obscurity-nonce' ) ){
	$saved = _wp_obscurity_page_request();
}

?>
<div class="wrap">
	
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php _e('Obscurity Settings'); ?></h2>
	<div class="clear"></div>
	
	<?php if ( isset($saved) && $saved ) { ?>
		<div id="message" class="updated"><p><?php _e('Success!'); ?></p></div>
	<?php } ?>
	
	<form action="options-general.php?page=wp-obscurity" method="post">
		<table class="form-table">
			<tbody>
				
				<?php foreach( get_obscurity_settings_info() as $slug => $settings ) : 
					$name = $settings['name'];
					$actions = $settings['actions'];
				?>
				
				<tr>
					<th scope="row"><?php echo $name; ?></th>
					<td id="<?php echo $slug ?>">
					
						<p><?php echo $settings['description']; ?></p>
						
						<?php foreach( $actions as $action_slug => $action ) : ?>
							
							<fieldset>
								<label>
									<input type="radio" name="obscurity[<?php echo $slug; ?>]" value="<?php echo $action_slug; ?>" <?php if ($action_slug == get_obscurity_setting($slug)) echo 'checked="checked"'; ?>>
									<?php echo $action; ?>
								</label>
							</fieldset>
						
						<?php endforeach; ?>
						
					</td>
				</tr>
				
				<?php endforeach; ?>
				
			</tbody>
		</table>
		<input type="hidden" value="<?php echo wp_create_nonce('wp-obscurity-nonce'); ?>" name="_wpnonce">
		<p class="submit">
			<input id="submit" class="button button-primary" type="submit" value="Save Changes" name="submit">
		</p>
	</form>

	<h3>Security Vulnerabilities</h3>
	<ul>
		<li>
			<strong>Administrator Username: </strong>
			<?php global $wpdb; 
				$admin = $wpdb->get_results("SELECT user_login FROM {$wpdb->users} WHERE ID = 1");
				$admin = array_shift( $admin );
				if ( !empty($admin) && 'admin' === $admin->user_login ){
					echo '<span style="color:red">admin</span>'
						.' - <b><i>You should change the administrator username to something other than "admin".</i></b>';
				}
				elseif ( !empty($admin) ){
					echo '<span style="color:green">' . $admin->user_login . '</span>';	
				}
			?>
		</li>
		<li>
			<strong>Table Prefix: </strong>
			<?php global $table_prefix;
				if ( 'wp_' === $table_prefix ){
					echo '<span style="color:red">wp_</span>'
						.' - <b><i>You should change the table prefix to something other than "wp_".</i></b>';	
				}
				else {
					echo '<span style="color:green">' . $table_prefix . '</span>';	
				}
			?>
		</li>
		<li>
			<strong>File editing allowed in wp-admin: </strong>
			<?php echo ( defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT ) ? '<span style="color:green">No</span>' : '<span style="color:orange">Yes</span>'; ?>
		</li>
		<li>
			<strong>Using SSL for Admin: </strong>
			<?php echo ( defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN ) ? '<span style="color:green">Yes</span>' : '<span style="color:orange">No</span>'; ?>
		</li>
		<li>
			<strong>Using SSL for Logins: </strong>
			<?php echo ( defined('FORCE_SSL_LOGIN') && FORCE_SSL_LOGIN ) ? '<span style="color:green">Yes</span>' : '<span style="color:orange">No</span>'; ?>
		</li>
	</ul>

</div>

<?php
