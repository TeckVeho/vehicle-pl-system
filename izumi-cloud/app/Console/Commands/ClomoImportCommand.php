<?php

namespace App\Console\Commands;

use Helper\Common;
use App\Imports\ClomoImport;
use Illuminate\Console\Command;

class ClomoImportCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'clomoImport';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    Common::setInputEncoding('clomo.csv');
    $import = new ClomoImport();
    $import->import('clomo.csv');
    foreach ($import->failures() as $failure) {
      $failure->row(); // row that went wrong
      $failure->attribute(); // either heading key (if using heading row concern) or column index
      $failure->errors(); // Actual error messages from Laravel validator
      $failure->values(); // The values of the row that has failed.
    }
  }
}
