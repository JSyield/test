<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class SRR_Factory {

    public static function createRestaurants()
    {
        global $wpdb;

        $db_restaurants = SRR_DB_Restaurants::$table_db;
        $list = $wpdb->get_col("SELECT * FROM {$db_restaurants}");

        if (count($list) < 3){
            $list_rest = ['Madrid', 'New York', 'Paris', 'Hong Kong', 'Beijing', 'Oslo', 'Roma'];
            foreach($list_rest as $name){
                self::restaurant($name);
            }
        }

        $db_bookings = SRR_DB_Bookings::$table_db;
        $list = $wpdb->get_col("SELECT * FROM {$db_bookings}");
        $count_restaurants = count($wpdb->get_col("SELECT * FROM {$db_restaurants}"));

        // Generate 500 bookings
        //if (count($list) < 500){
        //    for ($i = 0; $i < 500; $i++){
        //        self::booking(rand(1,$count_restaurants));
        //    }
        //}
    }

    public static function restaurant($name)
    {
        global $wpdb;
        $wpdb->insert(SRR_DB_Restaurants::$table_db, [
            'name' => $name,
            'date_created' =>  date('Y-m-d h:i:s'),
            'date_modified' =>  date('Y-m-d h:i:s')
        ]);
    }


    public static function booking($restaurant_id, $date = null)
    {
        global $wpdb;

        $names = ['John Doe', 'Alex Pas', 'Maria Luque', 'Bruno Sol', 'Eva Valle', 'Jane May', 'Leon Mijo'];
        $index = rand(0, count($names) - 1);

        $random_hours_ahead = - rand(24, 720 * 24) * 3600;

        $name = $names[$index] . rand(100,119);
		$name_first_last = explode(' ',$name);

        $email = str_replace(' ', '_', strtolower($name)).'@gmail.com';

        $customer_id = self::get_customer($name, $email, $restaurant_id);

		if (!$date) {
			$date = date('Y-m-d', time() + $random_hours_ahead);
		}

		$party = rand(1,20);

		$args = [
			'restaurant_id' => $restaurant_id,
			'customer_id' => $customer_id,
			'type' => \Alexr\Enums\BookingType::ONLINE, // inhouse , online
			'date' => $date,
			'time' => 60 * rand(720, 1200),
			'party' => $party,
			'duration' => rand(60,180),
			'first_name' => $name_first_last[0],
			'last_name' => $name_first_last[1],
			'email' => $email,
			'country_code' => 'US',
			'dial_code' => '1',
			'phone' => '620289123',
			'status' => \Alexr\Enums\BookingStatus::BOOKED,
			'spend' => $party * rand(10,50),
			'notes' => 'Some notes',
			'date_created' =>  date('Y-m-d h:i:s'),
			'date_modified' =>  date('Y-m-d h:i:s')
		];

		$booking = new \Alexr\Models\Booking($args);

		$booking->save();

		// Add some tags

        //$wpdb->insert(SRR_DB_Bookings::$table_db, );
    }

    public static function get_customer($name, $email, $restaurant_id)
    {
        global $wpdb;

        $table_name = SRR_DB_Customers::$table_db;
        $sql = "SELECT id FROM {$table_name} WHERE email='{$email}' AND restaurant_id='{$restaurant_id}' LIMIT 1";
        $id = $wpdb->get_var($sql);

		$companies = ['Sony', 'Apple', 'Micro', 'Laravel', 'Elap'];

        if (!$id){

			$visits = rand(1,40);
	        $spend = $visits * rand(30,200);
	        $spend_per_visit = intval($spend / $visits);

			$names = explode(' ',$name);
			if (is_array($names) && count($names) >= 2){
				$f_name = $names[0];
				$l_name = $names[1];
			} else {
				$f_name = $name;
				$l_name = $name;
			}

			$params = [
				'restaurant_id' => $restaurant_id,
				'company' => $companies[rand(0,count($companies)-1)],
				'email' => $email,
				'first_name' => $f_name,
				'last_name' => $l_name,
				'name' => $name,
				'visits' => $visits,
				'spend' => $spend,
				'spend_per_visit' => $spend_per_visit,
				'gender' => rand(1,10) < 6 ? 'male' : 'female',
				'birthday' => '2000-01-23',
				'country_code' => 'US',
				'dial_code' => '1',
				'phone' => '629837281',
				'date_created' =>  date('Y-m-d h:i:s'),
				'date_modified' =>  date('Y-m-d h:i:s'),
			];

			//ray($params);

            $wpdb->insert($table_name, $params);
            $id = $wpdb->get_var($sql);

			//ray('INSERTED in ' . $table_name . ': ' . $id);
        }

        //srrlog($sql);
        //srrlog($id);

	    //ray($id);
        return $id;
    }
}
