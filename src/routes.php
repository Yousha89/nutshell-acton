<?php
/**
 * Created by PhpStorm.
 * User: ahsuoy
 * Date: 4/10/2016
 * Time: 3:46 PM
 */
$app->group('/acton-nutshell', function() use ($app) {

//   $app->get('/test-accountType', function($request, $response, $args) use ($app) {

// 		$accountTypeParams = array(
// 		   'accountType' => array(

// 			      'name' => 'Private'
// 			 )
// 		);

// 		$newAccountType = $app->nutshellApiDev->call('newAccountType', $accountTypeParams);

// 		print_r($newAccountType);

// 	})->setName('create-new-accountType');
	
	  $app->get('/fromnutshell-toacton-update', function($request, $response, $args) use ($app) {
			
// 				$my_file = 'records.txt';
// 				$handle = fopen($my_file, 'w+') or die('Cannot open file:  '.$my_file);
// 				$data = "testing cronjob : " .time();
// 				foreach($contactRecords as $cKey => $cVal)  $data .= $cKey . " => " . $cVal;
// 				fwrite($handle, $data);
			ini_set('max_execution_time', 400);
			$where = "entrytype='account'";
			$entries = $app->db->simpleSelect('*', 'entries', $where);
			
			$entries = array();
			
			if($app->db->getNumRows() > 0) {
				
				while($entry = $app->db->fetchRow()) {
					
					$entries[] = $entry; 
					
				}
				
			}
			
			//echo "<pre>";
			
			if(count($entries) > 0) {
				
				
				foreach($entries as $entryKey => $entry) {
					
					$accountParams = array(
				
						'accountId'  => $entry['entrynutid'],
						'rev'        => null
						
					);
					
					$account = $app->nutshellApiDev->call('getAccount', $accountParams);
					$account = json_decode(json_encode($account), true);
					
					//print_r($account);
					//echo "<br>" . $entryKey . "<br>";
					
					if($account['accountType']['name'] == 'Customer') {
						
						$accessToken = call_user_func_array($app->checkAccessExpires, array('body' => $app->actOnAccountDev->getAccountInfo(), 'currentActOnAccount' => $app->actOnAccountDev->getCurrentAccount()));
						
						$recordId = $entry['recordid'];
						$listId = explode(":", $entry['recordid']);
						
						$params = array(

								'listid'         => $listId[0],
								'count'          => '1',
								'offset'         => '0',
								'modbefore'      => '',
								'modeafter'      => '',
								'createdbefore'  => '',
								'createdafter'   => '',
								//'fields'         => '',
								'datequalifiers' => true

						);
						
						$headers = array('Accept' => 'application/json', 'Content-Type' => 'application/json');
            $defaultHeaders = array('Authorization' => 'Bearer ' . $accessToken);
						
						$contactRecords = $app->initAccess->actOnPullContactRecord($defaultHeaders, $headers, $listId[0], $recordId, $params);
						
						if(count($contactRecords) > 0 && isset($contactRecords['E-mail Address']) && $contactRecords['E-mail Address'] != "" && isset($contactRecords['Pipeline Stage'])) {
							
							$body = array(
							  
								'E-mail Address'     =>  $contactRecords['E-mail Address'],
								'Pipeline Stage'     =>  'Client'
								
							);
							
							$updatedResults = $app->initAccess->actOnUpdateContactRecord($defaultHeaders, $headers, $listId[0], $recordId, $body);
							$updatedResults = json_decode(json_encode($updatedResults), true);
							
							//print_r($updatedResults);
							
						}
						
						
					}
					
					
				}
				
				
			}
			
		})->setName('from-nutshell-to-acton-update'); 

    $app->get('/test-searchbymail', function($request, $response, $args) use ($app) {


        $email = 'Barbara.Appleton@Agfa.Com';
        $queryParams = array(

            'emailAddressString' => $email

        );

        $res = $app->nutshellApiDev->call('searchByEmail', $queryParams);
        $res = json_decode(json_encode($res), true);

        echo "<pre>";
        $accountTypeParams = array(

            'orderBy'          => 'name',
            'orderDirection'   => 'ASC',
            'limit'            => 100,
            'page'             => 1

        );

        $accountTypes = $app->nutshellApiDev->call('findAccountTypes', $accountTypeParams);
        $accountTypes = json_decode(json_encode($accountTypes), true);
        print_r($res['contacts'][0]['id']);


    })->setName('test-serachByEmail-function');

    $app->post('/create-nutshell-people', function($request, $response, $args) use ($app) {

        ini_set('max_execution_time', 400);

        $formData = $request->getParsedBody();

        $queryParams = array(

            'emailAddressString' => $formData['E-mail_Address']

        );



        $res = $app->nutshellApiDev->call('searchByEmail', $queryParams);
        $res = json_decode(json_encode($res), true);

        if(!count($res['contacts'])) {

            $newContactParams = array(

                'contact'     => array(

                    'name'  => array(

                        'givenName'    => $formData['First_Name'],
                        'familyName'   => $formData['Last_Name'],
                        'displayName'  => $formData['First_Name'] . " " . $formData['Last_Name']
                    ),
                    'email'  => array($formData['E-mail_Address']),
                    'phone'  => array(

                        $formData['Business_Phone'],
                        'business' => $formData['Business_Phone']

                    )

                )

            );

            $app->nutshellApiDev->call('newContact', $newContactParams);



        }

    })->setName('create-new-contact-from-act-on-form');
	
	  $app->post('/generalized-form-submission', function($request, $response, $args) use ($app) {

        $formData = $request->getParsedBody();

        $my_file = $formData['_FORM'].'.txt';
        $handle = fopen($my_file, 'w+') or die('Cannot open file:  '.$my_file);
        $data = "testing cronjob : " .time();
        foreach($formData as $cKey => $cVal)  $data .= $cKey . " => " . $cVal;
        fwrite($handle, $data);


    })->setName('generalized-form-submission');

    $app->post('/sales-requests/iframe/sales-drs-1', function($request, $response, $args) use ($app) {

        ini_set('max_execution_time', 400);

        if(isset($_REQUEST)) {



            $formData = $request->getParsedBody();
            $queryParams = array(

                'emailAddressString' => $formData['E-mail_Address']

            );

            if(isset($formData['E-mail_Address'])) {


                $res = $app->nutshellApiDev->call('searchByEmail', $queryParams);
                $res = json_decode(json_encode($res), true);


                $industryParams = array(

                    'orderBy'          => 'name',
                    'orderDirection'   => 'ASC',
                    'limit'            => 100,
                    'page'             => 1

                );

                $industries = $app->nutshellApiDev->call('findIndustries', $industryParams);
                $industries = json_decode(json_encode($industries), true);
                $contactId = -1;
                $accountId = -1;
                $contactExist = 0;
                $accountExist = 0;

                if(count($res['contacts']) > 0) $contactExist = 1;
                if(count($res['accounts']) > 0) $accountExist = 1;

                if($contactExist == 0) {

                    $newContactParams = array(

                        'contact'     => array(

                            'name'  => array(

                                'givenName'    => $formData['First_Name'],
                                'familyName'   => $formData['Last_Name'],
                                'displayName'  => $formData['First_Name'] . " " . $formData['Last_Name']
                            ),
                            'email'  => array($formData['E-mail_Address']),
                            'phone'  => array(

                                $formData['Business_Phone'],
                                'business' => $formData['Business_Phone']

                            )

                        )

                    );

                    $newContact = $app->nutshellApiDev->call('newContact', $newContactParams);
                    $contactId = $newContact->id;

                    $entryData = array(
                        'entrytype'     => 'contact',
                        'recordid'      => $formData['_SUBMITRECID'],
                        'entrynutid'    => $contactId
                    );

                    $app->db->Insert('entries', $entryData);

                }else {

                    $contactId = $res['contacts'][0]['id'];
                    $entrynutid = $res['contacts'][0]['id'];



                    $where = "entrynutid='".$entrynutid."' AND entrytype='contact'";
                    $search = $app->db->simpleSelect('*', 'entries', $where);
                    $found = 0;
                    if($app->db->getNumRows() > 0) $found = 1;

                    if($found == 1) {

                        $upData = array(
                            'recordid' => $formData['_SUBMITRECID']
                        );

                        $app->db->Update('entries', $upData, $where);

                    }else {

                        $entryData = array(

                            'entrytype'     => 'contact',
                            'recordid'      => $formData['_SUBMITRECID'],
                            'entrynutid'    => $entrynutid

                        );

                        $app->db->Insert('entries', $entryData);
                    }

                }

                if($accountExist == 0) {


                    $industryId = 1;
                    foreach($industries as $key => $val) {

                        if($val['name'] == $formData['Company_Type']) $industryId = $val['id'];

                    }

                    $newAccountParams = array(

                        'account'  => array(

                            'name'           => $formData['Company'],
                            'industryId'  => $industryId,
                            'email'          => array($formData['E-mail_Address']),
                            'phone'          => array(

                                $formData['Business_Phone'],
                                'business' => $formData['Business_Phone']
                            ),

                            'contacts'       => array(array('relationship'=>'First Contact', 'id'=>$contactId))

                        )

                    );


                    $newAccount = $app->nutshellApiDev->call('newAccount', $newAccountParams);
                    $accountId = $newAccount->id;
                    $entryData = array(
                        'entrytype'     => 'account',
                        'recordid'      => $formData['_SUBMITRECID'],
                        'entrynutid'    => $newAccount->id
                    );

                    $app->db->Insert('entries', $entryData);


                }else {

							

                    $entrynutid = $res['accounts'][0]['id'];
                    $accountId = $res['accounts'][0]['id'];
                    $where = "entrynutid='".$entrynutid."' AND entrytype='account'";
                    $search = $app->db->simpleSelect('*', 'entries', $where);
                    $found = false;
                    if($app->db->getNumRows() > 0) $found = true;

                    if($found == 1) {

                        $upData = array(
                            'recordid' => $formData['_SUBMITRECID']
                        );

                        $app->db->Update('entries', $upData, $where);

                    }else {

                        $entryData = array(

                            'entrytype'     => 'account',
                            'recordid'      => $formData['_SUBMITRECID'],
                            'entrynutid'    => $entrynutid

                        );

                        $app->db->Insert('entries', $entryData);
                    }

                }

                if($contactExist == 1 || $accountExist == 1) {

                    $recordId = $formData['_SUBMITRECID'];
                    $listId = explode(":", $recordId);

                    $accessToken = call_user_func_array($app->checkAccessExpires, array('body' => $app->actOnAccountDev->getAccountInfo(), 'currentActOnAccount' => $app->actOnAccountDev->getCurrentAccount()));

                    $params = array(

                        'listid'         => $listId[0],
                        'count'          => '1',
                        'offset'         => '0',
                        'modbefore'      => '',
                        'modeafter'      => '',
                        'createdbefore'  => '',
                        'createdafter'   => '',
                        //'fields'         => '',
                        'datequalifiers' => true

                    );

                    $headers = array('Accept' => 'application/json');
                    $defaultHeaders = array('Authorization' => 'Bearer ' . $accessToken);

                    $contactRecords = $app->initAccess->actOnPullContactRecord($defaultHeaders, $headers, $listId[0], $recordId, $params);
                    
// 									  $my_file = 'records.txt';
// 										$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
// 										$data = $recordId . "  " . $accessToken . " " . $accountId . " ";
// 										foreach($contactRecords as $cKey => $cVal)  $data .= $cKey . " => " . $cVal;
// 										fwrite($handle, $data);
									  
									
                    if($contactExist == 1) {

                        $oldContact = $app->nutshellApiDev->call('getContact', array('contactId' => $contactId, 'rev' => null));
											  
                        $oldContactRev = $oldContact->rev;
                        $contactParams = array(

                            'contact'        => array(

                                'name'  => array(

                                    'givenName'    => $contactRecords['First Name'],
                                    'familyName'   => $contactRecords['Last Name'],
                                    'displayName'  => $contactRecords['First Name'] . " " . $contactRecords['Last Name']

                                ),

                                'phone'          => array(

                                    $contactRecords['Business Phone'],
                                    'business' => $contactRecords['Business Phone']
                                ),
															  
															  'address'        => array(

                                    array(

                                        'state'         => $contactRecords['Business State']

                                    )

                                ),

                                'accounts'          => array(

                                    array(
																				
																			  'id'              => $accountId,
                                        'relationship'    => $contactRecords['Job Title']
																		
																		)

                                )



                            )

                        );

                        $editContactParams = array(

                            'contactId'       => $contactId,
                            'rev'             => $oldContactRev,
                            'contact'         => $contactParams['contact']
                        );

											  
											
                        $editedContact = $app->nutshellApiDev->call('editContact', $editContactParams);
// 											  $my_file = 'edit-info.txt';
// 												$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
// 												$data = "edited contact id : ";
//                         $data .= $editedContact->id;
// 												fwrite($handle, $data);
											  

                    }

                    if($accountExist == 1) {

                        $industryId = 1;
                        foreach($industries as $key => $val) {

                            if($val['name'] == $contactRecords['Company Type']) $industryId = $val['id'];

                        }

                        $oldAccount = $app->nutshellApiDev->call('getAccount', array('accountId' => $accountId, 'rev' => null));
                        $oldAccountRev = $oldAccount->rev;
// 											  $my_file = 'edit-info.txt';
// 												$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
// 												$data = "edited account id : ";
//                         $data .= $oldAccountRev . "  " . $accountId;
// 												fwrite($handle, $data);
                        $accountParams = array(

                            'account'        => array(

                                'name'           => $contactRecords['Company'],
                                'industryId'     => $industryId,

                                'phone'          => array(

                                    $contactRecords['Business Phone'],
                                    'business' => $contactRecords['Business Phone']
                                ),

                                'address'        => array(

                                    array(

                                        'state'         => $contactRecords['Business State']

                                    )

                                ),

                                'contacts'          => array(

                                    array(
																		   
																			'id'              => $contactId,
                                      'relationship'    => $contactRecords['Job Title']
																			
																		)

                                ),
															  'customFields'      => array(
																
																	  'DOT'                   => $contactRecords['DOT Number'],
																	  'Number of Employees'   => $contactRecords['Number of Employees']
																	
																)
                            )

                        );

                        $editAccountParams = array(

                            'accountId'       => $accountId,
                            'rev'             => $oldAccountRev,
                            'account'         => $accountParams['account']
                        );

                        $editedAccount = $app->nutshellApiDev->call('editAccount', $editAccountParams);
											  
// 											  $my_file = 'edit-info.txt';
// 												$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
// 												$data = "edited account id : ";
//                         $data .= $editedAccount->id;
// 												fwrite($handle, $data);
                    }


                    if($accountExist == 1 && $contactExist == 1) {

                        // query leads
											
											  $whereLeads = "recordid='".$formData['_SUBMITRECID']."' AND entrytype='lead'";
											  $leads = $app->db->simpleSelect('*', 'entries', $whereLeads);
											  $existingLeadId = -1;
											  if($app->db->getNumRows() > 0) {
													
													while($leadRow = $app->db->fetchRow()) {
														
														$existingLeadId = $leadRow['entrynutid'];
														
													}
												}
											  
											  if($existingLeadId < 0) {
													
													// create lead

                        $newLeadParams = array(

                            'lead'       => array(

                                'contacts'   => array(
																		
																	array(
																			
																				'id' => $contactId
																		
																		)
																	
																),
															
                                'accounts'   => array(
																
																	array(
																	
																	  'id' => $accountId
																		
																	)
																	
																)

                            )
                        );

                        $newLead = $app->nutshellApiDev->call('newLead', $newLeadParams);
											  
												$newEntry = array(
												 
													'entrytype'   => 'lead',
													'recordid'    => $formData['_SUBMITRECID'],
													'entrynutid'  => $newLead->id
													
												);
													
												$entry = $app->db->Insert('entries', $newEntry);	
													
												}


                    }


                }

            }


        }

    })->setName('create-update-contact-account');

    $app->post('/sales-requests/iframe/sales-drs-2', function() {


        if(isset($_REQUEST)) {

            $my_file = 'keyvals2.txt';
            $handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
            $data = "";

            foreach($_REQUEST as $key => $request) {

                $data .= $key . " => " . $request . '\n';

            }

            fwrite($handle, $data);
					
					

        }

    });

});

