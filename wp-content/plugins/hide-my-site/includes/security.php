<?php

class hidemysite_security{
	
	public function __construct(){
		$this->needs_to_wait = 0;
		//add to front and backend inits
		add_action('init', array($this,'create_hidemysiteSecure'));		
	}
	
	//set up custom table in database, if it's not there yet
	function create_hidemysiteSecure(){
		if (!is_admin() and (get_option('hide_my_site_bruteforce', 1) == 1)) { //only create table on front end, so that this feature can be disabled via admin in case of malfunction
			global $wpdb;
		 
			//create the name of the table including the wordpress prefix (wp_ etc)
			$search_table = $wpdb->prefix . "hidemysiteSecure";
			//$wpdb->show_errors(); 
		 
			//check if there are any tables of that name already
			if($wpdb->get_var("show tables like '$search_table'") !== $search_table) 
			{
				//create your sql
				$sql =  "CREATE TABLE ". $search_table . " (
							  id mediumint(12) NOT NULL AUTO_INCREMENT,
							  ip text NOT NULL,
							  time VARCHAR (20) NOT NULL, 
							  repeated_fails VARCHAR (20) NOT NULL,
							  UNIQUE KEY id (id));";
			}
		 
			//include the wordpress db functions
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			if(isset($sql)){
				dbDelta($sql);
			}
		 
			//register the new table with the wpdb object
			if (!isset($wpdb->hidemysiteSecure)) 
			{
				$wpdb->hidemysiteSecure = $search_table; 
				//add the shortcut so you can use $wpdb->hidemysiteSecure
				$wpdb->tables[] = str_replace($wpdb->prefix, '', $search_table); 
			}
		}
	}
	
	function track_ip(){
		if(get_option('hide_my_site_bruteforce', 1) == 1) { //confirm brute force protection enabled
			global $wpdb;
			$dbName = $wpdb->prefix . "hidemysiteSecure";
			$userip = $_SERVER['REMOTE_ADDR'];

			$previousRecord = $wpdb->get_row("SELECT * FROM $dbName WHERE ip = '$userip'");
			if ($previousRecord != null){ //previous failure exists
				$prev_fails = $previousRecord->repeated_fails;
				$elapsed = (time() - ($previousRecord->time)); //time elapsed, in minutes, since previous failure
				if( ($prev_fails > 6) ) { //this user has too many failed logins recently. let's check to see if he has waited long enough...
					$punishable_fails = $prev_fails - 6;
					$punishable_fails_next = $prev_fails - 5;
					$min_wait_time_now = (pow(2, $punishable_fails)) + 20;
					$min_wait_time_next = (pow(2, $punishable_fails_next)) + 20;
					if ($min_wait_time_now > 1800) {
						$min_wait_time_now = 1800;
						$min_wait_time_next = 1800;
					}
					if ($elapsed > $min_wait_time_now) { //previous failure was a long time ago
						$prev_fails = 0; //reset previous fails to 0
					} else { //user hasn't waited long enough
						$this->needs_to_wait = 1;
						$this->remaining_wait_time = $min_wait_time_next;
					}
				}
				
				//increase fail count by 1
				$new_fails = $prev_fails + 1;
				//update ip to current failure count at current time				
				$where_array = array("ip" => "$userip");
				$wpdb->update( $dbName, array( 
					'repeated_fails' => $new_fails,  
					'time' => time()
				),  $where_array);
				//return "Record already exists. Previous failure was " . $elapsed . " seconds ago. You must wait $min_wait_time seconds before next attempt";
			} else {
				//ip record does not exist. create it
				$wpdb->insert( $dbName, array( 
					'ip' => $_SERVER['REMOTE_ADDR'],  
					'time' => time(), 
					'repeated_fails' => 1
				));
				//return "record does not exist";
			}
			
		}

	}
	
	function get_alert(){
		if ($this->needs_to_wait == 1) {
			return "<script>alert('You have too many failed login attempts recently. You must wait ".$this->remaining_wait_time." seconds before your next login attempt.')</script>";
		}
	}
	
	function remove_ip(){
		if(get_option('hide_my_site_bruteforce', 1) == 1) { //confirm brute force protection enabled
			global $wpdb;
			$dbName = $wpdb->prefix . "hidemysiteSecure";
			$userip = $_SERVER['REMOTE_ADDR'];
			$previousRecord = $wpdb->get_row("SELECT * FROM $dbName WHERE ip = '$userip'");
			if ($previousRecord != null){ //previous failure exists
				$wpdb->delete( $dbName, array( 'ip' => $userip ) );
			}
		}
	}

}

?>