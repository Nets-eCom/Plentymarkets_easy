<?php

namespace NetsEasyPay\Helper;

use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Address\Models\Address as AddressModel;
use Plenty\Modules\Account\Address\Models\AddressRelationType;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Order\Models\Order;


/**
 * Class AddressHelper
 */
class AddressHelper
{
    /**
     * @var AddressRepositoryContract
     */
    private $addressRepo;

    /**
     * Address constructor.
     *
     * @param AddressRepositoryContract $addressRepo
     */
    public function __construct(AddressRepositoryContract $addressRepo)
    {
        $this->addressRepo = $addressRepo;
    }

    /**
     * @param Basket $basket
     *
     * @return AddressModel
     */
    public function getBasketBillingAddress(Basket $basket)
    {
        $address = $this->loadAddress($this->getBillingAddressIdFromCart($basket));
       
        //Logger::error(__FUNCTION__, "BasketBillingAddress",$address);

        return $this->formatAddress($address,$basket->customerId);
    }

    /**
     * @param Basket $basket
     *
     * @return AddressModel
     */
    public function getBasketShippingAddress(Basket $basket)
    {
        $addressId = $this->getShippingAddressIdFromCart($basket) ?
            $this->getShippingAddressIdFromCart($basket) : $this->getBillingAddressIdFromCart($basket);

        $address = $this->loadAddress($addressId);

        //Logger::error(__FUNCTION__, "BasketShippingAddress",$address);

        return $this->formatAddress($address,$basket->customerId);
    }

    /**
     * @param Order $order
     *
     * @return AddressModel
     */
    public function getOrderBillingAddress(Order $order)
    {
        return $this->loadAddress($this->getBillingAddressIdFromOrder($order));
    }

    /**
     * @param Order $order
     *
     * @return AddressModel
     */
    public function getOrderShippingAddress(Order $order)
    {
        return $this->loadAddress($this->getShippingAddressIdFromOrder($order));
    }

    /**
     * @param AddressModel $address
     *
     * @return array
     */
    public function getAddressData($address)
    {
        $data = [];

        if (!$address) {
            return $data;
        }

        $data = $address->toArray();
        $data['town'] = $address->town;
        $data['postalCode'] = $address->postalCode;
        $data['firstname'] = $address->firstName;
        $data['lastname'] = $address->lastName;
        $data['street'] = $address->street;
        $data['houseNumber'] = $address->houseNumber;
        $data['country'] = $address->country->isoCode2;
        $data['addressaddition'] = $address->address3;
        $data['company'] = $address->companyName;

        return $data;
    }

    /**
     * @param Basket $basket
     *
     * @return int
     */
    private function getShippingAddressIdFromCart(Basket $basket)
    {
        return $basket->customerShippingAddressId;
    }

    /**
     * @param Basket $basket
     *
     * @return int
     */
    private function getBillingAddressIdFromCart(Basket $basket)
    {
        return $basket->customerInvoiceAddressId;
    }

    /**
     * @param Order $order
     *
     * @return int|null
     */
    private function getShippingAddressIdFromOrder(Order $order)
    {
        foreach ($order->addressRelations as $relation) {
            if ($relation['typeId'] == AddressRelationType::DELIVERY_ADDRESS) {
                return $relation['addressId'];
            }
        }
    }

    /**
     * @param Order $order
     *
     * @return mixed
     */
    private function getBillingAddressIdFromOrder(Order $order)
    {
        foreach ($order->addressRelations as $relation) {
            if ($relation['typeId'] == AddressRelationType::BILLING_ADDRESS) {
                return $relation['addressId'];
            }
        }
    }

    /**
     * @param int $addressId
     *
     * @return AddressModel
     */
    private function loadAddress($addressId)
    {
        try {
            return $this->addressRepo->findAddressById($addressId);
        } catch (\Exception $e) {
            // Maybe not logged in anymore?
        }
    }