$app->group('/acton', function() use ($app) {

    $app->get('/lists', function($request, $response, $args) use ($app) {

        $accessToken = call_user_func_array($app->checkAccessExpires, array('body' => $app->actOnAccountClient->getAccountInfo(), 'currentActOnAccount' => $app->actOnAccountClient->getCurrentAccount()));

        $headers = array('Accept' => 'application/json');
        $defaultHeaders = array('Authorization' => 'Bearer ' . $accessToken);
        $params = array(

            'listingtype'   => 'CONTACT_LIST',
            'count'         => '200',
            'offset'        => '0'

        );

        $lists = $app->initAccess->actOnList($defaultHeaders, $headers, $params);

        $app->initAccess->createListsTable('lists');

        foreach($lists['body']['result'] as $listKey => $list) {

            $params = $list;
            $params['updateTime'] = time();
            $app->initAccess->insertInto($params, 'lists','id');

        }

        $listIds = $app->initAccess->getValuesByKey('lists', 'folderName', array('Tradeshows', 'NTPC', 'Default Folder'));

        ini_set('max_execution_time', 400);

        foreach($listIds as $listKey => $list) {

            foreach($list as $key => $listId) {

                $accessToken = call_user_func_array($app->checkAccessExpires, array('body' => $app->actOnAccountClient->getAccountInfo(), 'currentActOnAccount' => $app->actOnAccountClient->getCurrentAccount()));

                $headers = array('Accept' => 'application/json');

                $defaultHeaders = array('Authorization' => 'Bearer ' . $accessToken);

                $params = array(

                    'listid'         => $listId,
                    'count'          => '1000',
                    'offset'         => '0',
                    'modbefore'      => '',
                    'modeafter'      => '',
                    'createdbefore'  => '',
                    'createdafter'   => '',
                    //'fields'         => '',
                    'datequalifiers' => true

                );

                $contacts = $app->initAccess->actOnListDownLoadById($defaultHeaders, $headers, $params);

                echo "<pre>";

                foreach($contacts as $contactKey => $val) {

                    //foreach($val['body'] as $currentListKey => $listValue) {
                    //print_r($);
                    $contactListId = $val['body']['listId'];

                    foreach($val['body']['data'] as $newContactKey => $newContactData) {


                        foreach($newContactData as $columnKey => $columnVal) {

                            $newContactMeta = array(

                                'key'                             => $val['body']['headers'][$columnKey],
                                'value'                           => $columnVal,
                                'contactLegacyId'                 => $newContactData[0],
                                'updateTime'                      => time(),
                                'updateLevel'                     => 0

                            );

                            echo "new contact meta array<br>";
                            print_r($newContactMeta);

                        }

                        $newContact = array(

                            'legacyId'            => $newContactData[0],
                            'listId'              => $contactListId,
                            'updateTime'          => time(),
                            'updateLevel'         => 0

                        );

                        echo "new contact array<br>";
                        print_r($newContact);
                    }

                    //}
                }

            }
        }

    });

});

