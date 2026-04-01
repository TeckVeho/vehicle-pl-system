<?php
    function convertLicenseClassification($licenseClassification) 
    {
        if ($licenseClassification) {
            if ((int)$licenseClassification < 3500) {
                return '普通';
            }
    
            if ((int)$licenseClassification >= 3500 && (int)$licenseClassification < 7500) {
                return '準中型';
            }
    
            if ((int)$licenseClassification >= 7500 && (int)$licenseClassification < 11000) {
                return '中型';
            }
    
            if ((int)$licenseClassification >= 11000) {
                return '大型';
            }
        }
    
        return null;
    }

    function convertMaintenanceLease($key, $valueData) {
        // Kiểm tra xem maintenance_lease có tồn tại và có phần tử không
        if (!isset($valueData['maintenance_lease']) || empty($valueData['maintenance_lease']) || !isset($valueData['maintenance_lease'][0])) {
            return null;
        }
        
        if ($key == 'tel') {
            return $valueData['maintenance_lease'][0]['tel'] ?? null;
        } else if ($key == 'garage') {
            return $valueData['maintenance_lease'][0]['garage'] ?? null;
        } else if ($key == 'leasing_company') {
            return $valueData['maintenance_lease'][0]['leasing_company'] ?? null;
        } else if ($key == 'leasing_period') {
            return $valueData['maintenance_lease'][0]['leasing_period'] ?? null;
        } else if ($key == 'end_of_leasing') {
            return $valueData['maintenance_lease'][0]['end_of_leasing'] ?? null;
        } else if ($key == 'start_of_leasing') {
            return $valueData['maintenance_lease'][0]['start_of_leasing'] ?? null;
        }
        return null;
    }

    function convertNumber($key, $valueData) {
        // convert sang kiểu 123,456,789
        $number = (int)$valueData;
        return number_format($number);
    }

    $numberFields = [
        'voluntary_premium', 'length', 'width', 'height', 'maximum_loading_capacity', 
        'vehicle_total_weight', 'optional_detail', 'monthly_mileage', 'maintenance_lease_fee'
    ];
?>

<div class="ritz grid-container" dir="ltr">
<table class="waffle" cellspacing="0" cellpadding="0">
    <thead>
    <tr>
        @foreach($data['vehicleStyleShow'] as $key => $value)
            <th>{{$value['label']}}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($data['vehicles'] as $keyData => $valueData)
        <tr>
        
            @foreach($data['vehicleStyleShow'] as $key => $value)

                @if($value['key'] == 'no_number_plate')
                    <td class="s3">
                            {{ isset($valueData['plate_history'][0]['no_number_plate']) ? $valueData['plate_history'][0]['no_number_plate'] : '' }}
                    </td>

                @elseif(in_array($value['key'], ['tel', 'garage', 'leasing_company', 'leasing_period', 'end_of_leasing', 'start_of_leasing']))
                    <td class="s3">
                        {{convertMaintenanceLease($value['key'], $valueData)}}
                    </td>
                @elseif($value['key'] != 'vehicle_pdf_history')
                    <td class="s3">
                        @foreach( $valueData as $keyVehicle => $valueVehicle)
                            @if($value['key'] ==  $keyVehicle)
                                @if(in_array($value['key'] , $numberFields))
                                    {{convertNumber($value['key'], $valueVehicle)}}
                                @else
                                    {{$valueVehicle}}
                                @endif
                            @elseif($value['key'] == 'license_classification' && $keyVehicle == 'vehicle_total_weight' )
                                {{convertLicenseClassification($valueVehicle)}}
                            @endif
                        @endforeach
                    </td>
                @endif
               
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
</div>
