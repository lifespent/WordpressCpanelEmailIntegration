<?php
/*
Plugin Name: Wordpress cPanel Integration
Plugin URI: http://www.innopar.com
Description: This plugin allows users to setup and create email accounts without having to access cPanel.
Version: 1.0
Author: Thomas Hastings
Author URI: http://www.thomashastings.com
License: Private
*/
register_activation_hook(__FILE__,'wmd_install');
register_activation_hook(__FILE__,'wmd_users');


//Create Database Backend
global $wmd_version;
$wmd_version = "1.0";

function wmd_install() {
   global $wmd;
   global $wmd_db_version;

   $table_name = $wpdb->prefix . "wmd_cpanel";
      
   $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
      username text NOT NULL,
	  password text NOT NULL,
	  theme text NOT NULL,
	  good int NOT NULL,
	  UNIQUE KEY id (id)
    );";
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
 dbDelta($sql);

 add_option("wmd_db_version", $wmd_db_version);

}

function wmd_users() {
   global $wmd;
   global $wmd_db_version;

   $table_name = $wpdb->prefix . "wmd_users";
      
   $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
      email text NOT NULL,
	  username text NOT NULL,
	  password text NOT NULL,
	  quota text NOT NULL,
	  UNIQUE KEY id (id)
    );";
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
 dbDelta($sql);

 add_option("wmd_db_version", $wmd_db_version);

}



add_action('admin_menu', 'mt_add_pages');

// action function for above hook
function mt_add_pages() {
    // Add a new top-level menu (ill-advised):
    add_menu_page(__('Wordpress cPanel','menu-test'), __('WP cPanel','menu-test'), 'manage_options', 'massinstall_handle', 'mt_toplevel_page' );

    // Add a submenu to the custom top-level menu:
    add_submenu_page('massinstall_handle', __('cPanel Settings','menu-test'), __('cPanel Settings','menu-test'), 'manage_options', 'sub-page', 'mt_sublevel_page');

    // Add a second submenu to the custom top-level menu:
    add_submenu_page('massinstall_handle', __('Add Email Address','menu-test'), __('Add Email Address','menu-test'), 'manage_options', 'sub-page3', 'mt_sublevel_page3');

    // Add a second submenu to the custom top-level menu:
    add_submenu_page('massinstall_handle', __('Email Users','menu-test'), __('Email Users','menu-test'), 'manage_options', 'sub-page2', 'mt_sublevel_page2');
}

// mt_toplevel_page() displays the page content for the custom Test Toplevel menu
function mt_toplevel_page() {
    echo "<h2>" . __( 'Wordpress cPanel Plugin', 'menu-test' ) . "</h2>";
	echo "<p style='width:75%' display:block; float:left;'>Welcome to the wordpress cPanel plugin. This is version 1.0. We are working on coding more features for integration into cPanel. As of right now
	this will integrate into your current cPanel client and allow you to manage new email accounts. This plugin will not auto-detect current email addresses. In order for this plugin to work appropriately you will need to add all new email accounts through here to manage them through the Wordpress installation. <br /> <br />
	
	<h1>Support</h1>
	If you need support or have questions about this plugin you can email us at <a href=\"mailto:support@innopar.com\">Support [at] Innopar.com</a></p>";
}