$app->get('/', function($request, $response, $args) use ($app) {

    // First test with api call

    echo "<h4 style=\"text-align: center;\">Lets try to fetch some data from current nutshell account!</h4><br>";

    $curParams = array(
        'query'          => null,
        'orderBy'        => 'id',
        'orderDirection' => 'ASC',
        'limit'          => 5,
        'page'           => 1,
        'stubResponses'  => true
    );


    $backupsParams = array();

    //$res = $app->nutshellApi->call('findContacts', $curParams);
    $res = $app->nutshellApiDev->call('findContacts', $curParams);

    $contactCounter = 1;
    ini_set('max_execution_time', 400);

    foreach($res as $contactKey => $contact) {

        $delContactParams = array(
            'contactId' => $contact->id,
            'rev'       => $contact->rev
        );

       
        echo "(" . $contactCounter++ . ") id : " . $contact->id . " rev : " . $contact->rev . "<br>";

    }

})->setName('home');


$app->get('/client-accounts', function($request, $response, $args) use ($app) {

    echo "<h4 style=\"text-align: center;\">Test Client accounts structure.....</h4><br>";

    $curParams = array(

        'query'          => null,
        'orderBy'        => 'id',
        'orderDirection' => 'ASC',
        'limit'          => 100,
        'page'           => 36,
        'stubResponses'  => false

    );

    $res = $app->nutshellApiClient->call('findAccounts', $curParams);

    foreach($res as $accountKey => $account) {

        $accountParams = array(

            'accountId' => $account->id,
            'rev'       => null
        );

        ini_set('max_execution_time', 400);

        $accountDet = $app->nutshellApiClient->call('getAccount', $accountParams);

        $accountDet = json_decode(json_encode($accountDet), true);

        $newAccountParams = array();

        foreach($accountDet as $fieldKey => $field) {

            if($fieldKey == "name" || $fieldKey == "url" || $fieldKey == "phone" || $fieldKey = "address" ) {

                $newAccountParams['account'][$fieldKey] = $field;

            }
        }

        foreach($newAccountParams['account'] as $key => $val) {

            if(is_array($val)) {

                foreach($val as $loneKey => $loneVal) {

                    if($loneKey == "stub") {

                        unset($newAccountParams['account'][$key]);
                    }
                }
            }

        }

        $newAccountCreate = $app->nutshellApiDev->call('newAccount', $newAccountParams);

        $createdAccount = $app->nutshellApiDev->call('getAccount', array('accountId' => $newAccountCreate->id, 'rev' => null));

        print_r($createdAccount);
        echo "<br>";

    }


})->setName('client-accounts-data');

