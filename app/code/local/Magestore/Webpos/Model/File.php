<?php
class Magestore_Webpos_Model_File
{
	function writeFile($data,$file)
	 {
		$dom = new DOMDocument();
		 $dom->formatOutput = true;
		 $wpos = $dom->createElement( "wpos" );
		 $dom->appendChild( $wpos );
		
		if(isset($data['webpos_admin_adminlogout'])){
			$webpos_admin_adminlogout = $dom->createElement( "webpos_admin_adminlogout" );
			$webpos_admin_adminlogout->appendChild(
			$dom->createTextNode( $data['webpos_admin_adminlogout'] ));
			$wpos->appendChild( $webpos_admin_adminlogout );
		}
		if(isset($data['webpos_admin_adminlogin'])){
			$webpos_admin_adminlogin = $dom->createElement( "webpos_admin_adminlogin" );
			$webpos_admin_adminlogin->appendChild(
			$dom->createTextNode( $data['webpos_admin_adminlogin'] ));
			$wpos->appendChild( $webpos_admin_adminlogin );
		}
		if(isset($data['firstname'])){
			$firstname = $dom->createElement( "firstname" );
			$firstname->appendChild(
			$dom->createTextNode( $data['firstname'] ));
			$wpos->appendChild( $firstname );
		}
		
		if(isset($data['lastname'])){
			$lastname = $dom->createElement( "lastname" );
			$lastname->appendChild(
			$dom->createTextNode( $data['lastname'] ));
			$wpos->appendChild( $lastname );
		}
		
		if(isset($data['username'])){
			$username = $dom->createElement( "username" );
			$username->appendChild(
			$dom->createTextNode( $data['username'] ));
			$wpos->appendChild( $username );
		}
		
		/* Daniel - link to webpos settings */
		if(isset($data['webpos_admin_settingslink'])){
			$webpos_admin_settingslink = $dom->createElement( "webpos_admin_settingslink" );
			$webpos_admin_settingslink->appendChild(
			$dom->createTextNode( $data['webpos_admin_settingslink'] ));
			$wpos->appendChild( $webpos_admin_settingslink );
		}
		/* end */
		
		$dom->saveXML();
		$_file = fopen($file, 'w');
		$result = fwrite($_file, $dom->saveXML());
		fclose($_file);
		
	 }
	
	 
	
	 
	 function readFile($file){
		$webpos_admin_adminlogout = '';
		$webpos_admin_adminlogin = '';
		$userFirstname = '';
		$userLastname = '';
		$userUsername = '';
		$webpos_admin_settingslink = '';
		
		$dom = new DOMDocument();

		$dom->load($file);
		
		$wposNode = $dom->getElementsByTagName("wpos")->item(0);	
		if(isset($wposNode)){
			try{
				$webpos_admin_adminlogout_element = $wposNode->getElementsByTagName("webpos_admin_adminlogout")->item(0);
				if(isset($webpos_admin_adminlogout_element)){
					$webpos_admin_adminlogout =  $webpos_admin_adminlogout_element->nodeValue;
				}
				$webpos_admin_adminlogin_element = $wposNode->getElementsByTagName("webpos_admin_adminlogin")->item(0);
				if(isset($webpos_admin_adminlogin_element)){
					$webpos_admin_adminlogin =  $webpos_admin_adminlogin_element->nodeValue;
				}
				$userFirstname_element = $wposNode->getElementsByTagName("firstname")->item(0);
				if(isset($userFirstname_element)){
					$userFirstname =  $userFirstname_element->nodeValue;
				}
				$userLastname_element = $wposNode->getElementsByTagName("lastname")->item(0);
				if(isset($userLastname_element)){
					$userLastname =  $userLastname_element->nodeValue;
				}
				$userUsername_element = $wposNode->getElementsByTagName("username")->item(0);
				if(isset($userUsername_element)){
					$userUsername =  $userUsername_element->nodeValue;
				}
				/* Daniel - link to webpos settings */
				$webpos_admin_settingslink_element = $wposNode->getElementsByTagName("webpos_admin_settingslink")->item(0);
				if(isset($webpos_admin_settingslink_element)){
					$webpos_admin_settingslink =  $webpos_admin_settingslink_element->nodeValue;
				}
				/* end */
			}catch(Exception $e){}
		}
		$result = array(
						'webpos_admin_adminlogout'=>$webpos_admin_adminlogout,
						'webpos_admin_adminlogin'=>$webpos_admin_adminlogin,
						 'firstname'=>$userFirstname,
						   'lastname'=>$userLastname,
						   'username'=>$userUsername,
						   /* Daniel - link to webpos settings */
						   'webpos_admin_settingslink'=>$webpos_admin_settingslink 
						   /* end */
						   );
		return $result;
	}
	
}