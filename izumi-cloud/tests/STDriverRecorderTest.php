<?php

namespace Tests;

use App\Models\Course;
use App\Models\Customer;
use App\Models\DriverRecorder;
use App\Models\File;
use App\Models\Route;
use App\Models\RouteNonDelivery;
use App\Models\Store;
use Carbon\Carbon;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;

class STDriverRecorderTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testGeneral()
    {
        //Artisan::call('migrate:fresh --seed');
        $this->browse(function ($browser) {
            $this->login($browser);
            $this->initData();
            $this->testRegisterRecord($browser);
            $this->playDriverRecorder($browser);
            $this->downloadDriverRecorder($browser);
            $this->editDriverRecorder($browser);
        });
    }

    public function initData()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('driver_recorder_file')->truncate();
        DriverRecorder::query()->truncate();
        File::query()->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function testRegisterRecord($browser)
    {
        $browser->click('a[href="#/driver-recorder"]')->pause(2000);
        $browser->click('.btn-registration')->pause(2000);
        $browser->assertSee('ドラレコデータ');
        $browser->assertSee('ドラレコデータ');
        $browser->assertSee('発生日時');
        $browser->assertSee('拠点');
        $browser->assertSee('種別');
        $browser->assertSee('事故');
        $browser->assertSee('タイトル');
        $browser->assertSee('アップロードファイル');
        $browser->assertSee('備考');
        $browser->assertSee('アップロード');
        $browser->assertSee('追加');

        $browser->pause(1000);
        $browser->scrollIntoView('#form-record-date__value_');
        $browser->click('#form-record-date__value_');
        $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $browser->click('div[data-date="' . $endMonth . '"]');
        $browser->select('#form-department', 7);
        $browser->click('#form-record-type .custom-control-inline > label');
        $browser->type('#form-title', 'Test');

        $browser->scrollIntoView('.component-upload-file');
//        $browser->click('.component-upload-file .btn-add')->pause(500);

        $browser->assertSee('動画タイトル');
        $browser->assertSee('ファイル');
        $browser->assertSee('前方');
        $browser->assertSee('車内');
        $browser->assertSee('後方');

        $browser->type('.zone-list-upload-file .title-movie > input', 'Test movie');
        $browser->attach('input:nth-child(10).dz-hidden-input', base_path('tests/csv/test_video.mp4'))->pause(2000);
        $browser->attach('input:nth-child(9).dz-hidden-input', base_path('tests/csv/test_video.mp4'))->pause(2000);
        $browser->attach('input:nth-child(8).dz-hidden-input', base_path('tests/csv/test_video.mp4'))->pause(2000);

        $browser->press('.text-right .btn-registration')->waitUntilMissing('.loading');
        $browser->pause(6000);
        error_log("AAAAAAAAAAA:::".$browser->text('tbody tr:nth-child(1) td:nth-child(1)'));
        $browser->assertSee('ドラレコデータ')->pause(2000)
            ->assertSee("発生日時")
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(1)', $endMonth)
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(2)', '東京')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(3)', '事故')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(4)', 'Test')
            ->click('tbody tr:nth-child(1) td:nth-child(5) i')->pause(1000)
            ->assertSee('Test movie');
        $browser->pause(2000);
    }

    public function playDriverRecorder($browser)
    {
        $browser->pause(3000);
        //test delete
        $browser->visit("#/driver-recorder/list")->pause(2000);
        $browser->with('#table-driver-recorder', function ($table) use ($browser) {
            $table->scrollIntoView('.fa-tv-alt')->pause(1000)
                ->click('tbody tr:nth-child(1) td:nth-child(5) i')
                ->pause(1000);
            $browser->click('.popover-body .item-play')
                ->pause(5000);
        });
        $window = collect($browser->driver->getWindowHandles())->last();
        // Switch to the tab
        $browser->driver->switchTo()->window($window);
        $browser->pause(1000);

        $browser->assertSee('を再生しています')
            ->assertSee('00:00:00')
            ->assertSee('前方')
            ->assertSee('車内')
            ->assertSee('後方');
        $browser->click('.btn-play')
            ->pause(4000)
            ->assertDontSee('00:00:00')
            ->pause(2000);
    }

    public function downloadDriverRecorder(Browser $browser)
    {
        $files = Storage::disk('local')->allFiles('test_download');
        Storage::disk('local')->delete($files);
        $browser->pause(3000);
        $browser->visit("#/driver-recorder/list")->pause(2000);
        $browser->with('#table-driver-recorder', function ($table) use ($browser) {
            $table->scrollIntoView('.fa-arrow-to-bottom')->pause(1000)
                ->click('tbody tr:nth-child(1) .fa-arrow-to-bottom')->pause(5000);
        });
        $files = Storage::disk('local')->allFiles('test_download');
        Storage::disk('local')->assertExists($files);
        Storage::disk('local')->delete($files);
    }

    public function editDriverRecorder($browser)
    {
        $browser->visit("#/driver-recorder/list")->pause(4000);
        $browser->with('#table-driver-recorder', function ($table) use ($browser) {
            $table->click('tbody tr:nth-child(1) .fa-eye')->pause(2000);
        });
        $browser->scrollIntoView('.text-right .btn-registration');
        $browser->press('.text-right .btn-registration')->pause(3000);
        $browser->assertSee('ドラレコデータ')
            ->assertSee('発生日時')
            ->assertSee('拠点')
            ->assertSee('タイトル')
            ->assertSee('アップロードファイル')
            ->assertSee('種別');        //->waitUntilMissing('.loading')
        $browser->assertSee('ドラレコデータ');
        $browser->assertSee('発生日時');
        $browser->assertSee('拠点');
        $browser->assertSee('種別');
        $browser->assertSee('事故');
        $browser->assertSee('タイトル');
        $browser->assertSee('アップロードファイル');
        $browser->assertSee('備考');
        $browser->assertSee('アップロード');
        $browser->assertSee('追加');

        $browser->pause(1000);
        $browser->scrollIntoView('#form-record-date__value_');
        $browser->click('#form-record-date__value_');
        $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $browser->click('div[data-date="' . $endMonth . '"]');

        $browser->select('#form-department', 1);
        $browser->click('#form-record-type .custom-control-inline > label');
        $browser->type('#form-title', 'Test 2');
        $browser->scrollIntoView('.component-upload-file');

        $browser->click('.component-upload-file .btn-add')->pause(1000);
        $browser->assertSeeIn('.zone-list-upload-file > div:nth-child(1)', '動画タイトル');
        $browser->assertSeeIn('.zone-list-upload-file > div:nth-child(1)', 'ファイル');
        $browser->assertSeeIn('.zone-list-upload-file > div:nth-child(1)', '前方');
        $browser->assertSeeIn('.zone-list-upload-file > div:nth-child(1)', '車内');
        $browser->assertSeeIn('.zone-list-upload-file > div:nth-child(1)', '後方');

        $browser->assertSeeIn('.zone-list-upload-file > div:nth-child(2)', '動画タイトル');
        $browser->assertSeeIn('.zone-list-upload-file > div:nth-child(2)', 'ファイル');
        $browser->assertSeeIn('.zone-list-upload-file > div:nth-child(2)', '前方');
        $browser->assertSeeIn('.zone-list-upload-file > div:nth-child(2)', '車内');
        $browser->assertSeeIn('.zone-list-upload-file > div:nth-child(2)', '後方');

//        $browser->press('.text-right .btn-registration')->pause(1000)
//            ->assertSee('動画がアップロードされていません');
        $browser->attach('input:nth-child(12).dz-hidden-input', base_path('tests/csv/test_video.mp4'))->pause(2000);
        $browser->attach('input:nth-child(11).dz-hidden-input', base_path('tests/csv/test_video.mp4'))->pause(2000);
        $browser->attach('input:nth-child(10).dz-hidden-input', base_path('tests/csv/test_video.mp4'))->pause(2000);

//        $browser->press('.text-right .btn-registration')->pause(1000)->assertSee('動画タイトルは20文字以内で入力してください');
        $browser->type('.zone-list-upload-file > div:nth-child(2) .title-movie > input', 'Test movie');
        $browser->pause(2000);
        $browser->press('.text-right .btn-registration')->waitUntilMissing('.loading');
        $browser->pause(4000);

        $browser->assertSee('ドラレコデータ')
            ->assertSee("発生日時")
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(1)', $endMonth)
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(2)', '本社')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(3)', '事故')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(4)', 'Test 2')
            ->click('tbody tr:nth-child(1) td:nth-child(5) i')->pause(1000)
            ->assertSee('Test movie');
        $browser->pause(2000);

        $browser->visit("#/driver-recorder/list")->pause(3000);
        $browser->with('#table-driver-recorder', function ($table) use ($browser) {
            $table->click('tbody tr:nth-child(1) .fa-eye')->pause(2000);
        });

        $browser->scrollIntoView('.text-right .btn-registration');
        $browser->press('.text-right .btn-registration')->pause(3000);

        $browser->click('.zone-list-upload-file > div:nth-child(2) .zone-delete button')->pause(1000);
        $browser->type('.zone-list-upload-file > div:nth-child(1) .title-movie > input', 'Test movie');
        $browser->press('.text-right .btn-registration')->waitUntilMissing('.loading');
        $browser->pause(2000);
        $browser->assertSee('編集が完了しました');
        $browser->assertSee('ドラレコデータ')
            ->assertSee("発生日時")
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(1)', $endMonth)
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(2)', '本社')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(3)', '事故')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(4)', 'Test 2')
            ->click('tbody tr:nth-child(1) td:nth-child(5) i')->pause(1000)
            ->assertSee('Test movie');
        $browser->pause(2000);
    }

    public function deleteDriverRecorder($browser)
    {

        //test delete
        $browser->visit("#/driver-recorder/list")->pause(2000);
        $browser->with('#table-driver-recorder', function ($table) use ($browser) {
            $table->scrollIntoView('.fa-trash')->pause(1000)
                ->click('tbody tr:nth-child(1) .fa-trash')->pause(1000);
            $browser->whenAvailable('#modal-cf', function ($modal) {
                $modal->assertSee('このデータを削除してもよろしいですか？')->assertSee('いいえ')->assertSee('はい')
                    ->pause(2000)
                    ->press('.btn-apply')->pause(2000);
            });
            $browser->assertSee('削除しました')->pause(3000);
        });
    }
}