    private function formatAddress($address,$customerId=null){
        
        $data = [];


        if ($address) {

            if($customerId){
                $personalData = $this->getContactInformation($customerId);
                $data['email'] = $personalData['email'];
                $data['phoneNumber'] = $personalData['fixNetPhoneNumber'] ? $this->formatPhone($personalData['fixNetPhoneNumber']) : null;
            }else{
                foreach ($address->options as $key => $option) {
                    if($option->typeId == 5){
                        $data['email'] = $option->value;
                    }
                    if($option->typeId == 4 && $option->value && $option->value != "" ){
                        $data['phoneNumber'] =  $this->formatPhone($option->value);
                    }
                }
            }

            if($address->name1 != ""){
                $data["company"] = [
                    "name" => $address->name1,
                    "contact" => [
                        "firstName" => $address->name2,
                        "lastName" => $address->name3  
                    ]
                ];
            }else{
                $data["privatePerson"] = [
                    "firstName" => $address->name2,
                    "lastName" => $address->name3
                ];
            }
            
            $data['address'] = [
                "addressLine1" => $address->address1 ,
                "addressLine2" => $address->address2 ,
                "postalCode"   => $address->postalCode ,
                "city"         => $address->town ,
                "country" => $this->getCountryById($address->countryId)
            ];
             
            $data['originalData']  = $address;
        }


        return $data;

    }

    private  function getCountryById($id)
    {
        $countryRepository = pluginApp(CountryRepositoryContract::class);
        return $countryRepository->findIsoCode($id, 'isoCode3');
    }

    private function getContactInformation($contactId)
    {

        $contact = pluginApp(ContactRepositoryContract::class)->findContactById((int) $contactId);

        $personalData = [
            "language" => $contact->lang,
            "externalCustomerId" => $contactId,
            "gender" => ($contact->gender == "male") ? "M" : "F",
            "dateOfBirth" => $contact->birthdayAt,
            "email" => $contact->email,
            "fixNetPhoneNumber" => $contact->privatePhone,
            "mobilePhoneNumber" => $contact->privateMobile
        ];

        return $personalData;
    }

