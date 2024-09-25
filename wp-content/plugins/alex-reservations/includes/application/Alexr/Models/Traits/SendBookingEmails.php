<?php

namespace Alexr\Models\Traits;

use Alexr\Enums\BookingStatus;
use Alexr\Enums\Countries;
use Alexr\Models\Booking;
use Alexr\Settings\EmailTemplate;
//use Carbon\Carbon;
use Alexr\Mail\MailManager;

trait SendBookingEmails {

	public $mailManager = null;

    protected function getMailManager() {
	    if ($this->mailManager == null) {
		    $this->mailManager = new MailManager($this->restaurant_id);
	    }
        return $this->mailManager;
    }

    protected function getEmailLinksColor()
    {
	    $color = $this->getMailManager()->config->email_link_color;
        return $color ? $color : '#0ca5e9';
    }

    protected function getEmailButtonBackgroundColor()
    {
	    $color = $this->getMailManager()->config->email_button_bg_color;
	    return $color ? $color : 'black';
    }

	protected function getEmailButtonTextColor()
	{
		$color = $this->getMailManager()->config->email_button_text_color;
		return $color ? $color : 'white';
	}

	public function parseEmailTags($message, $force_lang = null)
	{
        $language = $this->language;
        if (!$language) {
            $language = $this->restaurant->language;
        }
        if ($force_lang != null) {
            $language = $force_lang;
        }

		// Date formatted
		$date_format = $this->getDateFormat();
		//$date_formatted = Carbon::createFromFormat('Y-m-d', $this->date)
		//                        ->locale($language)
		//                        ->translatedFormat($date_format);

        $date_formatted = evavel_date_createFromFormatTranslate('Y-m-d', $this->date, $language, $date_format);

		// Current date formatted
		//$current_date_formatted = evavel_now_timezone($this->restaurant->timezone)
		//	->locale($language)
		//	->translatedFormat($date_format);

		$current_date_formatted = evavel_date_translate(
			evavel_now_timezone_formatted($this->restaurant->timezone, $date_format)
			, $language
		);

        $restaurant_link = $this->restaurant->link_web;
		$restaurant_facebook = $this->restaurant->link_facebook;
		$restaurant_instagram = $this->restaurant->link_instagram;

		$mybooking_link = evavel_view_booking_url($this->uuid);
		$site_link = evavel_site_url();

		$update_link = evavel_edit_booking_url($this->uuid);

		$payment_receipt = '';

        if (defined('ALEXR_PRO_VERSION')) {
            $payment_amount = $this->paidAmountFormatted;

            // Fetching receipt link takes time to call the stripe API
            if (preg_match('#\{payment_receipt\}#', $message)){
	            $payment_link = $this->paymentReceiptLink;
	            $payment_receipt = "<a href='{$payment_link}' style='background: #3a85dc; color: white; padding: 10px; border-radius: 10px; text-decoration: none;'>".__eva('Receipt')."</a>";
            } else {
	            $payment_link = '';
            }


        } else {
            $payment_amount = '';
	        $payment_receipt = '';
        }

        $style_color = 'style="color: '.$this->getEmailLinksColor().'"';

		$tags = [
			'{restaurant}'  => $this->restaurant->name,
			'{restaurant_phone}'  => $this->restaurant->dial_code.' '.$this->restaurant->phone,
			'{name}'        => $this->name,
			'{email}'       => $this->email,
			'{phone}'       => '('.$this->dial_code . ')' . $this->phone,
            '{service}'     => $this->shift_event_name,
			'{party}'       => $this->party,
			'{time}'        => $this->getTimeFormatted(),
			'{end_time}'    => $this->getEndTimeFormatted(),
			'{duration}'    => $this->toDuration($this->duration),
			'{date}'        => $date_formatted,
			'{current_date}'=> $current_date_formatted,
			'{message}'     => $this->notes,
			'{tags}'        => $this->getTagsNames(),
            '{area_table}'  => $this->getAreaTable(),

            '{country}'     => Countries::country($this->country_code),
            '{custom_fields}' => $this->customFieldsEmailHtml(),

			'{mybooking}' => $this->parseButtonBookingLink($mybooking_link, $language),
			'{mybooking_link}'   => '<a '.$style_color.' href="'.$mybooking_link.'">'.$mybooking_link.'</a>',

			'{site_link}'   => '<a '.$style_color.' href="'.$site_link.'">'.$site_link.'</a>',
            '{restaurant_link}' => '<a '.$style_color.' href="'.$restaurant_link.'">'.$restaurant_link.'</a>',
			'{restaurant_facebook}' => '<a '.$style_color.' href="'.$restaurant_facebook.'">'.$restaurant_facebook.'</a>',
			'{restaurant_instagram}' => '<a '.$style_color.' href="'.$restaurant_instagram.'">'.$restaurant_instagram.'</a>',

			'{note_from_us}' => $this->restaurant->note_from_us,
			'{reservation_policy}' => $this->restaurant->reservation_policy,
			'{reservation_number}' => $this->uuid,

			'{add_to_calendar}' => $this->getAddToCalendar(true),
			'{add_to_calendar_text}' => $this->getAddToCalendar(false),

            '{social}' => $this->getSocialLinks(true),
			'{social_text}' => $this->getSocialLinks(false),

            '{payment_amount}' => $payment_amount,
            '{payment_receipt}' => $payment_receipt,

			'{update_button}' => '<a '.$style_color.' href="'.$update_link.'">'.__eva_x('Update reservation', $this->restaurant->language).'</a>',
			'{number_reservations}' => $this->get_metrics(),
            '{booking_modified}' => $this->get_booking_modified_tag()
		];

		$tags = $this->filter_pro($tags);

		foreach ($tags as $tag => $replace) {
            if ($replace == null) $replace = '';
			$message = str_replace($tag, $replace, $message);
		}

        if (defined('ALEXR_PRO_VERSION')) {
	        $message = $this->filter_payment_section($message);
        }

		return $message;
	}

