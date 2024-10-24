<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use VaahCms\Modules\Appointment\Models\Patient;


class BulkRecordPatient implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $total_records;
    /**
     * Create a new job instance.
     */
    public function __construct($total_records)
    {
        $this->total_records = $total_records;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {

        \Log::info($this->total_records . ' new patients record creation-process is initiated.');
        $i = 0;
        while ($i<$this->total_records){
            $inputs = Patient::fillItem(false);

            $item =  new Patient();
            $item->fill($inputs);
            $item->save();

            $i++;
        }
        \Log::info($this->total_records . ' new patients record creation-process completed.');
    }
}