$app->get('/client-leads', function($request, $response, $args) use ($app) {

    echo "<h4 style=\"text-align: center;\">Lets try to fetch some data from current client nutshell account!</h4><br>";

    $curParams = array(
        'query'          => array(),
        'orderBy'        => 'id',
        'orderDirection' => 'ASC',
        'limit'          => 1,
        'page'           => 1,
        'stubResponses'  => true
    );

    ini_set('max_execution_time', 400);

    $res = $app->nutshellApiClient->call('findLeads', $curParams);

    foreach($res as $leadKey => $lead) {

        $leadParams = array(

            'leadId' => $lead->id,
            'rev'       => null

        );

        $leadDet = $app->nutshellApiClient->call('getLead', $leadParams);

        print_r(json_decode(json_encode($leadDet), true));

    }

})->setName('client-leads-data');

$app->get('/client-contacts', function($request, $response, $args) use ($app) {

    echo "<h4 style=\"text-align: center;\">Lets try to fetch some data from current client nutshell account!</h4><br>";



    $curParams = array(
        'query'          => null,
        'orderBy'        => 'id',
        'orderDirection' => 'ASC',
        'limit'          => 100,
        'page'           => 75,
        'stubResponses'  => true
    );

    ini_set('max_execution_time', 400);

    $res = $app->nutshellApiClient->call('findContacts', $curParams);

    foreach($res as $contactKey => $contact) {

        $contactParams = array(

            'contactId' => $contact->id,
            'rev'       => null

        );

        $singleContactData      = array();
        $singleCustomFields     = array();
        $newContactCustomFields = array();
        $newContactParams       = array();

        $contactDet = $app->nutshellApiClient->call('getContact', $contactParams);

        //$singleCustomFields = $app->nutshellApiClient->call('findCustomFields', array());

        $singleContactData = (array) $contactDet;
        //print_r($singleContactData);

        foreach($singleContactData as $fieldKey => $field) {

            if($fieldKey != "address" && $fieldKey != "accounts") {

                if(is_object($field)) $singleContactData[$fieldKey] = (array) $field;
            }
        }

        $contactAddressArray = array();
        $contactAccountArray = array();
        $contactPhoneArray   = array();
        $contactFileArray   = array();

        if(isset($singleContactData['territory'])) {

            if(is_array($singleContactData['territory'])) {

                $singleContactData['territoryId'] = $singleContactData['territory']['id'];

            }
        }

        if(isset($singleContactData['accounts'])) {

            foreach($singleContactData['accounts'] as $accountKey => $account) {

                if(is_object($account)) $contactAddressArray[$accountKey] = (array) $account;

            }



            foreach($contactAccountArray as $accountKey => $account) {

                if(is_array($contact)) {

                    foreach($contact as $fieldKey => $field) {

                        if(is_object($field)) $contactAccountArray[$contactKey][$fieldKey] = (array) $field;
                    }

                }
            }

        }

        if(isset($singleContactData['file'])) {

            foreach($singleContactData['file'] as $fileKey => $file) {

                if(is_object($file)) $contactAddressArray[$fileKey] = (array) $file;

            }



            foreach($contactFileArray as $fileKey => $file) {

                if(is_array($file)) {

                    foreach($file as $fieldKey => $field) {

                        if(is_object($file)) $contactFileArray[$fileKey][$fieldKey] = (array) $file;
                    }

                }
            }

        }

        if(isset($singleContactData['address'])) {

            foreach($singleContactData['address'] as $addressKey => $address) {

                if(is_object($address)) $contactAddressArray[$addressKey] = (array) $address;

            }



            foreach($contactAddressArray as $addressKey => $address) {

                if(is_array($address)) {

                    foreach($address as $fieldKey => $field) {

                        if(is_object($field)) $contactAddressArray[$addressKey][$fieldKey] = (array) $field;
                    }

                }
            }

        }

        if(isset($singleContactData['phone'])) {

            foreach($singleContactData['phone'] as $phoneKey => $phone) {

                if(is_object($phone)) $contactPhoneArray[$phoneKey] = (array) $phone;

            }



            foreach($contactPhoneArray as $phoneKey => $phone) {

                if(is_array($phone)) {

                    foreach($phone as $fieldKey => $field) {

                        if(is_object($field)) $contactPhoneArray[$phoneKey][$fieldKey] = (array) $field;
                    }

                }
            }
        }

        foreach($contactAddressArray as $addressKey => $address) {

            if(is_array($address)) {

                $tempCheck = 0;
                $tempKey = "";
                foreach($address as $fieldKey => $field) {

                    if($fieldKey == "clientType" || $fieldKey == "size" || $fieldKey == "mime" || $fieldKey == "uri" || $fieldKey == "deletedTime" || $fieldKey == "regions" || $fieldKey == "entityType" || $fieldKey == "modifiedTime" || $fieldKey == "stub" || $fieldKey == "id" || $fieldKey == "rev") {

                        unset($contactAddressArray[$addressKey][$fieldKey]);

                    }
                }

            }
        }


        $singleContactData['address']  = $contactAddressArray;
        $singleContactData['phone']  = $contactPhoneArray;
        $singleContactData['accounts'] = $contactAccountArray;
        $singleContactData['file'] = $contactFileArray;





        foreach($singleContactData as $key => $val) {
            if(is_null($val) || !count($val)) {

                continue;

            }else if($key != "avatar" && $key != "owner" && $key != "leads" && $key != "notes" && $key != "creator" && $key != "territory" && $key != "rev" && $key != "id" && $key != "contactedCount" && $key != "entityType" && $key != "creator" && $key != "notes" && $key != "lastContactedDate" && $key != "modifiedTime" && $key != "lastContactedDate" && $key != "htmlUrl") {

                if(is_array($val)) {

                    if(count($val)) $newContactParams['contact'][$key] = $val;

                }else {

                    $newContactParams['contact'][$key] = $val;

                }


            }
        }

        /*$newContactParams['contact']['name'] = $singleContactData['name'];
        $newContactParams['contact']['phone'] = $singleContactData['phone'];
        $newContactParams['contact']['address'] = $singleContactData['address'];
        $newContactParams['contact']['owner'] = $singleContactData['owner'];
        $newContactParams['contact']['leads'] = $singleContactData['leads'];
        $newContactParams['contact']['accounts'] = $singleContactData['accounts'];
        $newContactParams['contact']['notes'] = $singleContactData['notes'];*/


        $createNewContact = $app->nutshellApiDev->call('newContact', $newContactParams);

        $createdContact = $app->nutshellApiDev->call('getContact', array('contactId' => $createNewContact->id, 'rev' => null));

        print_r($createdContact);

        echo "<br>";

        unset($contactPhoneArray);
        unset($contactAddressArray);
        unset($contactAccountArray);
        unset($contactAccountArray);


    }

})->setName('test-client-data');