    protected function get_booking_modified_tag()
    {
	    $modified_bookings = Booking::where('parent_booking_id', $this->id)
              ->where('status', '=', BookingStatus::DELETED)
              ->orderBy('id', 'ASC')
              ->get();

        $text = "<div style='padding: 15px;'>";
        foreach($modified_bookings as $booking) {
            $text .= "<div style='text-decoration: line-through'>".$booking->party.' - '.$booking->date.' - '.$this->toHour($booking->time).' ('.$booking->date_created.')</div>';
        }
        $text .= "</div>";

        return $text;
    }


    protected function parseButtonBookingLink($booking_link, $language = null)
    {
        if ($language == null) {
            $language = $this->language;
        }
        $style = "background-color: " . $this->getEmailButtonBackgroundColor() . '; color: ' . $this->getEmailButtonTextColor() . ';';
        ob_start();
        ?>
        <a style="text-align: center; display: block; width: 100%; <?php echo $style; ?> padding: 10px 10px; text-decoration: none;" href="<?php echo $booking_link; ?>"><?php echo __eva_x('My Booking', $language); ?>
        </a>
        <?php
        return ob_get_clean();
        //return '<a href="'.$booking_link.'">'.$booking_link.'</a>';
    }

    protected function get_metrics()
    {
        // General Metrics for the booking date
        $date = $this->date;
        $count_date_pending = Booking::where('date', $date)
                 ->where('uuid', '!=', $this->uuid)
                 ->where('status', BookingStatus::PENDING)
                 ->get();

        $count_date_booked = Booking::where('date', $date)
                    ->where('uuid', '!=', $this->uuid)
                    ->where('status', BookingStatus::BOOKED)
                    ->get();

        $seatings_pending = 0;
        foreach($count_date_pending as $item){
	        $seatings_pending += intval($item->party);
        }

	    $seatings_confirmed = 0;
	    foreach($count_date_booked as $item){
		    $seatings_confirmed += intval($item->party);
	    }

        $message = $seatings_confirmed.' '.__eva('seatings Confirmed').'<br>'.$seatings_pending.' '.__eva('seatings Pending');

        return $message;
    }

	protected function filter_pro($list)
	{
		if (defined('ALEXR_PRO_VERSION')) {
			return $list;
		}

		$pro = ['message', 'tags', 'note_from_us', 'reservation_policy', 'add_to_calendar', 'add_to_calendar_text', 'social', 'social_text', 'custom_fields'];

		foreach ($pro as $key) {
			unset($list['{'.$key.'}']);
		}

		return $list;
	}

    protected function filter_payment_section($message)
    {
	    $paid_amount = $this->paidAmount;

        // If has payment then just remove section tags and leac the content
        if ($paid_amount > 0) {
            $message = str_replace('[payment]', '', $message);
	        $message = str_replace('[/payment]', '', $message);
            return $message;
        }

        // If no payment I have to remove the full section
        else {
	        $message = preg_replace('#\[payment\].+\[\/payment\]#', '', $message);
        }

        return $message;
    }

	protected function getTagsNames()
	{
		$tags_names = '';
		$btags = $this->tags;

		if ($btags)
        {
	        // Filter tags that are public only
	        $btags = $btags->filter(function($tag){
                return $tag->group->is_private != 1 ;
	        });

			$btags = $btags->map(function($tag) {
				return $tag->name;
			})->toArray();

			$tags_names = implode(', ', $btags);
		}
		return $tags_names;
	}

