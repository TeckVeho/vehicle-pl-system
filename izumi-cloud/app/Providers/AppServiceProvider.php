<?php

namespace App\Providers;


use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\DriverPlayListRepositoryInterface;
use App\Repositories\Contracts\DriverRecorderRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\HotlineRepositoryInterface;
use App\Repositories\Contracts\QuotationRepositoryInterface;
use App\Repositories\Contracts\QuotationMasterDataRepositoryInterface;
use App\Repositories\Contracts\QuotationStaffRepositoryInterface;
use App\Repositories\Contracts\InsuranceRateRepositoryInterface;
use App\Repositories\Contracts\PocketBooksRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserContactsRepositoryInterface;
use App\Repositories\Contracts\ShakenshoEmailRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\DataConnectionRepositoryInterface;
use App\Repositories\Contracts\DataRepositoryInterface;
use App\Repositories\Contracts\SystemRepositoryInterface;
use App\Repositories\Contracts\UploadDataRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\RouteRepositoryInterface;
use App\Repositories\Contracts\StoreRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\PLServiceRepositoryInterface;
use App\Repositories\Contracts\MoviesRepositoryInterface;
use App\Repositories\Contracts\VehicleMaintenanceCostRepositoryInterface;
use App\Repositories\Contracts\LineworkBotMessageRepositoryInterface;
use App\Repositories\Contracts\LineWorksRepositoryInterface;
use App\Repositories\Contracts\InspectionNotificationRecipientRepositoryInterface;
use App\Repositories\Contracts\EmployeePdfStorageRepositoryInterface;
use App\Repositories\Contracts\NewsLetterRepositoryInterface;

use Repository\VehicleMaintenanceCostRepository;
use Repository\BaseRepository;
use Repository\AuthRepository;
use Repository\DataConnectionRepository;
use Repository\DepartmentRepository;
use Repository\DriverPlayListRepository;
use Repository\DriverRecorderRepository;
use Repository\EmployeeRepository;
use Repository\HotlineRepository;
use Repository\InsuranceRateRepository;
use Repository\PocketBooksRepository;
use Repository\RoleRepository;
use Laravel\Dusk\DuskServiceProvider;
use Repository\DataRepository;
use Repository\ShakenshoEmailRepository;
use Repository\SystemRepository;
use Repository\UploadDataRepository;
use Repository\CourseRepository;
use Repository\RouteRepository;
use Repository\StoreRepository;
use Repository\CustomerRepository;
use Repository\UserContactsRepository;
use Repository\VehicleRepository;
use Repository\PLServiceRepository;
use Repository\MoviesRepository;
use Repository\LineWorksRepository;
use Repository\QuotationRepository;
use Repository\QuotationMasterDataRepository;
use Repository\QuotationStaffRepository;
use Repository\LineworkBotMessageRepository;
use App\Repositories\InspectionNotificationRecipientRepository;
use Repository\EmployeePdfStorageRepository;
use Repository\NewsLetterRepository;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use App\Exceptions\Handler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(SystemRepositoryInterface::class, systemRepository::class);
        $this->app->bind(DataRepositoryInterface::class, dataRepository::class);
        $this->app->bind(DataConnectionRepositoryInterface::class, DataConnectionRepository::class);
        $this->app->bind(UploadDataRepositoryInterface::class, UploadDataRepository::class);
        $this->app->bind(UploadDataRepositoryInterface::class, UploadDataRepository::class);

        // ------- couse master data -------
        $this->app->bind(CourseRepositoryInterface::class, CourseRepository::class);
        $this->app->bind(RouteRepositoryInterface::class, RouteRepository::class);
        $this->app->bind(StoreRepositoryInterface::class, StoreRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(VehicleRepositoryInterface::class, VehicleRepository::class);
        $this->app->bind(DriverRecorderRepositoryInterface::class, DriverRecorderRepository::class);
        $this->app->bind(InsuranceRateRepositoryInterface::class, InsuranceRateRepository::class);
        $this->app->bind(PLServiceRepositoryInterface::class, PLServiceRepository::class);
        $this->app->bind(DriverPlayListRepositoryInterface::class, DriverPlayListRepository::class);
        $this->app->bind(MoviesRepositoryInterface::class, MoviesRepository::class);
        $this->app->bind(PocketBooksRepositoryInterface::class, PocketBooksRepository::class);
        $this->app->bind(UserContactsRepositoryInterface::class, UserContactsRepository::class);
        $this->app->bind(ShakenshoEmailRepositoryInterface::class, ShakenshoEmailRepository::class);
        $this->app->bind(HotlineRepositoryInterface::class, HotlineRepository::class);
        $this->app->bind(VehicleMaintenanceCostRepositoryInterface::class, VehicleMaintenanceCostRepository::class);
        $this->app->bind(LineWorksRepositoryInterface::class, LineWorksRepository::class);
        $this->app->bind(QuotationRepositoryInterface::class, QuotationRepository::class);
        $this->app->bind(QuotationMasterDataRepositoryInterface::class, QuotationMasterDataRepository::class);
        $this->app->bind(QuotationStaffRepositoryInterface::class, QuotationStaffRepository::class);
        $this->app->bind(LineworkBotMessageRepositoryInterface::class,LineworkBotMessageRepository::class);
        $this->app->bind(InspectionNotificationRecipientRepositoryInterface::class, InspectionNotificationRecipientRepository::class);
        $this->app->bind(EmployeePdfStorageRepositoryInterface::class, EmployeePdfStorageRepository::class);
        $this->app->bind(NewsLetterRepositoryInterface::class, NewsLetterRepository::class);
        //
        //Customer
        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }
        //        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //Filesystem v3 has some changes in permissions, so set umask(0002) temporarily,
        // there is another way to fix it later, we will fix it later
        umask(0002);
    }

    public $singletons = [
        ExceptionHandlerContract::class => Handler::class
    ];
}