$app->get('/client-products', function($request, $response, $args) use ($app) {

    $curParams = array(

        'orderBy'        => 'id',
        'orderDirection' => 'ASC',
        'limit'          => 100,
        'page'           => 1,
        'stubResponses'  => false

    );

    ini_set('max_execution_time', 400);

    $res = $app->nutshellApiClient->call('findProducts', $curParams);

    foreach($res as $productKey => $product) {

        $productParams = array(

            'productId'  => $product->id,
            'rev'        => null

        );

        $productDet = $app->nutshellApiClient->call('getProduct', $productParams);

        $productDet = (json_decode(json_encode($productDet), true));
        $newProductParams = array();
        foreach($productDet as $key => $val) {

            if($key == "name" || $key == "type" || $key == "sku" || $key == "unit") {

                $newProductParams['product'][$key] = $val;
            }

        }

        if(isset($newProductParams['product'])) {

            $createdProduct = $app->nutshellApiDev->call('newProduct', $newProductParams);
            print_r($createdProduct);
            echo "<br>";
        }
    }

})->setName('client-products');

$app->get('/client-markets', function($request, $response, $args) use ($app) {

    $curParams = array(

        'orderBy'         => 'id',
        'orderDirection'  => 'ASC',
        'limit'           => 100,
        'page'            => 1,
        'stubResponse'    => false
    );

    ini_set('max_execution_time', 400);

    $res = $app->nutshellApiClient->call('findMarkets', $curParams);

    foreach($res as $marketKey => $market) {

        print_r(json_decode(json_encode($market), true));
    }

})->setName('client-markets');