    private function formatPhone($phone){
	
            $ccodes = [
                '44' => 'UK (+44)',
                '1' => 'USA (+1)',
                '213' => 'Algeria (+213)',
                '376' => 'Andorra (+376)',
                '244' => 'Angola (+244)',
                '1264' => 'Anguilla (+1264)',
                '1268' => 'Antigua & Barbuda (+1268)',
                '54' => 'Argentina (+54)',
                '374' => 'Armenia (+374)',
                '297' => 'Aruba (+297)',
                '61' => 'Australia (+61)',
                '43' => 'Austria (+43)',
                '994' => 'Azerbaijan (+994)',
                '1242' => 'Bahamas (+1242)',
                '973' => 'Bahrain (+973)',
                '880' => 'Bangladesh (+880)',
                '1246' => 'Barbados (+1246)',
                '375' => 'Belarus (+375)',
                '32' => 'Belgium (+32)',
                '501' => 'Belize (+501)',
                '229' => 'Benin (+229)',
                '1441' => 'Bermuda (+1441)',
                '975' => 'Bhutan (+975)',
                '591' => 'Bolivia (+591)',
                '387' => 'Bosnia Herzegovina (+387)',
                '267' => 'Botswana (+267)',
                '55' => 'Brazil (+55)',
                '673' => 'Brunei (+673)',
                '359' => 'Bulgaria (+359)',
                '226' => 'Burkina Faso (+226)',
                '257' => 'Burundi (+257)',
                '855' => 'Cambodia (+855)',
                '237' => 'Cameroon (+237)',
                '1' => 'Canada (+1)',
                '238' => 'Cape Verde Islands (+238)',
                '1345' => 'Cayman Islands (+1345)',
                '236' => 'Central African Republic (+236)',
                '56' => 'Chile (+56)',
                '86' => 'China (+86)',
                '57' => 'Colombia (+57)',
                '269' => 'Comoros (+269)',
                '242' => 'Congo (+242)',
                '682' => 'Cook Islands (+682)',
                '506' => 'Costa Rica (+506)',
                '385' => 'Croatia (+385)',
                '53' => 'Cuba (+53)',
                '90392' => 'Cyprus North (+90392)',
                '357' => 'Cyprus South (+357)',
                '42' => 'Czech Republic (+42)',
                '45' => 'Denmark (+45)',
                '253' => 'Djibouti (+253)',
                '1809' => 'Dominica (+1809)',
                '1809' => 'Dominican Republic (+1809)',
                '593' => 'Ecuador (+593)',
                '20' => 'Egypt (+20)',
                '503' => 'El Salvador (+503)',
                '240' => 'Equatorial Guinea (+240)',
                '291' => 'Eritrea (+291)',
                '372' => 'Estonia (+372)',
                '251' => 'Ethiopia (+251)',
                '500' => 'Falkland Islands (+500)',
                '298' => 'Faroe Islands (+298)',
                '679' => 'Fiji (+679)',
                '358' => 'Finland (+358)',
                '33' => 'France (+33)',
                '594' => 'French Guiana (+594)',
                '689' => 'French Polynesia (+689)',
                '241' => 'Gabon (+241)',
                '220' => 'Gambia (+220)',
                '7880' => 'Georgia (+7880)',
                '49' => 'Germany (+49)',
                '233' => 'Ghana (+233)',
                '350' => 'Gibraltar (+350)',
                '30' => 'Greece (+30)',
                '299' => 'Greenland (+299)',
                '1473' => 'Grenada (+1473)',
                '590' => 'Guadeloupe (+590)',
                '671' => 'Guam (+671)',
                '502' => 'Guatemala (+502)',
                '224' => 'Guinea (+224)',
                '245' => 'Guinea - Bissau (+245)',
                '592' => 'Guyana (+592)',
                '509' => 'Haiti (+509)',
                '504' => 'Honduras (+504)',
                '852' => 'Hong Kong (+852)',
                '36' => 'Hungary (+36)',
                '354' => 'Iceland (+354)',
                '91' => 'India (+91)',
                '62' => 'Indonesia (+62)',
                '98' => 'Iran (+98)',
                '964' => 'Iraq (+964)',
                '353' => 'Ireland (+353)',
                '972' => 'Israel (+972)',
                '39' => 'Italy (+39)',
                '1876' => 'Jamaica (+1876)',
                '81' => 'Japan (+81)',
                '962' => 'Jordan (+962)',
                '7' => 'Kazakhstan (+7)',
                '254' => 'Kenya (+254)',
                '686' => 'Kiribati (+686)',
                '850' => 'Korea North (+850)',
                '82' => 'Korea South (+82)',
                '965' => 'Kuwait (+965)',
                '996' => 'Kyrgyzstan (+996)',
                '856' => 'Laos (+856)',
                '371' => 'Latvia (+371)',
                '961' => 'Lebanon (+961)',
                '266' => 'Lesotho (+266)',
                '231' => 'Liberia (+231)',
                '218' => 'Libya (+218)',
                '417' => 'Liechtenstein (+417)',
                '370' => 'Lithuania (+370)',
                '352' => 'Luxembourg (+352)',
                '853' => 'Macao (+853)',
                '389' => 'Macedonia (+389)',
                '261' => 'Madagascar (+261)',
                '265' => 'Malawi (+265)',
                '60' => 'Malaysia (+60)',
                '960' => 'Maldives (+960)',
                '223' => 'Mali (+223)',
                '356' => 'Malta (+356)',
                '692' => 'Marshall Islands (+692)',
                '596' => 'Martinique (+596)',
                '222' => 'Mauritania (+222)',
                '269' => 'Mayotte (+269)',
                '52' => 'Mexico (+52)',
                '691' => 'Micronesia (+691)',
                '373' => 'Moldova (+373)',
                '377' => 'Monaco (+377)',
                '976' => 'Mongolia (+976)',
                '1664' => 'Montserrat (+1664)',
                '212' => 'Morocco (+212)',
                '258' => 'Mozambique (+258)',
                '95' => 'Myanmar (+95)',
                '264' => 'Namibia (+264)',
                '674' => 'Nauru (+674)',
                '977' => 'Nepal (+977)',
                '31' => 'Netherlands (+31)',
                '687' => 'New Caledonia (+687)',
                '64' => 'New Zealand (+64)',
                '505' => 'Nicaragua (+505)',
                '227' => 'Niger (+227)',
                '234' => 'Nigeria (+234)',
                '683' => 'Niue (+683)',
                '672' => 'Norfolk Islands (+672)',
                '670' => 'Northern Marianas (+670)',
                '47' => 'Norway (+47)',
                '968' => 'Oman (+968)',
                '680' => 'Palau (+680)',
                '507' => 'Panama (+507)',
                '675' => 'Papua New Guinea (+675)',
                '595' => 'Paraguay (+595)',
                '51' => 'Peru (+51)',
                '63' => 'Philippines (+63)',
                '48' => 'Poland (+48)',
                '351' => 'Portugal (+351)',
                '1787' => 'Puerto Rico (+1787)',
                '974' => 'Qatar (+974)',
                '262' => 'Reunion (+262)',
                '40' => 'Romania (+40)',
                '7' => 'Russia (+7)',
                '250' => 'Rwanda (+250)',
                '378' => 'San Marino (+378)',
                '239' => 'Sao Tome & Principe (+239)',
                '966' => 'Saudi Arabia (+966)',
                '221' => 'Senegal (+221)',
                '381' => 'Serbia (+381)',
                '248' => 'Seychelles (+248)',
                '232' => 'Sierra Leone (+232)',
                '65' => 'Singapore (+65)',
                '421' => 'Slovak Republic (+421)',
                '386' => 'Slovenia (+386)',
                '677' => 'Solomon Islands (+677)',
                '252' => 'Somalia (+252)',
                '27' => 'South Africa (+27)',
                '34' => 'Spain (+34)',
                '94' => 'Sri Lanka (+94)',
                '290' => 'St. Helena (+290)',
                '1869' => 'St. Kitts (+1869)',
                '1758' => 'St. Lucia (+1758)',
                '249' => 'Sudan (+249)',
                '597' => 'Suriname (+597)',
                '268' => 'Swaziland (+268)',
                '46' => 'Sweden (+46)',
                '41' => 'Switzerland (+41)',
                '963' => 'Syria (+963)',
                '886' => 'Taiwan (+886)',
                '7' => 'Tajikstan (+7)',
                '66' => 'Thailand (+66)',
                '228' => 'Togo (+228)',
                '676' => 'Tonga (+676)',
                '1868' => 'Trinidad & Tobago (+1868)',
                '216' => 'Tunisia (+216)',
                '90' => 'Turkey (+90)',
                '7' => 'Turkmenistan (+7)',
                '993' => 'Turkmenistan (+993)',
                '1649' => 'Turks & Caicos Islands (+1649)',
                '688' => 'Tuvalu (+688)',
                '256' => 'Uganda (+256)',
                '380' => 'Ukraine (+380)',
                '971' => 'United Arab Emirates (+971)',
                '598' => 'Uruguay (+598)',
                '7' => 'Uzbekistan (+7)',
                '678' => 'Vanuatu (+678)',
                '379' => 'Vatican City (+379)',
                '58' => 'Venezuela (+58)',
                '84' => 'Vietnam (+84)',
                '84' => 'Virgin Islands - British (+1284)',
                '84' => 'Virgin Islands - US (+1340)',
                '681' => 'Wallis & Futuna (+681)',
                '969' => 'Yemen (North)(+969)',
                '967' => 'Yemen (South)(+967)',
                '260' => 'Zambia (+260)',
                '263' => 'Zimbabwe (+263)',
            ];

            $formatedPhone = null;
                
            krsort( $ccodes );
                
            if(strpos($phone, "+") !== false){
                    
                    $trimedphone = trim($phone, "+");
                
                    foreach( $ccodes as $key=>$value ){
                        if ( substr( $trimedphone, 0, strlen( $key ) ) == $key ){
                            $formatedPhone = [
                                'prefix'=> '+'.$key,
                            ]
                                ;
                            break;
                        }
                    };
                
                    $formatedPhone['number'] = preg_replace(
                                '/\+(?:998|996|995|994|993|992|977|976|975|974|973|972|971|970|968|967|966|965|964|963|962|961|960|886|880|856|855|853|852|850|692|691|690|689|688|687|686|685|683|682|681|680|679|678|677|676|675|674|673|672|670|599|598|597|595|593|592|591|590|509|508|507|506|505|504|503|502|501|500|423|421|420|389|387|386|385|383|382|381|380|379|378|377|376|375|374|373|372|371|370|359|358|357|356|355|354|353|352|351|350|299|298|297|291|290|269|268|267|266|265|264|263|262|261|260|258|257|256|255|254|253|252|251|250|249|248|246|245|244|243|242|241|240|239|238|237|236|235|234|233|232|231|230|229|228|227|226|225|224|223|222|221|220|218|216|213|212|211|98|95|94|93|92|91|90|86|84|82|81|66|65|64|63|62|61|60|58|57|56|55|54|53|52|51|49|48|47|46|45|44\D?1624|44\D?1534|44\D?1481|44|43|41|40|39|36|34|33|32|31|30|27|20|7|1\D?939|1\D?876|1\D?869|1\D?868|1\D?849|1\D?829|1\D?809|1\D?787|1\D?784|1\D?767|1\D?758|1\D?721|1\D?684|1\D?671|1\D?670|1\D?664|1\D?649|1\D?473|1\D?441|1\D?345|1\D?340|1\D?284|1\D?268|1\D?264|1\D?246|1\D?242|1)\D?/',
                                '' , 
                                $phone
                    );    
                            
                return $formatedPhone;
                            
            }else{
                
                return null;
                $prefix = null;
                
                foreach( $ccodes as $key=>$value ){
                    if ( substr( $phone, 0, strlen( $key ) ) == $key ){
                        $prefix ='+'.$key;
                        break;
                    }
                };
                
                if($prefix){
                    
                    $formatedPhone['prefix'] = $prefix;
                        $formatedPhone['number'] = preg_replace(
                                '/\+(?:998|996|995|994|993|992|977|976|975|974|973|972|971|970|968|967|966|965|964|963|962|961|960|886|880|856|855|853|852|850|692|691|690|689|688|687|686|685|683|682|681|680|679|678|677|676|675|674|673|672|670|599|598|597|595|593|592|591|590|509|508|507|506|505|504|503|502|501|500|423|421|420|389|387|386|385|383|382|381|380|379|378|377|376|375|374|373|372|371|370|359|358|357|356|355|354|353|352|351|350|299|298|297|291|290|269|268|267|266|265|264|263|262|261|260|258|257|256|255|254|253|252|251|250|249|248|246|245|244|243|242|241|240|239|238|237|236|235|234|233|232|231|230|229|228|227|226|225|224|223|222|221|220|218|216|213|212|211|98|95|94|93|92|91|90|86|84|82|81|66|65|64|63|62|61|60|58|57|56|55|54|53|52|51|49|48|47|46|45|44\D?1624|44\D?1534|44\D?1481|44|43|41|40|39|36|34|33|32|31|30|27|20|7|1\D?939|1\D?876|1\D?869|1\D?868|1\D?849|1\D?829|1\D?809|1\D?787|1\D?784|1\D?767|1\D?758|1\D?721|1\D?684|1\D?671|1\D?670|1\D?664|1\D?649|1\D?473|1\D?441|1\D?345|1\D?340|1\D?284|1\D?268|1\D?264|1\D?246|1\D?242|1)\D?/',
                                '' , 
                                '+'.$phone
                            );    
                    
                }
                
                return $formatedPhone;
                
            }
       
        
      }
}