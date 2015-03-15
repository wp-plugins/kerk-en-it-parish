<?php
	if (!function_exists('kei_dashboard_render_list_page')) {

	function kei_dashboard_render_list_page() { ?>
	<div class="wrap">
		<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
<?php
$kei_options = get_option('kei_options');
if ($_SERVER['REQUEST_METHOD'] === 'POST') :
	if(isset($_POST['kei-reset-masstype'])) :
		wp_redirect(admin_url('admin.php?page=' . $_REQUEST['page']));
	elseif(isset($_POST['kei-save-masstype'])) :
		//$id = kei_post_val('id');
		//$title = kei_post_val('title');
		//$activeWidget = kei_post_isChecked('activeWidget');

		$kei_options['targetAudience'] = kei_post_val('targetAudience');

		update_option('kei_options', $kei_options);
	endif;
endif;
?>
	<form id="masstype-filter" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<div id="naw">
			<div>
				<label for="massType_ID">
					<?php _e('Target Audience', 'kei-parish'); ?>:
				</label>
				<?php
					echo '<select id="targetAudience" name="targetAudience" style="width:367px;">';
					$targetAudiences = array(
						1 => __('Parishioners', 'kei-parish'),
						2 => __('Evangelization', 'kei-parish'),
						3 => __('Parishioners and Evangelization', 'kei-parish')
					);
					foreach($targetAudiences as $key => $value)
					{
						echo '<option value="' . $key . '"' . ((int)$kei_options['targetAudience'] == $key ? ' selected="selected"' : '') . '>' . $value . '</option>';
					}
					echo '</select>';
				?>
			</div>
		</div>
		<div style="clear:both;">

			<?php
				$other_attributes = array( 'id' => 'masstype' );
				submit_button( (empty($data[0]->ID) ? __('Save', 'kei-parish') : __('Save Changes', 'kei-parish') ), 'primary', 'kei-save-masstype', false, $other_attributes );
				echo '&nbsp;&nbsp;';
				submit_button( __('Reset', 'kei-parish'), 'secondary', 'kei-reset-masstype', false);
			?>
		</div>
	</form>
</div>
<?php
	}
	}
?>