$app->get('/client-account-types', function($request, $response, $args) use ($app) {

    $curParams = array(

        'orderBy'         => 'id',
        'orderDirection'  => 'ASC',
        'limit'           => 100,
        'page'            => 1

    );

    ini_set('max_execution_time', 400);

    $res = $app->nutshellApiDev->call('findAccountTypes', $curParams);

    foreach($res as $accountTypesKey => $accountType) {

        print_r(json_decode(json_encode($accountType), true));

        echo "<br>";

    }

})->setName('client-account-types');

$app->get('/client-sources', function($request, $response, $args) use ($app) {

    $curParams = array(

        'orderBy'         => 'name',
        'orderDirection'  => 'ASC',
        'limit'           => 100,
        'page'            => 1

    );

    ini_set('max_execution_time', 40);

    $res = $app->nutshellApiClient->call('findSources', $curParams);

    foreach($res as $sourcesKey => $source) {

        $tempSource = json_decode(json_encode($source), true);

        $sourceParams = array(

            'name' => $tempSource['name']

        );

        $newSource = $app->nutshellApiDev->call('newSource', $sourceParams);

        print_r($newSource);
        echo "<br>";

    }

})->setName('client-sources');

$app->get('/client-tags', function($request, $response, $args) use ($app) {

    $curParams = array();

    ini_set('max_execution_time', 400);

    $res = $app->nutshellApiClient->call('findTags', $curParams);

    foreach($res as $tagsKey => $tag) {

        $tempTagsArray = json_decode(json_encode($tag), true);

        if(is_array($tempTagsArray)) {

            foreach($tempTagsArray as $key => $val) {

                $tagParams = array(

                    'tag'  => array(

                        'name'        => $val,
                        'entityType'  => $tagsKey
                    )
                );

                $newTag = $app->nutshellApiDev->call('newTag', $tagParams);

                print_r($newTag);
                echo "<br>";
            }
        }

    }

})->setName('client-tags');