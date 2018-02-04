<?php  
            $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>";  
	       //**********************************************************************************************************
            // V1.0 : Script de gestion de double détection de mouvement (couplage de détecteur) - Influman 2017
            //*************************************** ******************************************************************
            // recuperation des infos depuis la requete
            $action = getArg("action", $mandatory = true, $default = 'status');
            // API des détecteurs
			$api1 = getArg("api1");
			$api2 = getArg("api2");
            // valeur passée en argument
            $value = getArg("value", $mandatory = false, $default = '');
			// API DU PERIPHERIQUE APPELANT LE SCRIPT
            $periph_id = getArg('eedomus_controller_module_id'); 
 
            $xml .= "<DOUBLEDETECTION>";
			
			$key = $api1.$api2;
			$actual_status = 0;
			$new_status = 0;
			$tabok = false;
			$periph = 0;
			if (loadVariable('DOUBLEDETECTION') != "") {
				$tabdetect = loadVariable('DOUBLEDETECTION');
				if (array_key_exists($key, $tabdetect)) {
					$tabok = true;
					$actual_status = $tabdetect[$key]['status']; 
					$detect1_status = $tabdetect[$key]['detect1'];
					$detect2_status = $tabdetect[$key]['detect2']; 
					$periph = $tabdetect[$key]['periph_id'];
				} else {
					$tabdetect[$key]['status'] = 0;
					$tabdetect[$key]['detect1'] = 0;
					$tabdetect[$key]['detect2'] = 0;
					$tabdetect[$key]['periph_id'] = 0;					
				}
			}
			else {
				$tabdetect[$key]['status'] = 0;
				$tabdetect[$key]['detect1'] = 0;
				$tabdetect[$key]['detect2'] = 0; 
				$tabdetect[$key]['periph_id'] = 0;
			}
			
            if ($action == 'status') {
				if ($periph == 0 || $periph != $periph_id) {
					$tabdetect[$key]['periph_id'] = $periph_id;
					saveVariable('DOUBLEDETECTION', $tabdetect);
				}
				$xml .= "<STATUS>".$actual_status."</STATUS>";
			}
			
			
			if ($action == 'set1') {
				if ($value == 100) { // mouvement detect1
					$detect1_status = 100;
					if ($actual_status == 0) { // aucun mouvement
						$new_status = 101; // mouvement detect1
					}
					if ($actual_status == 102) { // mouvement detect2
						$new_status = 104; // double mouvement 2 vers 1
					}
				}
				if ($value == 0) { // pas de mouvement detect1
					$detect1_status = 0;
					if ($actual_status == 101) { // mouvement detect1
						$new_status = 0; // aucun mouvement
					}
					if ($actual_status == 103 || $actual_status == 104) { // double mouvement
						if ($detect2_status == 100) {
							$new_status = 102;  // mouvement detect2
						} else {
							$new_status = 0; // aucun mouvement
						}
					}
				}
				$tabdetect[$key]['detect1'] = $detect1_status;
				$tabdetect[$key]['status'] = $new_status;
				if ($periph != 0) {
					setValue($periph, $new_status, $verify_value_list = false, $update_only = true);
				}
				saveVariable('DOUBLEDETECTION', $tabdetect);
				$xml .= "<DETECT1>".$detect1_status."</DETECT1>";
				$xml .= "<DETECT2>".$detect2_status."</DETECT2>";
				$xml .= "<PERIPH>".$periph."</PERIPH>";
				$xml .= "<STATUS>".$new_status."</STATUS>";
			}
			
			if ($action == 'set2') {
				if ($value == 100) { // mouvement detect2
					$detect2_status = 100;
					if ($actual_status == 0) { // aucun mouvement
						$new_status = 102; // mouvement detect2
					}
					if ($actual_status == 101) { // mouvement detect1
						$new_status = 103; // double mouvement 1 vers 2
					}
				}
				if ($value == 0) { // pas de mouvement detect2
					$detect2_status = 0;
					if ($actual_status == 102) { // mouvement detect2
						$new_status = 0; // aucun mouvement
					}
					if ($actual_status == 103 || $actual_status == 104) { // double mouvement
						if ($detect1_status == 100) {
							$new_status = 101; // mouvement detect1
						} else {
							$new_status = 0; // aucun mouvement
						}
					}
				}
				$tabdetect[$key]['detect2'] = $detect2_status;
				$tabdetect[$key]['status'] = $new_status;
				if ($periph != 0) {
					setValue($periph, $new_status, $verify_value_list = false, $update_only = true);
				}
				saveVariable('DOUBLEDETECTION', $tabdetect);
				$xml .= "<DETECT1>".$detect1_status."</DETECT1>";
				$xml .= "<DETECT2>".$detect2_status."</DETECT2>";
				$xml .= "<PERIPH>".$periph."</PERIPH>";
				$xml .= "<STATUS>".$new_status."</STATUS>";
			}
            
			
	    

			$xml .= "</DOUBLEDETECTION>";
			sdk_header('text/xml');
			echo $xml;
?>
