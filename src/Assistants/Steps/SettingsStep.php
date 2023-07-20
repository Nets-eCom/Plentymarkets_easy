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
	public static function stepOne($countries){

		return  [
			"title" => 'NetsEasyPayAssistant.stepOneTitle',
			"sections" => [
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
					"form" => [
						"allowNetsEasyForGuest" => [
							'type' => 'checkbox',
							'defaultValue' => false,
							'options' => [
								'name' => 'NetsEasyPayAssistant.assistantEasyForGuestCheckbox'
							]
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
	public static function stepThree($icons){
        return [
                    "title" => 'NetsEasyPayAssistant.stepTwoThree',
                    "sections" => [
                        [
                            "title" => 'NetsEasyPayAssistant.sectionLogoTitle',
                            "description" => 'NetsEasyPayAssistant.sectionLogoDescription',
                            "form" => [
                                "logo_type_external" => [
                                    'type' => 'toggle',
                                    'defaultValue' => false,
                                    'options' => [
                                        'name' => 'NetsEasyPayAssistant.logoTypeToggle',
                                    ],
                                ],
                            ],
                        ],
                        [
                            "title" => '',
                            "description" => 'NetsEasyPayAssistant.logoURLDescription',
                            "condition" => 'logo_type_external',
                            "form" => [
                                "logo_url" => [
                                    'type' => 'file',
                                    'defaultValue' => '',
                                    'showPreview' => true
                                ],
                            ],
                        ],
                        /*[
                            "title" => 'NetsEasyPayAssistant.sectionPaymentMethodIconTitle',
                            "description" => 'NetsEasyPayAssistant.sectionPaymentMethodIconDescription',
                            "form" => [
                                "NetsEasyPaymentMethodIcon" => [
                                    'type' => 'checkbox',
                                    'defaultValue' => 'false',
                                    'options' => [
                                        'name' => 'NetsEasyPayAssistant.assistantPaymentMethodIconCheckbox'
                                    ]
                                ],
                            ],
                        ],*/
						[
							"title" => 'NetsEasyPayAssistant.IconsTitle',
							"description" => 'NetsEasyPayAssistant.IconsDescription',
							"form" => [
								"icons" => [
									'type' => 'checkboxGroup',
									'defaultValue' => [],
									'options' => [
										"required" => false,
										'name' => 'NetsEasyPayAssistant.icons',
										'checkboxValues' => $icons,
									],
								],
							],
						],

                    ]
					];
	}

	public static function stepFour(){

		return  [
			"title" => 'NetsEasyPayAssistant.stepFourTitle',
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


	
}