    protected function getAreaTable()
    {
        $text = '';
        if ($this->area_selected_id != null) {
            $text .= $this->areaSelectedName;
        }
	    if ($this->table_selected_id != null) {
		    $text .= ' ('.$this->tableSelectedName.')';
	    }

        return $text;
    }

	protected function getTimeFormatted()
	{
		return $this->toHour($this->time, $this->restaurant->time_format);
	}

	protected function getEndTimeFormatted()
	{
        $end_time = intval($this->time) + intval($this->duration);
		return $this->toHour($end_time, $this->restaurant->time_format);
    }

	protected function getDateFormat()
	{
		$date_format = $this->restaurant->date_format;
		if ($date_format == 'locale') {
			$date_format = 'l j F Y';
		} else {
			$date_formats = alexr_config('app.date_formats_carbon');
			$date_format = isset($date_formats[$date_format]) ? $date_formats[$date_format] : 'l j F Y';
		}
		return $date_format;
	}

	protected function getAddToCalendar($use_logos = false)
	{
		$calendar_links = $this->getCalendarLinks();

        if ($use_logos) {
	        $style = "margin: 0px 20px; display: inline-block;";
            $logos = [
                'apple' => '<img style="height: 32px; width: auto;" src="https://www.cdnlogo.com/logos/a/2/apple.svg"/>',
                'google' => '<img style="height: 32px; width: auto;" src="https://www.cdnlogo.com/logos/g/35/google-icon.svg"/>',
                'outlook' => '<img style="height: 32px; width: auto;" src="https://www.cdnlogo.com/logos/o/50/outlook-icon.svg"/>'
            ];
        } else {
	        $style = "color: " . $this->getEmailLinksColor() . "; margin: 0px 20px; text-decoration: none; display: inline-block;";
	        $logos = [
		        'apple' => 'Apple',
		        'google' => 'Google',
		        'outlook' => 'Outlook'
	        ];
        }

        ob_start();
		?>
			<div style="text-align: center">
				<?php
					foreach($calendar_links as $key => $link) {
						echo '<a href="'.$link.'" style="'.$style.'">'.$logos[$key].'</a>';
					}
				?>
			</div>
		<?php
		return ob_get_clean();
	}

	protected function getSocialLinks($use_logos = false)
	{
		$link_facebook = $this->restaurant->link_facebook;
		$link_instagram = $this->restaurant->link_instagram;

        $facebook_logo = 'facebook';
        $instagram_logo = 'instagram';

        if ($use_logos) {
	        $facebook_logo = '<img style="height: 32px; max-height: 32px; width: auto;" src="https://www.cdnlogo.com/logos/f/91/facebook-icon.svg"/>';
	        $instagram_logo = '<img style="height: 32px; max-height: 32px; width: auto;" src="https://www.cdnlogo.com/logos/i/93/instagram.svg">';
        }

		ob_start();
		echo '<div style="text-align: center">';
		echo '<a href="'.$link_facebook.'" style="display: inline-block; margin: 0px 20px; color: ' . $this->getEmailLinksColor() . '; text-decoration: none;">'.$facebook_logo.'</a>';
		echo '<a href="'.$link_instagram.'" style="display: inline-block; margin: 0px 20px; color: ' . $this->getEmailLinksColor() . '; text-decoration: none;">'.$instagram_logo.'</a>';
		echo '</div>';
		return ob_get_clean();
	}

    // $type es para la notificacion
	protected function sendEmailTemplate($template = 'booking_pending', $lang = null, $type = null, $type_id = null)
	{
        if (!$type) {
            $type = 'email';
        }
        if (!$lang) {
            $lang = $this->restaurant->language;
        }

		$emailTemplate = EmailTemplate::where('restaurant_id', $this->restaurant_id)->first();

		// Check if it enabled
		$enable = $emailTemplate->{$template.'_enable'};
		if ($enable !== 1) return false;

		$subject_b64 = $emailTemplate->{$template}['subject_'.$lang];
		$content_b64 = $emailTemplate->{$template}['content_'.$lang];

		$subject = base64_decode($subject_b64);
		$content = base64_decode($content_b64);

		$subject = $this->parseEmailTags($subject);
		$content = $this->parseEmailTags($content);

		// Booking user email
		$to = $this->email;

		// Is admin email then use the setting
		if (preg_match('#_admin#', $template))
		{
			$to = $emailTemplate->{$template.'_email'};

			// Use the restaurant email
			if (empty($to)) {
				$to = $this->restaurant->email;
			}

			$to_arr = explode(',', $to);
			$to = [];
			foreach($to_arr as $to_email) {
				$to_email = trim($to_email);
				if (filter_var($to_email, FILTER_VALIDATE_EMAIL)){
					$to[] = $to_email;
				}
			}



			if (empty($to)) return false;
		}

		$mailManager = $this->getMailManager();

		$result = $mailManager->send_email($to, $subject, $content);

		//$mm = new MailManager($this->restaurant_id);
		//$result = $mm->send_email($to, $subject, $content);

		// Save in the history of the booking when it is email to user
		if (!preg_match('#_admin#', $template)){
			$this->storeEmailSent($to, $subject, $content, $result, $type, $type_id);
		}

		return $result;
	}