// mt_sublevel_page() displays the page content for the first submenu
// of the custom Test Toplevel menu
function mt_sublevel_page() {
    echo "<h2>" . __( 'cPanel Settings', 'menu-test' ) . "</h2>";


if (isset($_POST['cpanel_submit'])) {
	
  		$username = $_REQUEST['username'];
	  	$password = $_REQUEST['password'];
        $theme = $_REQUEST['theme'];
	
   global $wpdb;
   $cpanel_user = $username;
   $cpanel_password = $password;
   $cpanel_skin = $theme;
   $dbuser_pass = $password;
   $verify_db = "wmdu";
   $cpanel_host = $_SERVER['HTTP_HOST'];
	$db_user = $cpanel_user . "_" . $verify_db;


     // START CREATE DB FUNCTION 
					// Update this only if you are experienced user or if script does not work
					// Path to cURL on your server. Usually /usr/bin/curl
					$curl_path = "";

					//////////////////////////////////////
					/* Code below should not be changed */
					//////////////////////////////////////

					function execCommand($command) {
					  global $curl_path;

					  if (!empty($curl_path)) {
					    return exec("$curl_path '$command'");
					  }
					  else {
					    return file_get_contents($command);
					  }
					}




							// Create Database
					    $result = execCommand("http://$cpanel_user:$cpanel_password@$cpanel_host:2082/frontend/$cpanel_skin/sql/addb.html?db=$verify_db");
					
					// Create User
					
					    $result .= execCommand("http://$cpanel_user:$cpanel_password@$cpanel_host:2082/frontend/$cpanel_skin/sql/adduser.html?user={$cpanel_user}_{$verify_db}&pass=$dbuser_pass");

					        // assign user to database
					        $result .= execCommand("http://$cpanel_user:$cpanel_password@$cpanel_host:2082/frontend/$cpanel_skin/sql/addusertodb.html?user={$cpanel_user}_{$verify_db}&db={$cpanel_user}_{$verify_db}&ALL=ALL");


							$con = mysql_connect('localhost', $db_user, $dbuser_pass);
							if (!$con)
							  {
							  echo "<h3 style='color:#FF0000'>Error. Please check your cPanel login info.</h2>";
							  } else {
								   $rows_affected = $wpdb->insert( 'wmd_cpanel', array( 'username' => $username, 'password' => $password, 'theme' => $theme, 'good' => 1 ) );
									echo "<h3>cPanel info has been saved.</h2>";
									mysql_close($con);
									
							}

							




	
} else {
		

	 		$cpanel_settings = <<<EOF
					
						<form method='post' action='#'>
										<table class="table">
			          <tr>
			            <td class="tdleft"><strong>Username</strong><span class="smallas">*</span></td>
			            <td class="tdright"><input class="in" id="f1" name="username" type="text" size="15"></td>
			          </tr>
			          <tr>
			            <td><strong>Password</strong><span class="smallas">*</span></td>
			            <td><input id="f2" class="in"  name="password" type="text" size="15"></td>
			          </tr>
			          <tr>
			            <td><strong>cPanel Theme</strong><span class="smallas">*</span></td>
			            <td ><input id="f20" class="in" name="theme" type="text" size="5"> (Example is x3) </td>
			          </tr>
			          <tr>
					</table>
					<input type="hidden" name="active" value="0">
						<input type="hidden" name="premium" value="1">
				      <input type='submit' name="cpanel_submit" value='SAVE cPanel Info' class="button">
				
					
					</form>
EOF;
	echo "$cpanel_settings";
	
	global $wpdb;
	$is_good = $wpdb->get_row("SELECT * FROM wmd_cpanel WHERE good = 1 ORDER BY id ASC");
	
	$good = $is_good->good;
	
	if (!empty($good)) {
		echo "<h3 style='color:green';>cPanel settings are good!</h3>";
	} else {
		echo "<h3 style='color:red';>cPanel settings are bad!</h3>";
	}
}


}

