<?php

namespace NetsEasyPay\Assistants\Steps;

class SettingsStep
{

	public static function stepZero(){
        return [
                    "title" => 'NetsEasyPayAssistant.stepZeroTitle',
                    "sections" => [
                        [
                            "title" => 'NetsEasyPayAssistant.sectionCredentialTitle',
                            "description" => 'NetsEasyPayAssistant.sectionCredentialDescription',
                            "form" => [
								"secretKey" => [
									'type' => 'text',
									'defaultValue' => '',
									'options' => [
										'required'=> false,
										'name' => 'NetsEasyPayAssistant.secretKey',
									],
								],
								"checkoutKey" => [
									'type' => 'text',
									'defaultValue' => '',
									'options' => [
										'required'=> false,
										'name' => 'NetsEasyPayAssistant.checkoutKey',
									],
								],

                            ],
                        ],
						[
                            "title" => 'NetsEasyPayAssistant.sectionCredentialTitleTest',
                            "description" => 'NetsEasyPayAssistant.sectionCredentialDescriptionTest',
                            "form" => [
								"secretKeyTest" => [
									'type' => 'text',
									'defaultValue' => '',
									'options' => [
										'required'=> true,
										'name' => 'NetsEasyPayAssistant.secretKey',
									],
								],
								"checkoutKeyTest" => [ 
									'type' => 'text',
									'defaultValue' => '',
									'options' => [
										'required'=> true,
										'name' => 'NetsEasyPayAssistant.checkoutKey',
									],
								],
								"UseTestCredentials" => [
									'type' => 'checkbox',
									'defaultValue' => true,
									'options' => [
										'name' => 'NetsEasyPayAssistant.UseTestCredentials'
									]
								],

                            ],
                        ],
                      ]
					];
	}
	public static function stepOne($countries,$methods){

		return  [
			"title" => 'NetsEasyPayAssistant.stepOneTitle',
			"sections" => [
				[
					"title" => 'NetsEasyPayAssistant.paymentMethodsTitle',
					"description" => 'NetsEasyPayAssistant.paymentMethodsDescription',
					"form" => [
						"allowedNexiMethods" => [
							'type' => 'checkboxGroup',
							'defaultValue' => [],
							'options' => [
								"required" => false,
								'name' => 'NetsEasyPayAssistant.AllowedMethods',
								'checkboxValues' => $methods,
							],
						],
					],
				],
				[
					"title" => 'NetsEasyPayAssistant.shippingCountriesTitle',
					"description" => 'NetsEasyPayAssistant.shippingCountriesDescription',
					"form" => [
						"countries" => [
							'type' => 'checkboxGroup',
							'defaultValue' => [],
							'options' => [
								"required" => false,
								'name' => 'NetsEasyPayAssistant.shippingCountries',
								'checkboxValues' => $countries,
							],
						],
					],
				],
				[
					"title" => 'NetsEasyPayAssistant.allowEasyForGuestTitle',
					"description" => 'NetsEasyPayAssistant.allowEasyForGuestDescription',
					"form" =>  [
						"allowNexiForGuest" => [
							'type' => 'checkboxGroup',
							'defaultValue' => [],
							'options' => [
								"required" => false,
								'name' => 'NetsEasyPayAssistant.assistantEasyForGuestCheckbox',
								'checkboxValues' => $methods,
							],
						],
					],
				],
			],
		];
		
	}
	public static function stepTwo(){
		return [
			"title" => 'NetsEasyPayAssistant.stepTwoTitle',
			"sections" => [
				[
					"title" => 'NetsEasyPayAssistant.infoPageTitle',
					"description" => 'NetsEasyPayAssistant.infoPageDescription',
					"form" => [
						"info_page_toggle" => [
							'type' => 'toggle',
							'options' => [
								'name' => 'NetsEasyPayAssistant.infoPageToggle',
							]
						],
					],
				],
				[
					"title" => 'NetsEasyPayAssistant.infoPageTypeTitle',
					"description" => 'NetsEasyPayAssistant.infoPageTypeDescription',
					"condition" => 'info_page_toggle',
					"form" => [
						"info_page_type" => [
							'type' => 'select',
							'defaultValue' => 'internal',
							'options' => [
								"required" => false,
								'name' => 'NetsEasyPayAssistant.infoPageTypeName',
								'listBoxValues' => [
									[
										"caption" => 'NetsEasyPayAssistant.infoPageInternal',
										"value" => 'internal',
									],
									[
										"caption" => 'NetsEasyPayAssistant.infoPageExternal',
										"value" => 'external',
									],
								],
							],
						],
					],
				],
				[
					"title" => '',
					"description" => 'NetsEasyPayAssistant.infoPageNameInternal',
					"condition" => 'info_page_toggle && info_page_type == "internal"',
					"form" => [
						"internal_info_page" => [
							"type" => 'category',
							'defaultValue' => '',
							'isVisible' => "info_page_toggle == true && info_page_type == 'internal'",
							"displaySearch" => true
						],
					],
				],
				[
					"title" => '',
					"description" => '',
					"condition" => 'info_page_toggle && info_page_type == "external"',
					"form" => [
						"external_info_page" => [
							'type' => 'text',
							'defaultValue' => '',
							'options' => [
								'required'=> false,
								'pattern'=> "(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})",
								'name' => 'NetsEasyPayAssistant.infoPageNameExternal',
							],
						],
					],
				],
			],
		];
	}
	public static function stepThree(){

		return  [
			"title" => 'NetsEasyPayAssistant.stepThreeTitle',
			"sections" => [
				[         
					"title" => 'NetsEasyPayAssistant.PaymentSettings',
					"description" => 'NetsEasyPayAssistant.PaymentSettingsDescription',
					"form" => [
						/*"immediatecharge" => [
							'type' => 'checkbox',
							'defaultValue' => false,
							'options' => [
								'name' => 'NetsEasyPayAssistant.assistantImmediateCharge'
							]
						],*/
						"merchanthandlesconsumerdata" => [
							'type' => 'checkbox',
							'defaultValue' => true,
							'options' => [
								'name' => 'NetsEasyPayAssistant.merchanthandlesconsumerdata'
							]
						],
						"BackendNotificationEnabled" => [
							'type' => 'checkbox',
							'defaultValue' => true,
							'options' => [
								'name' => 'NetsEasyPayAssistant.BackendNotificationEnabled'
							]
						]
					],
				],
				
			],
		];
	
	}
	public static function stepFour($StatusList){

		return  [
			"title" => 'NetsEasyPayAssistant.stepFourTitle',    
			"sections" => [ 
				[
					"title" => 'NetsEasyPayAssistant.ChargeEventOrderStatusTitle',
					"description" => 'NetsEasyPayAssistant.ChargeEventOrderStatusDescription',
					"form" => [
						"allowedOrderStatusChangeChargeEvent" => [
							'type' => 'toggle',
							'defaultValue' => true,
							'options' => [
								'name' => 'NetsEasyPayAssistant.allowedOrderStatusChangeChargeEvent',
							],
						],
					],
				],
				[         
					"title" => 'NetsEasyPayAssistant.ChargeEventOrderStatusTitle',
					"description" => 'NetsEasyPayAssistant.ChargeEventOrderStatusDescription',
					"condition" => 'allowedOrderStatusChangeChargeEvent',
					"form" => [
						"ChargeCompletedStatus" => [
							'type' => 'select',
							'defaultValue' => 1,
							'options' => [
								'name' => 'NetsEasyPayAssistant.ChargeCompletedStatus',
								'listBoxValues' => $StatusList,
							],
						],
						"ChargeFaildStatus" => [
							'type' => 'select',
							'defaultValue' => 1,
							'options' => [
								'name' => 'NetsEasyPayAssistant.ChargeFaildStatus',
								'listBoxValues' => $StatusList,
							],
						]
						
					],
				],
				[
					"title" => 'NetsEasyPayAssistant.CancelEventOrderStatusTitle',
					"description" => 'NetsEasyPayAssistant.CancelEventOrderStatusDescription',
					"form" => [
						"allowedOrderStatusChangeCancelEvent" => [
							'type' => 'toggle',
							'defaultValue' => true,
							'options' => [
								'name' => 'NetsEasyPayAssistant.allowedOrderStatusChangeCancelEvent',
							],
						],
					],
				],
				[         
					"title" => 'NetsEasyPayAssistant.CancelEventOrderStatusTitle',
					"description" => 'NetsEasyPayAssistant.CancelEventOrderStatusDescription',
					"condition" => 'allowedOrderStatusChangeCancelEvent',
					"form" => [
						"CancelCompletedStatus" => [
							'type' => 'select',
							'defaultValue' => 1,
							'options' => [
								'name' => 'NetsEasyPayAssistant.CancelCompletedStatus',
								'listBoxValues' => $StatusList,
							],
						],
						"CancelFaildStatus" => [
							'type' => 'select',
							'defaultValue' => 1,
							'options' => [
								'name' => 'NetsEasyPayAssistant.CancelFaildStatus',
								'listBoxValues' => $StatusList,
							],
						]
					],
				],
				[
					"title" => 'NetsEasyPayAssistant.RefundEventOrderStatusTitle',
					"description" => 'NetsEasyPayAssistant.RefundEventOrderStatusDescription',
					"form" => [
						"allowedOrderStatusChangeRefundEvent" => [
							'type' => 'toggle',
							'defaultValue' => true,
							'options' => [
								'name' => 'NetsEasyPayAssistant.allowedOrderStatusChangeRefundEvent',
							],
						],
					],
				],
				[         
					"title" => 'NetsEasyPayAssistant.RefundEventOrderStatusTitle',
					"description" => 'NetsEasyPayAssistant.RefundEventOrderStatusDescription',
					"condition" => 'allowedOrderStatusChangeRefundEvent',
					"form" => [
						"RefundCompletedStatus" => [
							'type' => 'select',
							'defaultValue' => 1,
							'options' => [
								'name' => 'NetsEasyPayAssistant.RefundCompletedStatus',
								'listBoxValues' => $StatusList,
							],
						],
						"RefundFaildStatus" => [
							'type' => 'select',
							'defaultValue' => 1,
							'options' => [
								'name' => 'NetsEasyPayAssistant.RefundFaildStatus',
								'listBoxValues' => $StatusList,
							],
						]
					],
				],
				[ 
					"title" => 'NetsEasyPayAssistant.CreditNoteRefundEventTitle',
					"description" => 'NetsEasyPayAssistant.CreditNoteRefundEventDescription',
					"form" => [
						"allowCreditNoteCreationOnRefund" => [
							'type' => 'toggle',
							'defaultValue' => true,
							'options' => [
								'name' => 'NetsEasyPayAssistant.allowCreditNoteCreationOnRefund',
							],
						],
					],
				],
				[ 
					"title" => 'NetsEasyPayAssistant.CreditNoteCreationStatusTitle',
					"description" => 'NetsEasyPayAssistant.CreditNoteCreationStatusDescription',
					"condition" => 'allowCreditNoteCreationOnRefund',
					"form" => [
						"creditNoteCreationStatus" => [
							'type' => 'select',
							'defaultValue' => 1,
							'options' => [
								'name' => 'NetsEasyPayAssistant.CreditNoteCreationStatusName',
								'listBoxValues' => $StatusList,
							],
						],
					],
				],
				[
					"title" => 'NetsEasyPayAssistant.allowedOrderStatusChangeOnAPIfailureTitle',
					"description" => 'NetsEasyPayAssistant.allowedOrderStatusChangeOnAPIfailureDescription',
					"form" => [
						"allowedOrderStatusChangeOnAPIfailure" => [
							'type' => 'toggle',
							'defaultValue' => true,
							'options' => [
								'name' => 'NetsEasyPayAssistant.allowedOrderStatusChangeOnAPIfailure',
							],
						],
					],
				],
				[         
					"title" => 'NetsEasyPayAssistant.APIcallFaildStatusTitle',
					"description" => 'NetsEasyPayAssistant.APIcallFaildStatusDescription',
					"condition" => 'allowedOrderStatusChangeOnAPIfailure',
					"form" => [
						"APIcallFaildStatus" => [
							'type' => 'select',
							'defaultValue' => 1,
							'options' => [
								'name' => 'NetsEasyPayAssistant.APIcallFaildStatusName',
								'listBoxValues' => $StatusList,
							],
						]
					],
				],
			],
		];
	
	}

	public static function stepFive(){

		return  [
			"title" => 'NetsEasyPayAssistant.stepFiveTitle',    
			"sections" => [ 
				[
					"title" => 'NetsEasyPayAssistant.AppleVerificationTitle',
					"description" => 'NetsEasyPayAssistant.AppleVerificationDescription',
					"form" => [
						"AppleVerification" => [
							'type' => 'toggle',
							'defaultValue' => false,
							'options' => [
								'name' => 'NetsEasyPayAssistant.AppleVerification',
							],
						],
					],
				],
				[         
					"title" => 'NetsEasyPayAssistant.AppleVerificationTextTitle',
					"description" => 'NetsEasyPayAssistant.AppleVerificationTextDescription',
					"condition" => 'AppleVerification',
					"form" => [
						"AppleVerificationText" => [
							'type' => 'textarea',
							'defaultValue' => '',
							'options' => [
								'required'=> false,
								'name' => 'NetsEasyPayAssistant.AppleVerificationText',
							],
						]
						
					],
				],
			],
		];
	
	}
}