	// Customer emails
	//-----------------------------------------------------------------------

	public function sendEmailPending($lang = 'en')
	{
		return $this->sendEmailTemplate('booking_pending', $lang);
	}

	public function sendEmailPendingPayment($lang = 'en')
	{
		return $this->sendEmailTemplate('booking_pending_payment', $lang, 'email_pending_payment');
	}

	public function sendEmailBooked($lang = 'en')
	{
		return $this->sendEmailTemplate('booking_booked', $lang);
	}

	public function sendEmailConfirmed($lang = 'en')
	{
		return $this->sendEmailTemplate('booking_confirmed', $lang);
	}

	public function sendEmailModified($lang = 'en')
	{
		return $this->sendEmailTemplate('booking_modified', $lang);
	}

	public function sendEmailDenied($lang = 'en')
	{
		return $this->sendEmailTemplate('booking_denied', $lang);
	}

	public function sendEmailCancelled($lang = 'en')
	{
		return $this->sendEmailTemplate('booking_cancelled', $lang);
	}

	public function sendEmailNoShow($lang = 'en')
	{
		return $this->sendEmailTemplate('booking_no_show', $lang);
	}

	public function sendEmailFinished($lang = 'en')
	{
		return $this->sendEmailTemplate('booking_finished', $lang);
	}

	public function sendEmailCustom($subject, $content)
	{
		$to = $this->email;

		$mm = new MailManager($this->restaurant_id);
        $subject = $this->parseEmailTags($subject);
        $content = $this->parseEmailTags($content);

		$result = $mm->send_email($to, $subject, $content);

		$this->storeEmailSent($to, $subject, $content, $result);

		return $result;
	}


	// Admin emails
	//-----------------------------------------------------------------------

	public function sendEmailAdminPending($lang = null)
	{
		return $this->sendEmailTemplate('booking_pending_admin', $lang);
	}

	public function sendEmailAdminBooked($lang = null)
	{
		return $this->sendEmailTemplate('booking_booked_admin', $lang);
	}

	public function sendEmailAdminConfirmed($lang = null)
	{
		return $this->sendEmailTemplate('booking_confirmed_admin', $lang);
	}

	public function sendEmailAdminCancelled($lang = null)
	{
		return $this->sendEmailTemplate('booking_cancelled_admin', $lang);
	}

	public function sendEmailAdminModified($lang = null)
	{
		return $this->sendEmailTemplate('booking_modified_admin', $lang);
	}

	// General funcion used after payment succeed
	//-----------------------------------------------------------------------

    public function sendBookingEmail()
    {
        $booking = $this;

	    $lang = $booking->language;

	    if ($booking->status == BookingStatus::PENDING) {

		    $booking->sendEmailPending($lang);
		    $booking->sendEmailAdminPending();

	    } else if ($booking->status == BookingStatus::BOOKED) {

		    $booking->sendEmailBooked($lang);
		    $booking->sendEmailAdminBooked();

	    } else if ($booking->status == BookingStatus::FINISHED) {

		    $booking->sendEmailFinished($lang);

	    } else if ($booking->status == BookingStatus::DENIED) {

		    $booking->sendEmailDenied($lang);

	    } else if ($booking->status == BookingStatus::CANCELLED) {

		    $booking->sendEmailCancelled($lang);

	    } else if ($booking->status == BookingStatus::NO_SHOW) {

		    $booking->sendEmailNoShow($lang);
	    }
    }

	public function sendSmsNotification()
    {
        $booking = $this;

        if ($booking->agree_receive_sms != 1) return;

        $lang = $booking->language;
        if (!$lang) {
            $lang = $booking->restaurant->language;
        }

		if ($booking->status == BookingStatus::PENDING) {

			$booking->sendSmsPending($lang);

		} else if ($booking->status == BookingStatus::BOOKED) {

			$booking->sendSmsBooked($lang);

		}  else if ($booking->status == BookingStatus::FINISHED) {

			$booking->sendSmsFinished($lang);

		} else if ($booking->status == BookingStatus::DENIED) {

			$booking->sendSmsDenied($lang);

		} else if ($booking->status == BookingStatus::CANCELLED) {

			$booking->sendSmsCancelled($lang);

		} else if ($booking->status == BookingStatus::NO_SHOW) {

			$booking->sendSmsNoShow($lang);

		}
	}

}