// mt_sublevel_page2() displays the page content for the second submenu
// of the custom Test Toplevel menu
function mt_sublevel_page2() {
    echo "<h2>" . __( 'Email Settings', 'menu-test' ) . "</h2>";
$i = 0;
	$url = $_SERVER['HTTP_HOST'];

 $option =	$_REQUEST['option'];


// START DEL SESSION 

// START Change ACCOUNT
			// Update this only if you are experienced user or if script does not work
			// Path to cURL on your server. Usually /usr/bin/curl
			$curl_path = "";

			//////////////////////////////////////
			/* Code below should not be changed */
			//////////////////////////////////////

			function execCommand($command) {
			  global $curl_path;

			  if (!empty($curl_path)) {
			    return exec("$curl_path '$command'");
			  }
			  else {
			    return file_get_contents($command);
			  }
			}
			if ($option == "change") {

				 $id =	$_REQUEST['id'];
				
					global $wpdb;
					$user_info = $wpdb->get_row("SELECT * FROM wmd_users WHERE id = $id ");
				
				echo "<h4>Settings for $user_info->email</h4>";
				
					if (isset($_POST['update_email'])) {

						$cpanel_info = $wpdb->get_row("SELECT * FROM wmd_cpanel WHERE good = 1 ORDER BY id ASC");

					   $cpanel_user = $cpanel_info->username;
					   $cpanel_password = $cpanel_info->password;
					   $cpanel_skin = $cpanel_info->theme;
					   $cpanel_host = $_SERVER['HTTP_HOST'];


										$epassword = $_REQUEST['epassword'];
										$epassword1 = $_REQUEST['epassword1'];
										$equota = $_REQUEST['equota'];


									if (!empty($epassword)) {



										if ($epassword != $epassword1) {

											echo "<h3>Error. The new passwords do not match.</h3>";
											die ();
										} else {
											
																		$setpassword = $user_info->password;
																			$setemail = $user_info->email;
																			$setuser = $user_info->username;
																			
																		
								$wpdb->update( 
								'wmd_users', 
								array( 
									'quota' => $equota,	// string
									'password' => $epassword	// integer (number) 
								), 
								array( 'id' => $id ), 
								array( 
									'%s',	// value1
									'%s'	// value2
								), 
								array( '%s' ) 
							);			
				
				
				
				
																
							$result .= execCommand("http://$cpanel_user:$cpanel_password@$cpanel_host:2082/frontend/$cpanel_skin/mail/dopasswdpop.html?email=$setuser&domain=$cpanel_host&quota=$equota&password=$epassword");
											echo "<h3>The user has been updated.</h3>";
										}

									} else {

										$wpdb->query(
											"
											 UPDATE wmd_users
										     SET quota = $equota
										     WHERE id = $id
										     "
										);
										
								$setpassword = $user_info->password;
									$setemail = $user_info->email;
									$setuser = $user_info->username;
									
								
									
									
										
$result .= execCommand("http://$cpanel_user:$cpanel_password@$cpanel_host:2082/frontend/$cpanel_skin/mail/dopasswdpop.html?email=$setuser&domain=$cpanel_host&quota=$equota&password=$setpassword");
										
		echo "<h3>The user has been updated.</h3>";


					}
			}	
						global $wpdb;
						$user_info = $wpdb->get_row("SELECT * FROM wmd_users WHERE id = $id ");
						
						
						echo "<form method='post' action=''>
																<table class=\"table\">
									          <tr>
									            <td class=\"tdleft\"><strong>Username</strong><span class=\"smallas\">*</span></td>
									            <td class=\"tdright\"><input class=\"in\" value=\"$user_info->email\" readonly=\"readonly\" id=\"f1\" name=\"eusername\" type=\"text\" size=\"35\"></td>
									          </tr>
									          <tr>
									            <td><strong>New Password</strong><span class=\"smallas\">*</span></td>
									            <td><input id=\"f2\" class=\"in\"  name=\"epassword\" type=\"password\" size=\"15\"></td>
									          </tr>
									        <tr>
									            <td><strong>Verify Password</strong><span class=\"smallas\">*</span></td>
									            <td><input id=\"f2\" class=\"in\"  name=\"epassword1\" type=\"password\" size=\"15\"></td>
									          </tr>
									          <tr>
											<tr>
									            <td><strong>Quota:</strong><span class=\"smallas\">*</span></td>
									            <td><input id=\"f2\" class=\"in\" value=\"$user_info->quota\" name=\"equota\" type=\"text\" size=\"5\"> MB</td>
									          </tr>
											</table>
											<input type=\"hidden\" name=\"active\" value=\"0\">
												<input type=\"hidden\" name=\"premium\" value=\"1\">
										      <input type='submit' name=\"update_email\" value='Update Email Account' class=\"button\">


											</form> ";
				}
				
				
				
				
				
			
				
			
		if ($option == "change") { 
			die();
		}
		
		if ($option == "delete") {
		
			 $id =	$_REQUEST['id'];
			
		
	


	global $wpdb;
	$cpanel_info = $wpdb->get_row("SELECT * FROM wmd_cpanel WHERE good = 1 ORDER BY id ASC");

   $cpanel_user = $cpanel_info->username;
   $cpanel_password = $cpanel_info->password;
   $cpanel_skin = $cpanel_info->theme;
   $cpanel_host = $_SERVER['HTTP_HOST'];



					global $wpdb;
					$user_info = $wpdb->get_row("SELECT * FROM wmd_users WHERE id = $id ");
	
			// Delete User
	$result = execCommand("http://$cpanel_user:$cpanel_password@$cpanel_host:2082/frontend/$cpanel_skin/mail/realdelpop.html?domain=$cpanel_host&email=$user_info->email");
	
	$wpdb->query(
		"
		DELETE FROM wmd_users
		WHERE id = $id
		"
	);
			
			echo "<h3>The user was deleted!</h3>";
			
		}
	
	$link = $_SERVER['PHP_SELF'];
	
	global $wpdb;
	$users = $wpdb->get_results( 
		"
		SELECT *
		FROM wmd_users
		
		"
	);
	foreach ( $users as $user ) 
	{
		$tableinfo .= <<<EOF
		
			<tr>
				<td>$user->email</td>
				<td>$user->quota MBs</td>
				<td><a href="./admin.php?page=sub-page2&option=delete&id=$user->id" onclick="return confirm('Are you sure you want to delete?')">Delete</a> | <a href="./admin.php?page=sub-page2&option=change&id=$user->id">Change Settings</a> | <a href="http://$url/webmail" target="_blank">Check Email</a></td>
			</tr>
		
EOF;
		$i++;
	}

	$table = <<<EOF
	
		<table cellspacing="5" width="75%">
			<tr>
				<td><h4>Email Address</h4></td>
				<td><h4>Quota</h4></td>
				<td><h4>Options</h4></td>
			</tr>
			$tableinfo
		</table>
EOF;

if ($i == 1) {
	echo "<h3>There is $i registered user.</h3>";
} else {
	echo "<h3>There are $i registered users.</h3>";
}

if ($i > 0) {
echo $table;
}

}

