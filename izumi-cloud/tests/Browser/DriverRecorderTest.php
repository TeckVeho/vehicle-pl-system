<?php

namespace Tests\Browser;

use App\Jobs\SaveFileToS3Job;
use App\Models\Course;
use App\Models\Customer;
use App\Models\DriverRecorder;
use App\Models\File;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Faker\Factory as Faker;

class DriverRecorderTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testGeneral()
    {
        $this->browse(function ($browser) {
            $this->login($browser);
            $this->registryDriverRecorder($browser);
            $this->editDriverRecorder($browser);
            $this->filterDriverRecorder($browser);
            $this->sortDriverRecorder($browser);
            $this->paginationDriverRecorder($browser);
            $this->deleteDriverRecorder($browser);
            $this->detailDriverRecorder($browser);
            $this->downloadDriverRecorder($browser);
            $this->playDriverRecorder($browser);
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

    public function registryDriverRecorder($browser)
    {
        $this->initData();
        $browser->visit("#/driver-recorder/create")->pause(5000);
        //input blank

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

        $browser->press('.text-right .btn-registration')->pause(1000)
            ->assertSee('発生日時を入力してください');
        $browser->pause(1000);
        $browser->scrollIntoView('#form-record-date__value_');
        $browser->click('#form-record-date__value_');
        $browser->pause(1000);
        $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $browser->click('div[data-date="' . $endMonth . '"]');

        $browser->press('.text-right .btn-registration')->pause(1000)
            ->assertSee('拠点を選択してください');
        $browser->select('#form-department', 7);

        $browser->press('.text-right .btn-registration')->pause(1000)
            ->assertSee('種別を選択してください');
        $browser->click('#form-record-type .custom-control-inline > label');

        $browser->press('.text-right .btn-registration')->pause(1000)
            ->assertSee('タイトルは20文字以内で入力してください');
        $browser->type('#form-title', 'Test');

        $browser->scrollIntoView('.component-upload-file');
//        $browser->click('.component-upload-file .btn-add')->pause(500);

        $browser->assertSee('動画タイトル');
        $browser->assertSee('ファイル');
        $browser->assertSee('前方');
        $browser->assertSee('車内');
        $browser->assertSee('後方');

        $browser->press('.text-right .btn-registration')->pause(1000)
            ->assertSee('動画タイトルは20文字以内で入力してください');
        $browser->type('.zone-list-upload-file .title-movie > input', 'Test movie');

        $browser->press('.text-right .btn-registration')->pause(1000)
            ->assertSee('動画がアップロードされていません');
        $browser->attach('input:nth-child(10).dz-hidden-input', base_path('tests/csv/test_video.mp4'))->pause(2000);
        $browser->attach('input:nth-child(9).dz-hidden-input', base_path('tests/csv/test_video.mp4'))->pause(2000);
        $browser->attach('input:nth-child(8).dz-hidden-input', base_path('tests/csv/test_video.mp4'))->pause(2000);

        $browser->press('.text-right .btn-registration')->waitUntilMissing('.loading');
        $browser->pause(2000);
        $browser->assertSee('ドラレコデータ')->pause(3000)
            ->assertSee("発生日時")
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(1)', $endMonth)
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(2)', '東京')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(3)', '事故')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(4)', 'Test')
            ->click('tbody tr:nth-child(1) td:nth-child(5) i')->pause(1000)
            ->assertSee('Test movie');
        $browser->pause(2000);
    }

    public function editDriverRecorder($browser)
    {
        $this->initData();

        $factory = DriverRecorder::factory()->count(1)->create()->each(function ($data) {
            $front = File::factory()->count(1)->create();
            $inside = File::factory()->count(1)->create();
            $behind = File::factory()->count(1)->create();
            $data->file()->attach($front->first()->id, ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front',]);
            $data->file()->attach($inside->first()->id, ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside',]);
            $data->file()->attach($behind->first()->id, ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind',]);
        });

        $browser->visit("#/driver-recorder/edit/" . $factory->first()->id)->pause(2000);
        //->waitUntilMissing('.loading')
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
        $browser->pause(1000);
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

        $browser->attach('input:nth-child(13).dz-hidden-input', base_path('tests/csv/test_video.mp4'))->pause(2000);
        $browser->attach('input:nth-child(12).dz-hidden-input', base_path('tests/csv/test_video.mp4'))->pause(2000);
        $browser->attach('input:nth-child(11).dz-hidden-input', base_path('tests/csv/test_video.mp4'))->pause(2000);

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
        $browser->visit("#/driver-recorder/edit/" . $factory->first()->id)->pause(2000);

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


    public function filterDriverRecorder($browser)
    {
        $this->initData();
        $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $factory = DriverRecorder::factory()->count(1)
            ->state(function (array $attributes) use ($endMonth) {
                return [
                    'department_id' => 7,
                    'record_date' => $endMonth,
                    'type' => 0,
                ];
            })
            ->create()->each(function ($data) {
                $front = File::factory()->count(1)->create();
                $inside = File::factory()->count(1)->create();
                $behind = File::factory()->count(1)->create();
                $data->file()->attach($front->first()->id, ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front',]);
                $data->file()->attach($inside->first()->id, ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside',]);
                $data->file()->attach($behind->first()->id, ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind',]);
            });

        $browser->visit("#/driver-recorder/list")->pause(500)
            //->waitUntilMissing('.loading')
            ->assertSee('ドラレコデータ')->pause(300)
            ->assertSee('フィルタ')->pause(300)
            ->click("#collapsed-show-hide-filter")->pause(300)
            ->assertSee('発生日時')
            ->assertSee('拠点')
            ->assertSee('種別');
        //filter by department
        $browser->with('#zone-filter', function ($element) {
            $element->check("#filter-department")->pause(300)
                ->assertChecked("#filter-department")
                ->select(" > div:nth-child(3) .custom-select", 7)
                ->click(".btn-summit-filter")->pause(3000);
        });
        $browser->with('#table-driver-recorder', function ($table) {
            $table->assertSee('拠点');
            $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(2)', '東京');
        });

        $browser->with('#zone-filter', function ($element) use ($browser) {
            $element->click('.text-clear-all')->pause(500);
            $element->check("#filter-accident-date")->pause(300);
            $browser->click('.b-form-datepicker');
            $browser->pause(1000);
            $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
            $browser->click('div[data-date="' . $endMonth . '"]')->pause(1000);
            $element->click(".btn-summit-filter")->pause(3000);
        });

        $browser->with('#table-driver-recorder', function ($table) use ($endMonth) {
            $table->assertSee('発生日時');
            $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(1)', $endMonth);
        });

        $browser->with('#zone-filter', function ($element) {
            $element->click('.text-clear-all')->pause(500);
            $element->check("#filter-type")->pause(300)
                ->select(" > div:nth-child(3) .custom-select", 0)
                ->click(".btn-summit-filter")->pause(3000);
        });

        $browser->with('#table-driver-recorder', function ($table) {
            $table->assertSee('種別');
            $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(3)', '事故');
        });
    }

    public function paginationDriverRecorder($browser)
    {
        $this->initData();
        $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $factory = DriverRecorder::factory()->count(50)
            ->state(function (array $attributes) use ($endMonth) {
                return [
                    'department_id' => 7,
                    'record_date' => $endMonth,
                    'type' => 0,
                ];
            })
            ->create()->each(function ($data) {
                $front = File::factory()->count(1)->create();
//                $inside = File::factory()->count(1)->create();
//                $behind = File::factory()->count(1)->create();
                $data->file()->attach($front->first()->id, ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front',]);
//                $data->file()->attach($inside->first()->id, ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside',]);
//                $data->file()->attach($behind->first()->id, ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind',]);
            });

        $browser->visit("#/driver-recorder/list")
            ->pause(2000)
            ->scrollIntoView('.pagination')
            // Go to next page
            ->click('.next')->waitUntilMissing('.loading')
            ->pause(2000)
            ->scrollIntoView('.pagination')
            ->assertSeeIn('.pagination .active', '2')
            // Go to previous page
            ->pause(2000)
            ->click('.prev')->waitUntilMissing('.loading')
            ->assertSeeIn('.pagination .active', '1');
    }

    public function sortDriverRecorder($browser)
    {
        $this->initData();
        $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $endMonth2 = Carbon::now()->firstOfMonth()->addDay()->format('Y-m-d');
        $factory = DriverRecorder::factory()->count(2)
            ->state(function (array $attributes) use ($endMonth, $endMonth2) {
                return [
                    //'department_id' => 7,
                    'record_date' => Arr::random([$endMonth, $endMonth2]),
                    //'type' => 0,
                ];
            })
            ->create()->each(function ($data) {
                $front = File::factory()->count(1)->create();
                $inside = File::factory()->count(1)->create();
                $behind = File::factory()->count(1)->create();
                $data->file()->attach($front->first()->id, ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front',]);
                $data->file()->attach($inside->first()->id, ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside',]);
                $data->file()->attach($behind->first()->id, ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind',]);
            });

        $classOrder = [
            ['class' => '.th-accident-date', 'sort_by' => 'record_date', 'key_map' => 'accident_date'],
            ['class' => '.th-department', 'sort_by' => 'department_id', 'key_map' => 'department_name'],
            ['class' => '.th-type', 'sort_by' => 'type', 'key_map' => 'type'],
        ];

        $browser->visit("#/driver-recorder/list")->pause(500)
            //->waitUntilMissing('.loading')
            ->assertSee('ドラレコデータ')->pause(300)
            ->assertSee('フィルタ')->pause(300);

        $browser->with('#table-driver-recorder', function ($table) use ($classOrder) {
            foreach ($classOrder as $key => $lstOrder) {
                $index = $key + 1;
                $clOrder = (object)$lstOrder;
                $table->scrollIntoView($clOrder->class)->pause(1000)->click($clOrder->class)->pause(2000);
                if (!in_array($index, [3])) {
                    $responseData = $this->callApiSortByCheck($clOrder->sort_by, true);
                    $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(' . $index . ')', $responseData['result'][0][$clOrder->key_map]);
                    $table->assertSeeIn('tbody tr:nth-child(2) td:nth-child(' . $index . ')', $responseData['result'][1][$clOrder->key_map]);
                }
                $table->scrollIntoView($clOrder->class)->pause(1000)->click($clOrder->class)->pause(2000);
                if (!in_array($index, [3])) {
                    $responseData = $this->callApiSortByCheck($clOrder->sort_by);
                    $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(' . $index . ')', $responseData['result'][0][$clOrder->key_map]);
                    $table->assertSeeIn('tbody tr:nth-child(2) td:nth-child(' . $index . ')', $responseData['result'][1][$clOrder->key_map]);
                }
            }
        });
    }

    private function callApiSortByCheck($sort_by, $sort_type = false)
    {
        $sort_type = $sort_type ? 'desc' : 'asc';
        $rpLogin = $this->post('/api/auth/login', ['id' => "111111", 'password' => '123456789']);
        $data = json_decode($rpLogin->getContent());
        $token = $data->data->access_token;
        $monthYear = Carbon::now()->format('Y-m');
        $response = $this->withHeaders(['Authorization' => $token])->getJson('/api/driver-recorder?month=' . $monthYear . '&sort_by=' . $sort_by . '&sort_type=' . $sort_type);
        return $response->json();
    }

    public function deleteDriverRecorder($browser)
    {
        $this->initData();
        $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $factory = DriverRecorder::factory()->count(1)
            ->state(function (array $attributes) use ($endMonth) {
                return [
                    'department_id' => 7,
                    'record_date' => $endMonth,
                    'type' => 0,
                ];
            })
            ->create()->each(function ($data) {
                $front = File::factory()->count(1)->create();
//                $inside = File::factory()->count(1)->create();
//                $behind = File::factory()->count(1)->create();
                $data->file()->attach($front->first()->id, ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front',]);
//                $data->file()->attach($inside->first()->id, ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside',]);
//                $data->file()->attach($behind->first()->id, ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind',]);
            });

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

    public function detailDriverRecorder($browser)
    {
        $this->initData();
        $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $factory = DriverRecorder::factory()->count(1)
            ->state(function (array $attributes) use ($endMonth) {
                return [
                    'department_id' => 7,
                    'record_date' => $endMonth,
                    'type' => 0,
                ];
            })
            ->create()->each(function ($data) {
                $front = File::factory()->count(1)->create();
//                $inside = File::factory()->count(1)->create();
//                $behind = File::factory()->count(1)->create();
                $data->file()->attach($front->first()->id, ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front',]);
//                $data->file()->attach($inside->first()->id, ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside',]);
//                $data->file()->attach($behind->first()->id, ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind',]);
            });

        //test delete
        $browser->visit("#/driver-recorder/list")->pause(2000);
        $browser->with('#table-driver-recorder', function ($table) use ($browser) {
            $table->scrollIntoView('.fa-eye')->pause(1000)
                ->click('tbody tr:nth-child(1) .fa-eye')->pause(1000);
        });
        $browser->assertSee('ドラレコデータ')
            ->assertSee('発生日時')
            ->assertSee('拠点')
            ->assertSee('タイトル')
            ->assertSee('アップロードファイル')
            ->assertSee('種別');
    }

    public function downloadDriverRecorder(Browser $browser)
    {
        $files = Storage::disk('local')->allFiles('test_download');
        Storage::disk('local')->delete($files);
        $this->initData();
        $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $factory = DriverRecorder::factory()->count(1)
            ->state(function (array $attributes) use ($endMonth) {
                return [
                    'title' => 'aaaaaaa',
                    'department_id' => 7,
                    'record_date' => $endMonth,
                    'type' => 0,
                ];
            })
            ->create()->each(function ($data) {
                $front = $this->callApiUploadFile();
                $inside = $this->callApiUploadFile();
                $behind = $this->callApiUploadFile();

                $data->file()->attach($front['id'], ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front',]);
                $data->file()->attach($inside['id'], ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside',]);
                $data->file()->attach($behind['id'], ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind',]);
            });
        $browser->pause(3000);

        //test delete
        $browser->visit("#/driver-recorder/list")->pause(2000);
        $browser->with('#table-driver-recorder', function ($table) use ($browser) {
            $table->scrollIntoView('.fa-arrow-to-bottom')->pause(1000)
                ->click('tbody tr:nth-child(1) .fa-arrow-to-bottom')->pause(5000);
        });
        $files = Storage::disk('local')->allFiles('test_download');
        Storage::disk('local')->assertExists($files);
        Storage::disk('local')->delete($files);
    }

    private function callApiUploadFile()
    {
        $rpLogin = $this->post('/api/auth/login', ['id' => "111111", 'password' => '123456789']);
        $data = json_decode($rpLogin->getContent());
        $token = $data->data->access_token;

        $response = $this->withHeaders(['Authorization' => $token])->post('/api/driver-recorder/upload-file', [
            "file" => UploadedFile::fake()->createWithContent('move.mp4', file_get_contents(base_path('tests/csv/test_video.mp4')))
        ]);
        File::query()->where('id', $response->json('id'))->update(['expired_at' => null]);
        SaveFileToS3Job::dispatchSync($response->json('id'));
        return $response->json();
    }


    public function playDriverRecorder(Browser $browser)
    {
        $this->initData();
        $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $factory = DriverRecorder::factory()->count(1)
            ->state(function (array $attributes) use ($endMonth) {
                return [
                    'title' => 'aaaaaaa',
                    'department_id' => 7,
                    'record_date' => $endMonth,
                    'type' => 0,
                ];
            })
            ->create()->each(function ($data) {
                $front = $this->callApiUploadFile();
                $inside = $this->callApiUploadFile();
                $behind = $this->callApiUploadFile();

                $data->file()->attach($front['id'], ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front',]);
                $data->file()->attach($inside['id'], ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside',]);
                $data->file()->attach($behind['id'], ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind',]);
            });
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
            ->pause(2000)
            ->assertDontSee('00:00:00')
            ->pause(2000);
    }
}
