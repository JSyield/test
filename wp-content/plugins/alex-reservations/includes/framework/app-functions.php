<?php

// Son funciones que necesito de la aplication pero que no quiero usar directamente
// para poder mantener el codigo de la framework de un plugin a otro

function evavel_config($key, $default = false)
{
	return alexr_config($key, $default = false);
}

function evavel_get_active_languages()
{
	return alexr_get_active_languages();
}

function evavel_datetranslations_translate($string_not_translated, $locale)
{
	return \Alexr\Enums\DateTranslations::translate($string_not_translated, $locale);
}

function evavel_new_notifications_controller()
{
	return new \Alexr\Http\Controllers\NotificationsController();
}

function evavel_shift_class()
{
	return \Alexr\Settings\Shift::class;
}

function evavel_event_class()
{
	return \Alexr\Settings\Event::class;
}

function evavel_tenant_class()
{
	return \Alexr\Models\Restaurant::class;
}

// Creo que no se usa en inguna parte
function evavel_tenant_model_class()
{
	$str = \Evavel\Support\Str::singular(evavel_tenant_resource());
	$str = \Evavel\Support\Str::studly($str);
	return "\Alexr\Models\\" . $str;
}

function evavel_app_routes_params_formats()
{
	return [
		['#:area#', ':area', '.'],
		['#:restaurantId#', ':restaurantId', '[\d]'],
		['#:shiftId#', ':shiftId', '[\d]'],
	];
}

function evavel_app_user_class()
{
	return \Alexr\Models\User::class;
}

function evavel_app_user_role_administrator()
{
	return \Alexr\Enums\UserRole::ADMINISTRATOR;
}

function evavel_app_email_template_class()
{
	return \Alexr\Settings\EmailTemplate::class;
}

function evavel_app_email_reminder_class()
{
	return \Alexr\Settings\EmailReminder::class;
}

function evavel_app_email_custom_class()
{
	return \Alexr\Settings\EmailCustom::class;
}

function evavel_app_email_manager_class()
{
	return \Alexr\Mail\MailManager::class;
}

// LOG DB ACTIONS
function evavel_log_to_file($action, $message)
{
	// Target dir
	$base_dir = evavel_path_to_log_files();
	if (!is_dir($base_dir)) {
		//mkdir($base_dir, 0770, true);
		$folder_created = wp_mkdir_p($base_dir);
	}

	\Evavel\Log\Log::save($base_dir, $action, $message);
}

function evavel_path_to_log_files()
{
	$upload_dir = wp_upload_dir();
	$base_dir = $upload_dir['basedir'].'/'.ALEXR_UPLOAD_FOLDER.'/log';
	return $base_dir;
}


function evavel_has_installed_users_table()
{
	$key = '_srr_installed_users_checked_release_'.ALEXR_RELEASE;
	$srr_tables_checked = get_option($key, false);

	if (current_time( 'timestamp' ) > $srr_tables_checked)
	{
		$installed = WP_ALEXR()->users->installed();
		if ($installed) {
			// Do not check until 1 day again
			update_option( $key, ( current_time( 'timestamp' ) + 24 * 60 * 60 ) );
		}
		return $installed;
	}
	return true;
}

function evavel_force_check_tables_installed()
{
	ALEXR_after_install_check_DB(true);
}
