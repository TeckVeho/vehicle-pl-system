<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleInspectionCertTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_inspection_cert', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('vehicle_id');
            $table->string('FormType')->nullable();
            $table->string('ElectCertMgNo')->nullable();
            $table->string('CarId')->nullable();
            $table->string('ElectCertPublishDateE')->nullable();
            $table->string('ElectCertPublishDateY')->nullable();
            $table->string('ElectCertPublishDateM')->nullable();
            $table->string('ElectCertPublishDateD')->nullable();
            $table->string('TransportationBureauChiefName')->nullable();
            $table->string('EntryNoCarNo')->nullable();
            $table->string('RegGrantDateE')->nullable();
            $table->string('RegGrantDateY')->nullable();
            $table->string('RegGrantDateM')->nullable();
            $table->string('RegGrantDateD')->nullable();
            $table->string('FirstRegDateE')->nullable();
            $table->string('FirstRegDateY')->nullable();
            $table->string('FirstRegDateM')->nullable();
            $table->string('CarName')->nullable();
            $table->string('CarNameCode')->nullable();
            $table->string('CarNo')->nullable();
            $table->string('CarNoConvert')->nullable();
            $table->string('Model')->nullable();
            $table->string('EngineModel')->nullable();
            $table->string('OwnerNameLowLevelChar')->nullable();
            $table->string('OwnerNameHighLevelChar')->nullable();
            $table->string('OwnerAddressChar')->nullable();
            $table->string('OwnerAddressNumValue')->nullable();
            $table->string('OwnerAddressCode')->nullable();
            $table->string('UsernameLowLevelChar')->nullable();
            $table->string('UsernameHighLevelChar')->nullable();
            $table->string('UserAddressChar')->nullable();
            $table->string('UserAddressNumValue')->nullable();
            $table->string('UserAddressCode')->nullable();
            $table->string('UseHeadquarterChar')->nullable();
            $table->string('UseHeadquarterNumValue')->nullable();
            $table->string('UseHeadquarterCode')->nullable();
            $table->string('CarKind')->nullable();
            $table->string('Use')->nullable();
            $table->string('PrivateBusiness')->nullable();
            $table->string('CarShape')->nullable();
            $table->string('CarShapeCode')->nullable();
            $table->string('Cap')->nullable();
            $table->string('MaxLoadAge')->nullable();
            $table->string('CarWgt')->nullable();
            $table->string('CarTotalWgt')->nullable();
            $table->string('Length')->nullable();
            $table->string('Width')->nullable();
            $table->string('Height')->nullable();
            $table->string('FfAxWgt')->nullable();
            $table->string('FrAxWgt')->nullable();
            $table->string('RfAxWgt')->nullable();
            $table->string('RrAxWgt')->nullable();
            $table->string('Displacement')->nullable();
            $table->string('FuelClass')->nullable();
            $table->string('ModelSpecifyNo')->nullable();
            $table->string('ClassifyAroundNo')->nullable();
            $table->string('ValidPeriodExpDateE')->nullable();
            $table->string('ValidPeriodExpDateY')->nullable();
            $table->string('ValidPeriodExpDateM')->nullable();
            $table->string('ValidPeriodExpDateD')->nullable();
            $table->text('NoteInfo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_inspection_cert');
    }
}