// mt_sublevel_page2() displays the page content for the second submenu
// of the custom Test Toplevel menu
function mt_sublevel_page3() {
	$url = $_SERVER['HTTP_HOST'];
    echo "<h2>" . __( 'Add An Email Account', 'menu-test' ) . "</h2>";


if (isset($_POST['add_email'])) {
	
  		$eusername = $_REQUEST['eusername'];
	  	$epassword = $_REQUEST['epassword'];
		$epassword1 = $_REQUEST['epassword1'];
		
		if ($epassword != $epassword1) {
			die('Error. The passwords do not match.');
		}
        $equota = $_REQUEST['equota'];

 $eaddress = $eusername . "@" . $url; 
		
   global $wpdb;
	$cpanel_info = $wpdb->get_row("SELECT * FROM wmd_cpanel WHERE good = 1 ORDER BY id ASC");
   
   $cpanel_user = $cpanel_info->username;
   $cpanel_password = $cpanel_info->password;
   $cpanel_skin = $cpanel_info->theme;
   $cpanel_host = $_SERVER['HTTP_HOST'];



					global $wpdb;
					$is_already = $wpdb->get_row("SELECT * FROM wmd_users WHERE email = '$eaddress' ");
					
					$is_already = $is_already->email;
					
					
					echo $is_already->email;
					
					if (!empty($is_already)) {
						echo "<h3>Error. The email address already exists.</h3>";
					} else {
						
						// START CREATE DB FUNCTION 
									// Update this only if you are experienced user or if script does not work
									// Path to cURL on your server. Usually /usr/bin/curl
									$curl_path = "";

									//////////////////////////////////////
									/* Code below should not be changed */
									//////////////////////////////////////

									function execCommand($command) {
									  global $curl_path;

									  if (!empty($curl_path)) {
									    return exec("$curl_path '$command'");
									  }
									  else {
									    return file_get_contents($command);
									  }
									}
									
				// Create email
					    $result = execCommand("http://$cpanel_user:$cpanel_password@$cpanel_host:2082/frontend/$cpanel_skin/mail/doaddpop.html?email=$eusername&domain=$cpanel_host&password=$epassword&quota=$equota");
			  $rows_affected = $wpdb->insert( 'wmd_users', array( 'email' => $eaddress, 'username' => $eusername, 'quota' => $equota, 'password' => $epassword) );
					
					echo "<h3>User added successfully</h3>";
					
			
				}
				

	} else {
		



		 	echo "<form method='post' action='#'>
											<table class=\"table\">
				          <tr>
				            <td class=\"tdleft\"><strong>Username</strong><span class=\"smallas\">*</span></td>
				            <td class=\"tdright\"><input class=\"in\" id=\"f1\" name=\"eusername\" type=\"text\" size=\"15\"> @$url</td>
				          </tr>
				          <tr>
				            <td><strong>Password</strong><span class=\"smallas\">*</span></td>
				            <td><input id=\"f2\" class=\"in\"  name=\"epassword\" type=\"password\" size=\"15\"></td>
				          </tr>
				        <tr>
				            <td><strong>Verify Password</strong><span class=\"smallas\">*</span></td>
				            <td><input id=\"f2\" class=\"in\"  name=\"epassword1\" type=\"password\" size=\"15\"></td>
				          </tr>
				          <tr>
						<tr>
				            <td><strong>Quota:</strong><span class=\"smallas\">*</span></td>
				            <td><input id=\"f2\" class=\"in\" value=\"250\" name=\"equota\" type=\"text\" size=\"5\"> MB</td>
				          </tr>
						</table>
						<input type=\"hidden\" name=\"active\" value=\"0\">
							<input type=\"hidden\" name=\"premium\" value=\"1\">
					      <input type='submit' name=\"add_email\" value='Add Email Account' class=\"button\">


						</form>
						";
}

}




?>